@extends('layouts.master')
@section('title','Nouveau fournisseur')
@section('content')
<div class="container">
    <h1>Nouveau fournisseur</h1>
    @include('components.flash')
    <form method="post" action="{{ route('suppliers.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input name="name" class="form-control" required value="{{ old('name') }}">
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Téléphone</label>
            <input name="phone" class="form-control" required value="{{ old('phone') }}">
            @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Email (facultatif)</label>
            <input type="text" name="email" class="form-control" value="{{ old('email') }}">
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Adresse</label>
            <input name="address" class="form-control" value="{{ old('address') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-secondary" href="{{ route('suppliers.index') }}">Annuler</a>
        </div>
    </form>
</div>
@endsection



