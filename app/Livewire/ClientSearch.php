<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;

class ClientSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public ?string $placeholder = 'Chercher un client (nom, email, téléphone)';

    public function updatedQuery(): void
    {
        $q = trim($this->query);
        if ($q === '') { $this->results = []; return; }
        $this->results = Client::query()
            ->where('name','like',"%{$q}%")
            ->orWhere('email','like',"%{$q}%")
            ->orWhere('phone','like',"%{$q}%")
            ->orderBy('name')->limit(7)
            ->get(['id','name','email','phone'])
            ->map(fn($c)=>['id'=>$c->id,'name'=>$c->name,'email'=>$c->email,'phone'=>$c->phone])->toArray();
    }

    public function pick(int $id): void
    {
        $c = collect($this->results)->firstWhere('id',$id);
        if ($c) {
            $this->dispatch('clientSelected', $c);
            $this->results = [];
            $this->query = '';
        }
    }

    public function render()
    {
        return view('livewire.client-search');
    }
}
