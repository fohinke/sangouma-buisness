<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrdersTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public ?string $status = null;
    public ?string $delivery = null;
    public int $perPage = 15;
    public string $sortField = 'ordered_at';
    public string $sortDirection = 'desc';
    public int $lateAfterDays = 15;

    public array $allowedPerPage = [10, 15, 25, 50];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => null],
        'delivery' => ['except' => null],
        'perPage' => ['except' => 15],
        'sortField' => ['except' => 'ordered_at'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingDelivery(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

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

    public function getOrdersProperty()
    {
        $q = trim($this->search);

        $perPage = $this->perPage;
        if (!in_array($perPage, $this->allowedPerPage, true)) {
            $perPage = 15;
        }

        $query = PurchaseOrder::with('supplier', 'items')
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($x) use ($q) {
                    $x->where('code', 'like', "%{$q}%")
                        ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($this->status, fn($b) => $b->where('status', $this->status))
            ->when($this->delivery, function ($b) {
                if ($this->delivery === 'en_attente') {
                    $b->whereHas('items', function ($q) {
                        $q->whereColumn('received_qty', '<', 'qty');
                    });
                } elseif ($this->delivery === 'en_cours') {
                    $b->whereHas('items', function ($q) {
                        $q->whereColumn('received_qty', '>', '0')
                            ->whereColumn('received_qty', '<', 'qty');
                    });
                } elseif ($this->delivery === 'livree') {
                    $b->whereDoesntHave('items', function ($q) {
                        $q->whereColumn('received_qty', '<', 'qty');
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($perPage);
    }

    public function render()
    {
        return view('livewire.purchase-orders-table', [
            'orders' => $this->orders,
        ]);
    }
}
