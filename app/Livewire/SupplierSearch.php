<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;

class SupplierSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public ?string $placeholder = 'Chercher un fournisseur (nom, email, téléphone)';

    public function updatedQuery(): void
    {
        $q = trim($this->query);
        if ($q === '') { $this->results = []; return; }
        $this->results = Supplier::query()
            ->where('name','like',"%{$q}%")
            ->orWhere('email','like',"%{$q}%")
            ->orWhere('phone','like',"%{$q}%")
            ->orderBy('name')->limit(7)
            ->get(['id','name','email','phone'])
            ->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'email'=>$s->email,'phone'=>$s->phone])->toArray();
    }

    public function pick(int $id): void
    {
        $s = collect($this->results)->firstWhere('id',$id);
        if ($s) {
            $this->dispatch('supplierSelected', $s);
            $this->results = [];
            $this->query = '';
        }
    }

    public function render()
    {
        return view('livewire.supplier-search');
    }
}
