<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SequenceService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Composant Livewire: création de vente.
 */
class SalesCreate extends Component
{
    public array $items = [];
    public ?int $client_id = null;
    public ?string $notes = null;

    // Pour le select de clients
    public array $clients = [];

    public float $total = 0.0;

    public function mount(): void
    {
        $this->clients = Client::orderBy('name')->pluck('name', 'id')->toArray();
        $this->items = [];
        $this->total = 0.0;
    }

    #[On('productSelected')]
    public function onProductSelected(array $product): void
    {
        // Ajoute une ligne depuis l'autocomplétion (prix de vente par défaut)
        $this->items[] = [
            'product_id' => (int) ($product['id'] ?? 0),
            'name' => (string) ($product['name'] ?? 'Produit'),
            'qty' => 1,
            'unit_price' => (float) ($product['sale_price'] ?? 0),
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
        // Normalise les entrées
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

    private function validateStock(): void
    {
        // Agrège les quantités par produit et compare au stock courant
        $requested = [];
        foreach ($this->items as $row) {
            $pid = (int)($row['product_id'] ?? 0);
            if ($pid <= 0) { continue; }
            $requested[$pid] = ($requested[$pid] ?? 0) + (int)($row['qty'] ?? 0);
        }
        if (!$requested) { return; }
        $stocks = Product::whereIn('id', array_keys($requested))->pluck('stock','id');
        foreach ($requested as $pid => $qty) {
            $stock = (int) ($stocks[$pid] ?? 0);
            if ($qty > $stock) {
                $name = optional(Product::find($pid))->name ?? 'Produit';
                $this->addError('items', "Le produit {$name} n'a que {$stock} en stock.");
            }
        }
        // Ne pas lancer d'exception ; laisser Livewire afficher l'erreur et bloquer la sauvegarde.
    }

    #[On('clientCreated')]
    public function onClientCreated(array $client): void
    {
        // Rafraîchir la liste et sélectionner le client nouvellement créé
        $this->clients = Client::orderBy('name')->pluck('name', 'id')->toArray();
        $this->client_id = (int) ($client['id'] ?? 0);
    }

    public function save(SequenceService $seq)
    {
        $this->resetErrorBag();
        $this->validate(
            [
                'client_id' => 'required|exists:clients,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ],
            [
                'client_id.required' => 'Le client est obligatoire.',
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
                'client_id' => 'client',
                'items' => 'articles',
            ]
        );

        $this->validateStock();
        if ($this->getErrorBag()->any()) {
            // Erreurs de stock présentes : rester sur la page et afficher les messages.
            return;
        }

        DB::transaction(function () use ($seq) {
            $code = $seq->next('INV');
            $sale = Sale::create([
                'client_id' => $this->client_id,
                'code' => $code,
                'status' => 'en_attente',
                'sold_at' => now(),
                'total_ht' => 0,
                'total_ttc' => 0,
                'notes' => $this->notes,
            ]);

            $total = 0.0;
            foreach ($this->items as $row) {
                $qty = (int) $row['qty'];
                $up = (float) $row['unit_price'];
                $subtotal = $qty * $up;
                $sale->items()->create([
                    'product_id' => (int) $row['product_id'],
                    'qty' => $qty,
                    'unit_price' => $up,
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }
            $sale->update(['total_ht' => $total, 'total_ttc' => $total]);
        });

        return redirect()->route('sales.index')->with('success', 'Vente créée.');
    }

    #[Layout('layouts.master')]
    public function render()
    {
        return view('livewire.sales-create');
    }
}

