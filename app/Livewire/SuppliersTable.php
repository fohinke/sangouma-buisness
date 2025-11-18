<?php

namespace App\Livewire;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliersTable extends Component
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
    public string $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
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

    public function getSuppliersProperty()
    {
        $q = Supplier::query()
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
            ? Rule::unique('suppliers', 'email')->ignore($this->editingId)
            : Rule::unique('suppliers', 'email');

        return [
            'name' => ['required','string','max:255'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255', $emailRule],
            'address' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
            // status géré en interne (active/inactive)
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
        $this->status = '';
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalKey++;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function openEdit(int $id): void
    {
        $s = Supplier::findOrFail($id);
        $this->editingId = $s->id;
        $this->name = (string) $s->name;
        $this->phone = (string) ($s->phone ?? '');
        $this->email = (string) ($s->email ?? '');
        $this->address = (string) ($s->address ?? '');
        $this->notes = (string) ($s->notes ?? '');
        $this->status = (string) ($s->status ?? '');
        $this->modalKey++;
        $this->showModal = true;
    }

    public function saveSupplier(): void
    {
        $data = $this->validate();
        if ($this->editingId) {
            $s = Supplier::findOrFail($this->editingId);
            $s->update([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            session()->flash('success', 'Fournisseur mis à jour.');
        } else {
            Supplier::create([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'active',
            ]);
            session()->flash('success', 'Fournisseur créé.');
        }
        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $s = Supplier::find($id);
        if (!$s) return;
        $current = strtolower((string) ($s->status ?? ''));
        // Considère '', 'active' ou 'actif' comme actif pour que le premier clic désactive
        $isActive = in_array($current, ['active','actif',''], true);
        $s->status = $isActive ? 'inactive' : 'active';
        $s->save();
        session()->flash('success', $s->status === 'active' ? 'Fournisseur activé.' : 'Fournisseur désactivé.');
    }

    public function render()
    {
        return view('livewire.suppliers-table', [
            'suppliers' => $this->suppliers,
        ]);
    }
}
