<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\SequenceService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Composant Livewire: création de commande fournisseur (lignes dynamiques + calculs).
 */
class PurchaseOrderCreate extends Component
{
    public array $items = [];
    public ?int $supplier_id = null;
    public ?string $notes = null;
    public array $suppliers = [];
    public float $total = 0.0;

    public function mount(): void
    {
        $this->suppliers = Supplier::orderBy('name')->pluck('name','id')->toArray();
    }

    #[On('supplierSelected')]
    public function onSupplierSelected(array $supplier): void
    {
        $this->supplier_id = (int) ($supplier['id'] ?? 0);
        if (!array_key_exists($this->supplier_id, $this->suppliers)) {
            $this->suppliers = Supplier::orderBy('name')->pluck('name','id')->toArray();
        }
        $this->resetErrorBag('supplier_id');
    }

    #[On('productSelected')]
    public function onProductSelected(array $product): void
    {
        $this->items[] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'qty' => 1,
            'unit_price' => (float) ($product['purchase_price'] ?? 0),
        ];
        $this->recalc();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalc();
    }

    public function updatedItems(): void
    {
        foreach ($this->items as $i => $row) {
            $this->items[$i]['qty'] = max(1, (int) ($row['qty'] ?? 1));
            $this->items[$i]['unit_price'] = max(0, (float) ($row['unit_price'] ?? 0));
        }
        $this->recalc();
    }

    private function recalc(): void
    {
        $t = 0.0;
        foreach ($this->items as $row) {
            $t += ((int)($row['qty'] ?? 0)) * ((float)($row['unit_price'] ?? 0));
        }
        $this->total = $t;
    }

    public function save(SequenceService $seq)
    {
        $this->resetErrorBag();
        $this->validate(
            [
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ],
            [
                'supplier_id.required' => 'Le fournisseur est obligatoire.',
                'items.required' => 'Veuillez ajouter au moins un article.',
                'items.*.product_id.required' => 'Le produit est obligatoire.',
                'items.*.product_id.exists' => 'Le produit sélectionné est invalide.',
                'items.*.qty.required' => 'La quantité est obligatoire.',
                'items.*.qty.integer' => 'La quantité doit être un entier.',
                'items.*.qty.min' => 'La quantité doit être au moins 1.',
                'items.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
                'items.*.unit_price.numeric' => 'Le prix unitaire doit être un nombre.',
                'items.*.unit_price.min' => 'Le prix unitaire doit être au moins 0.',
            ],
            [
                'supplier_id' => 'fournisseur',
                'items' => 'articles',
            ]
        );

        DB::transaction(function () use ($seq) {
            $code = $seq->next('PO');
            $order = PurchaseOrder::create([
                'supplier_id' => $this->supplier_id,
                'code' => $code,
                'status' => 'en_attente',
                'ordered_at' => now(),
                'total_ht' => 0,
                'total_ttc' => 0,
                'notes' => $this->notes,
            ]);

            $total = 0.0;
            foreach ($this->items as $row) {
                $qty = (int) $row['qty'];
                $up = (float) $row['unit_price'];
                $subtotal = $qty * $up;
                $order->items()->create([
                    'product_id' => (int) $row['product_id'],
                    'qty' => $qty,
                    'unit_price' => $up,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }
            $order->update(['total_ht' => $total, 'total_ttc' => $total]);
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Commande créée.');
    }

    #[Layout('layouts.master')]
    public function render()
    {
        return view('livewire.purchase-order-create');
    }

    #[\Livewire\Attributes\On('supplierCreated')]
    public function onSupplierCreated(array $supplier): void
    {
        $this->suppliers = Supplier::orderBy('name')->pluck('name','id')->toArray();
        $this->supplier_id = (int) ($supplier['id'] ?? 0);
    }
}
