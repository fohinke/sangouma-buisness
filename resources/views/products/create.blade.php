@extends('layouts.master')
@section('title','Nouveau produit')
@section('content')
<div>
    <h1 class="mb-3"><i class="bi bi-plus-circle"></i> Nouveau produit</h1>
    @include('components.flash')
    <form method="post" action="{{ route('products.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Référence</label>
            <input name="sku" class="form-control" value="{{ old('sku') }}" required>
            @error('sku')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Fournisseur</label>
            <select name="supplier_id" class="form-select">
                @foreach($suppliers as $id=>$n)
                    <option value="{{ $id }}">{{ $n }}</option>
                @endforeach
            </select>
            @error('supplier_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Prix achat</label>
            <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price',0) }}" required>
            @error('purchase_price')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Prix vente</label>
            <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price',0) }}" required>
            @error('sale_price')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" value="{{ old('stock',0) }}" required>
            @error('stock')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Stock min</label>
            <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock',0) }}">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
    @if($errors->any())
      <div class="alert alert-danger mt-3">
        <ul class="mb-0">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif
</div>
@endsection

