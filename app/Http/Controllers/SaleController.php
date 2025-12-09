<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Payment;
use App\Services\DeliveryService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\SequenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct(
        private readonly SequenceService $seq,
        private readonly PaymentService $payments,
        private readonly DeliveryService $delivery,
        private readonly InvoiceService $invoice,
    ) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status');
        $delivery = $request->get('delivery');
        $perPage = (int) $request->get('per_page', 15);
        $allowed = [10, 15, 25, 50];
        if (!in_array($perPage, $allowed, true)) { $perPage = 15; }

        $sales = Sale::with('client')
            ->when($q, function ($b) use ($q) {
                $b->where(function ($x) use ($q) {
                    $x->where('code', 'like', "%$q%")
                      ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%$q%"));
                });
            })
            ->when($status, fn($b) => $b->where('status', $status))
            ->when($delivery, function ($b) use ($delivery) {
                if ($delivery === 'en_attente') {
                    $b->where(function ($x) {
                        $x->whereNull('delivery_status')->orWhere('delivery_status', 'en_attente');
                    });
                } else {
                    $b->where('delivery_status', $delivery);
                }
            })
            ->latest('sold_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('sales.index', compact('sales', 'q', 'status', 'delivery', 'perPage', 'allowed'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->pluck('name','id');
        $products = Product::orderBy('name')->get();
        $productsJson = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->sale_price,
                'stock' => $p->stock,
            ];
        })->values();
        return view('sales.create', compact('clients','products','productsJson'));
    }

    public function store(StoreSaleRequest $request)
    {
        $data = $request->validated();

        // Prevent selling more than current stock
        $requested = [];
        foreach ($data['items'] as $row) {
            $pid = (int) $row['product_id'];
            $requested[$pid] = ($requested[$pid] ?? 0) + (int) $row['qty'];
        }
        $products = Product::whereIn('id', array_keys($requested))->get(['id','name','stock']);
        $errors = [];
        foreach ($products as $p) {
            if ($requested[$p->id] > $p->stock) {
                $errors[] = "Le produit {$p->name} n'a que {$p->stock} en stock.";
            }
        }
        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        DB::transaction(function () use ($data) {
            $code = $this->seq->next('INV');
            $sale = Sale::create([
                'client_id' => $data['client_id'],
                'code' => $code,
                'status' => 'en_attente',
                'sold_at' => now(),
                'total_ht' => 0,
                'total_ttc' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $qty = (int) $row['qty'];
                $up = (float) $row['unit_price'];
                $subtotal = $qty * $up;
                $sale->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $qty,
                    'unit_price' => $up,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }
            $sale->update(['total_ht' => $total, 'total_ttc' => $total]);
        });

        return redirect()->route('sales.index')->with('success','Vente créée.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['client','items.product','payments']);
        $paid = (float) $sale->payments->sum('amount');
        return view('sales.show', [
            'sale' => $sale,
            'paid' => $paid,
            'balance' => max(0, ($sale->total_ttc ?? 0) - $paid),
            'canCancel' => (int) $sale->items->sum('delivered_qty') === 0 && !$sale->payments->count(),
        ]);
    }

    public function addPayment(Request $request, Sale $sale)
    {
        $request->merge([
            'amount' => $this->normalizeAmount($request->input('amount')),
        ]);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
            'method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        try {
            $this->payments->addPayment(
                $sale,
                (float) $validated['amount'],
                \Carbon\Carbon::parse($validated['paid_at']),
                $validated['method'] ?? 'cash',
                $validated['notes'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors([$e->getMessage()])->withInput();
        }
        return back()->with('success','Paiement enregistré.');
    }

    public function removePayment(Request $request, Sale $sale, Payment $payment)
    {
        if ($payment->payable_type !== Sale::class || (int) $payment->payable_id !== (int) $sale->id) {
            abort(404);
        }
        $payment->delete();
        return back()->with('success','Paiement supprimé.');
    }

    public function cancel(Request $request, Sale $sale)
    {
        $sale->load(['items','payments']);
        $deliveredQty = (int) $sale->items->sum('delivered_qty');
        $hasPayments = $sale->payments()->exists();

        if ($deliveredQty > 0) {
            return back()->withErrors(["Impossible d'annuler : des articles ont déjà été livrés."]);
        }
        if ($hasPayments) {
            return back()->withErrors(["Impossible d'annuler : des paiements sont enregistrés."]);
        }

        DB::transaction(function () use ($sale) {
            $sale->items()->delete();
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Vente annulée.');
    }

    public function deliver(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|integer|exists:sale_items,id',
            'items.*.qty' => 'required|integer|min:0',
            'delivered_at' => 'nullable|date',
            'carrier' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        try {
            $this->delivery->deliverSale(
                $sale,
                $validated['items'],
                isset($validated['delivered_at']) ? \Carbon\Carbon::parse($validated['delivered_at']) : null,
                $validated['carrier'] ?? null,
                $validated['notes'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors([$e->getMessage()])->withInput();
        }
        return back()->with('success','Livraison enregistrée.');
    }

    public function invoice(Sale $sale)
    {
        $pdf = $this->invoice->generateSaleInvoice($sale->load(['client','items.product','payments']));
        return $pdf->download($sale->code.'-facture.pdf');
    }

    public function deliveryNote(Sale $sale)
    {
        $pdf = $this->invoice->generateDeliveryNote($sale->load(['client','items.product']));
        return $pdf->download($sale->code.'-BL.pdf');
    }

    public function export()
    {
        // Export CSV simple (compatibilité Excel) sans dépendance externe
        $rows = Sale::with('client')->orderByDesc('sold_at')->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sales.csv"',
        ];
        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputs($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($out, ['Code','Client','Date','Statut','Total TTC','Payé','Reste'], ';');
            foreach ($rows as $s) {
                $paid = (float) $s->payments()->sum('amount');
                $rest = max(0, ($s->total_ttc ?? 0) - $paid);
                fputcsv($out, [
                    $s->code,
                    optional($s->client)->name,
                    optional($s->sold_at)->format('Y-m-d'),
                    $s->status,
                    number_format((float)$s->total_ttc, 2, ',', ' '),
                    number_format($paid, 2, ',', ' '),
                    number_format($rest, 2, ',', ' '),
                ], ';');
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    private function normalizeAmount(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/[^0-9,.\-]/', '', $value);
        if ($clean === null) {
            return null;
        }
        $clean = str_replace(',', '.', str_replace(' ', '', $clean));

        $dotCount = substr_count($clean, '.');
        if ($dotCount > 1) {
            $parts = explode('.', $clean);
            $decimal = array_pop($parts);
            $clean = implode('', $parts).'.'.$decimal;
        }

        return $clean;
    }
}
