<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 10;
    public int $page = 1;
    public bool $lowStockOnly = false;
    public string $minPrice = '';
    public string $maxPrice = '';

    // Modal state and form fields
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $sku = '';
    public string $supplier_id = '';
    public string $purchase_price = '';
    public string $sale_price = '';
    public string $stock = '';
    public string $min_stock = '';
    public array $modalSupplierOptions = [];
    public int $modalKey = 0;
    public bool $deleteConfirmOpen = false;
    public ?int $pendingDeleteId = null;
    public int $deleteKey = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
        'lowStockOnly' => ['except' => false],
        'minPrice' => ['except' => ''],
        'maxPrice' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->modalSupplierOptions = Supplier::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($s) => ['id' => (string) $s->id, 'name' => $s->name])
            ->toArray();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }
    public function updatingLowStockOnly(): void { $this->resetPage(); }
    public function updatingMinPrice(): void { $this->resetPage(); }
    public function updatingMaxPrice(): void { $this->resetPage(); }

    public function updated(string $name): void
    {
        $this->validateOnly($name);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function getProductsProperty()
    {
        $query = Product::query()
            ->with('supplier')
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('sku', 'like', "%{$s}%");
                });
            })
            ->when($this->lowStockOnly, function ($q) {
                $q->whereNotNull('min_stock')->whereColumn('stock', '<=', 'min_stock');
            })
            ->when($this->minPrice !== '', function ($q) {
                $q->where('sale_price', '>=', (float) $this->minPrice);
            })
            ->when($this->maxPrice !== '', function ($q) {
                $q->where('sale_price', '<=', (float) $this->maxPrice);
            });

        if ($this->sortField === 'supplier') {
            $query->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
                ->select('products.*')
                ->orderBy('suppliers.name', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.products-table', [
            'products' => $this->products,
        ]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->sku = '';
        $this->supplier_id = '';
        $this->purchase_price = '';
        $this->sale_price = '';
        $this->stock = '';
        $this->min_stock = '';
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalKey++;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = Product::findOrFail($id);
        $this->editingId = $p->id;
        $this->name = (string) $p->name;
        $this->sku = (string) ($p->sku ?? '');
        $this->supplier_id = $p->supplier_id ? (string) $p->supplier_id : '';
        $this->purchase_price = $p->purchase_price !== null ? (string) (float) $p->purchase_price : '';
        $this->sale_price = $p->sale_price !== null ? (string) (float) $p->sale_price : '';
        $this->stock = $p->stock !== null ? (string) (int) $p->stock : '';
        $this->min_stock = $p->min_stock !== null ? (string) (int) $p->min_stock : '';
        $this->modalKey++;
        $this->showModal = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->pendingDeleteId = $id;
        $this->deleteKey++;
        $this->deleteConfirmOpen = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteConfirmOpen = false;
        $this->pendingDeleteId = null;
    }

    public function deleteProduct(): void
    {
        if ($this->pendingDeleteId) {
            $product = Product::find($this->pendingDeleteId);
            if ($product) {
                $product->is_active = !$product->is_active;
                $product->save();
                session()->flash('success', $product->is_active ? 'Produit activé.' : 'Produit désactivé.');
            }
        }
        $this->cancelDelete();
    }

    public function toggleActive(int $id): void
    {
        $product = Product::find($id);
        if ($product) {
            $product->is_active = !$product->is_active;
            $product->save();
            session()->flash('success', $product->is_active ? 'Produit activé.' : 'Produit désactivé.');
        }
    }

    public function saveProduct(): void
    {
        $data = $this->validate();
        $payload = [
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'supplier_id' => isset($data['supplier_id']) && $data['supplier_id'] !== null && $data['supplier_id'] !== '' ? (int) $data['supplier_id'] : null,
            'purchase_price' => isset($data['purchase_price']) && $data['purchase_price'] !== null && $data['purchase_price'] !== '' ? (float) $data['purchase_price'] : null,
            'sale_price' => isset($data['sale_price']) && $data['sale_price'] !== null && $data['sale_price'] !== '' ? (float) $data['sale_price'] : null,
            'stock' => isset($data['stock']) && $data['stock'] !== null && $data['stock'] !== '' ? (int) $data['stock'] : null,
            'min_stock' => isset($data['min_stock']) && $data['min_stock'] !== null && $data['min_stock'] !== '' ? (int) $data['min_stock'] : null,
        ];

        if ($this->editingId) {
            $p = Product::findOrFail($this->editingId);
            $p->update($payload);
            session()->flash('success', 'Produit mis à jour.');
        } else {
            Product::create($payload);
            session()->flash('success', 'Produit créé.');
        }

        $this->showModal = false;
        $this->resetForm();
    }
}

