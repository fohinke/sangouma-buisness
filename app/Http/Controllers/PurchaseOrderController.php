<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Payment;
use App\Models\Supplier;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\DeliveryService;
use App\Services\SequenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
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

        $orders = PurchaseOrder::with('supplier')
            ->when($q, function ($b) use ($q) {
                $b->where(function ($x) use ($q) {
                    $x->where('code', 'like', "%$q%")
                      ->orWhereHas('supplier', fn($s) => $s->where('name','like',"%$q%"));
                });
            })
            ->when($status, fn($b) => $b->where('status', $status))
            ->when($delivery, function ($b) use ($delivery) {
                if ($delivery === 'en_attente') {
                    $b->whereHas('items', function($q){ $q->whereColumn('received_qty','<','qty'); });
                } elseif ($delivery === 'en_cours') {
                    $b->whereHas('items', function($q){ $q->whereColumn('received_qty','>','0')->whereColumn('received_qty','<','qty'); });
                } elseif ($delivery === 'livree') {
                    $b->whereDoesntHave('items', function($q){ $q->whereColumn('received_qty','<','qty'); });
                }
            })
            ->latest('ordered_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('purchase_orders.index', compact('orders','q','status','delivery','perPage','allowed'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->pluck('name','id');
        $products = Product::orderBy('name')->get();
        return view('purchase_orders.create', compact('suppliers','products'));
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $code = $this->seq->next('PO');
            $order = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'code' => $code,
                'status' => 'en_attente',
                'ordered_at' => now(),
                'total_ht' => 0,
                'total_ttc' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $row) {
                $qty = (int) $row['qty'];
                $up = (float) $row['unit_price'];
                $subtotal = $qty * $up;
                $order->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $qty,
                    'unit_price' => $up,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }
            $order->update(['total_ht' => $total, 'total_ttc' => $total]);
        });

        return redirect()->route('purchase-orders.index')->with('success','Commande créée.');
    }

    public function show(PurchaseOrder $purchase_order)
    {
        $purchase_order->load(['supplier', 'items.product', 'payments']);
        $paid = (float) $purchase_order->payments->sum('amount');
        return view('purchase_orders.show', [
            'order' => $purchase_order,
            'paid' => $paid,
            'balance' => max(0, ($purchase_order->total_ttc ?? 0) - $paid),
        ]);
    }

    public function addPayment(Request $request, PurchaseOrder $purchase_order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
            'method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        try {
            $this->payments->addPayment(
                $purchase_order,
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

    public function removePayment(Request $request, PurchaseOrder $purchase_order, Payment $payment)
    {
        if ($payment->payable_type !== PurchaseOrder::class || (int) $payment->payable_id !== (int) $purchase_order->id) {
            abort(404);
        }
        $payment->delete();
        return back()->with('success','Paiement supprimé.');
    }

    public function receive(Request $request, PurchaseOrder $purchase_order)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|integer|exists:purchase_order_items,id',
            'items.*.qty' => 'required|integer|min:0',
            'delivered_at' => 'nullable|date',
            'delivered_by' => 'nullable|string',
        ]);
        try {
            $this->delivery->receivePurchase(
                $purchase_order,
                $validated['items'],
                isset($validated['delivered_at']) ? \Carbon\Carbon::parse($validated['delivered_at']) : null,
                $validated['delivered_by'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors([$e->getMessage()])->withInput();
        }
        return back()->with('success','Réception enregistrée.');
    }

    public function invoice(PurchaseOrder $purchase_order)
    {
        $pdf = $this->invoice->generatePurchaseInvoice($purchase_order->load(['supplier','items.product','payments']));
        return $pdf->download($purchase_order->code.'-facture.pdf');
    }

    public function export()
    {
        $rows = PurchaseOrder::with('supplier')->orderByDesc('ordered_at')->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="purchase_orders.csv"',
        ];
        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputs($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Code','Fournisseur','Date','Statut','Total TTC','Payé','Reste'], ';');
            foreach ($rows as $o) {
                $paid = (float) $o->payments()->sum('amount');
                $rest = max(0, ($o->total_ttc ?? 0) - $paid);
                fputcsv($out, [
                    $o->code,
                    optional($o->supplier)->name,
                    optional($o->ordered_at)->format('Y-m-d'),
                    $o->status,
                    number_format((float)$o->total_ttc, 2, ',', ' '),
                    number_format($paid, 2, ',', ' '),
                    number_format($rest, 2, ',', ' '),
                ], ';');
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }
}
