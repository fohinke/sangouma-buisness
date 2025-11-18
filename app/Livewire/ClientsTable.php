<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ClientsTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 15;
    public int $page = 1;

    // Modal state + form fields
    public bool $showModal = false;
    public int $modalKey = 0;
    public ?int $editingId = null;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $notes = '';
    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

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

    public function getClientsProperty()
    {
        $q = Client::query()
            ->when($this->statusFilter === 'inactive', fn($b) => $b->onlyTrashed())
            ->when($this->statusFilter === 'active', fn($b) => $b->whereNull('deleted_at'))
            ->when($this->statusFilter === '', fn($b) => $b->withTrashed())
            ->when($this->search, function ($qb) {
                $s = trim($this->search);
                $qb->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $q->paginate($this->perPage);
    }

    protected function rules(): array
    {
        $emailRule = $this->editingId
            ? Rule::unique('clients', 'email')->ignore($this->editingId)
            : Rule::unique('clients', 'email');

        return [
            'name' => ['required','string','max:255'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255', $emailRule],
            'address' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
        ];
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->notes = '';
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalKey++;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = Client::withTrashed()->findOrFail($id);
        $this->editingId = $c->id;
        $this->name = (string) $c->name;
        $this->phone = (string) ($c->phone ?? '');
        $this->email = (string) ($c->email ?? '');
        $this->address = (string) ($c->address ?? '');
        $this->notes = (string) ($c->notes ?? '');
        $this->modalKey++;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function saveClient(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $c = Client::withTrashed()->findOrFail($this->editingId);
            $c->update($data);
            session()->flash('success', 'Client mis à jour.');
        } else {
            Client::create($data);
            session()->flash('success', 'Client créé.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $c = Client::withTrashed()->find($id);
        if (!$c) return;
        if ($c->trashed()) {
            $c->restore();
            session()->flash('success', 'Client activé.');
        } else {
            $c->delete();
            session()->flash('success', 'Client désactivé.');
        }
    }

    public function render()
    {
        return view('livewire.clients-table', [
            'clients' => $this->clients,
        ]);
    }
}
