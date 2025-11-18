<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Component;

class QuickCreateClient extends Component
{
    public bool $open = false;
    public string $name = '';
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $address = null;
    public ?string $notes = null;

    public function toggle(): void
    {
        $this->open = !$this->open;
    }

    public function save()
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required', 'string', 'max:50',
                Rule::unique('clients','phone')->whereNull('deleted_at'),
            ],
            'email' => [
                'nullable', 'string', 'max:255',
                Rule::unique('clients','email')->whereNull('deleted_at'),
            ],
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.unique' => "Cet e-mail est déjà utilisé.",
        ]);

        $client = Client::create($data);

        $this->reset(['name','phone','email','address','notes']);
        $this->open = false;

        $this->dispatch('clientCreated', [
            'id' => $client->id,
            'name' => $client->name,
        ]);
    }

    public function render()
    {
        return view('livewire.quick-create-client');
    }
}
