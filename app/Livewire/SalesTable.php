<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SalesTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $status = '';
    public string $delivery = '';
    public string $sortField = 'sold_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public int $page = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'delivery' => ['except' => ''],
        'sortField' => ['except' => 'sold_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
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

    public function getSalesProperty()
    {
        $query = Sale::query()
            ->with(['client','items'])
            ->withSum('payments as paid_amount','amount')
            ->withSum('items as total_qty', 'qty')
            ->withSum('items as delivered_qty', 'delivered_qty')
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('code', 'like', "%{$s}%")
                        ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$s}%"));
                });
            })
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->when($this->delivery !== '', function ($q) {
                if (!in_array($this->delivery, ['en_attente','en_cours','livree'], true)) {
                    return;
                }
                // Dérive le statut livraison à partir des quantités
                if ($this->delivery === 'en_attente') {
                    // Aucun article OU rien livré
                    $q->havingRaw('COALESCE(total_qty,0) = 0 OR COALESCE(delivered_qty,0) = 0');
                } elseif ($this->delivery === 'en_cours') {
                    // Une partie livrée mais pas tout
                    $q->havingRaw('COALESCE(delivered_qty,0) > 0 AND COALESCE(delivered_qty,0) < COALESCE(total_qty,0)');
                } elseif ($this->delivery === 'livree') {
                    // Tout livré (et au moins 1 article)
                    $q->havingRaw('COALESCE(total_qty,0) > 0 AND COALESCE(delivered_qty,0) >= COALESCE(total_qty,0)');
                }
            });

        if ($this->sortField === 'client') {
            $query->leftJoin('clients', 'clients.id', '=', 'sales.client_id')
                ->orderBy('clients.name', $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.sales-table', [
            'sales' => $this->sales,
        ]);
    }
}
