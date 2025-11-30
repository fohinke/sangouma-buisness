<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;

class ClientSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public ?string $placeholder = 'Chercher un client (nom, email, telephone)';
    public bool $showCreate = false;
    public string $new_name = '';
    public string $new_email = '';
    public string $new_phone = '';

    public function updatedQuery(): void
    {
        $q = trim($this->query);
        if ($q === '') { $this->results = []; $this->showCreate = false; return; }
        $this->results = Client::query()
            ->where('name','like',"%{$q}%")
            ->orWhere('email','like',"%{$q}%")
            ->orWhere('phone','like',"%{$q}%")
            ->orderBy('name')->limit(7)
            ->get(['id','name','email','phone'])
            ->map(fn($c)=>['id'=>$c->id,'name'=>$c->name,'email'=>$c->email,'phone'=>$c->phone])->toArray();
        $this->showCreate = empty($this->results);
    }

    public function pick(int $id): void
    {
        $c = collect($this->results)->firstWhere('id',$id);
        if ($c) {
            $this->dispatch('clientSelected', [
                'id' => $c['id'],
                'name' => $c['name'] ?? '',
                'email' => $c['email'] ?? '',
                'phone' => $c['phone'] ?? '',
            ]);
            $this->results = [];
            $this->query = '';
            $this->showCreate = false;
        }
    }

    public function createClient(): void
    {
        $data = $this->validate([
            'new_name' => ['required','string','min:2','max:255'],
            'new_email' => ['nullable','email','max:255'],
            'new_phone' => ['nullable','string','max:50'],
        ], [], [
            'new_name' => 'nom',
            'new_email' => 'email',
            'new_phone' => 'telephone',
        ]);

        $client = Client::create([
            'name' => $data['new_name'],
            'email' => $data['new_email'] ?? null,
            'phone' => $data['new_phone'] ?? null,
        ]);

        $this->dispatch('clientSelected', [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
        ]);

        $this->query = '';
        $this->results = [];
        $this->showCreate = false;
        $this->new_name = '';
        $this->new_email = '';
        $this->new_phone = '';
        session()->flash('success', 'Client cree et selectionne.');
    }

    public function render()
    {
        return view('livewire.client-search');
    }
}
