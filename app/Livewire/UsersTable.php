<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UsersTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 15;

    // Modal state + form fields
    public bool $showModal = false;
    public int $modalKey = 0;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $availableRoles = [];
    public array $selectedRoles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->availableRoles = Role::orderBy('name')->pluck('name')->toArray();
    }

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

    public function getUsersProperty()
    {
        $q = trim($this->search);

        return User::query()
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    protected function rules(): array
    {
        $base = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($this->editingId) {
            $base['email'] = ['required', 'email', 'max:255', 'unique:users,email,' . $this->editingId];
            $base['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        return $base;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
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
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = $user->roles->pluck('name')->all();
        $this->modalKey++;
        $this->showModal = true;
    }

    public function saveUser(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];
            if (!empty($data['password'])) {
                $payload['password'] = bcrypt($data['password']);
            }
            $user->update($payload);
            $user->syncRoles($this->selectedRoles);
            session()->flash('success', 'Utilisateur mis à jour.');
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
            $user->syncRoles($this->selectedRoles);
            session()->flash('success', 'Utilisateur créé.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.users-table', [
            'users' => $this->users,
        ]);
    }
}

