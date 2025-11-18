<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Composant de recherche produit avec autocomplétion.
 * - Propriété publique $placeholder (optionnel)
 * - Émet 'productSelected' vers le parent avec: [id, name, sku, purchase_price, sale_price, stock]
 */
class ProductSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public ?string $placeholder = 'Rechercher produit (nom ou référence)';

    public function updatedQuery(): void
    {
        $q = trim($this->query);
        if ($q === '') {
            $this->results = [];
            return;
        }
        $this->results = Product::query()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('sku', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(7)
            ->get(['id','name','sku','purchase_price','sale_price','stock'])
            ->map(fn($p)=>[
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'purchase_price' => (float) $p->purchase_price,
                'sale_price' => (float) $p->sale_price,
                'stock' => (int) $p->stock,
            ])->toArray();
    }

    public function pick(int $id): void
    {
        $p = collect($this->results)->firstWhere('id', $id);
        if ($p) {
            $this->dispatch('productSelected', $p);
            $this->results = [];
            $this->query = '';
        }
    }

    public function render()
    {
        return view('livewire.product-search');
    }
}
