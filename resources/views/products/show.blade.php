@extends('layouts.master')
@section('title', $product->name)
@section('content')
<div>
    <h1>{{ $product->name }}</h1>
    <div class="mb-3">
        <div><strong>Référence:</strong> {{ $product->sku }}</div>
        <div><strong>Fournisseur:</strong> {{ $product->supplier->name ?? '-' }}</div>
        <div><strong>Prix vente:</strong> {{ number_format($product->sale_price,2,',',' ') }}</div>
        <div><strong>Stock:</strong> {{ $product->stock }}</div>
    </div>
    <a href="{{ route('products.edit',$product) }}" class="btn btn-secondary">Modifier</a>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Retour</a>
</div>
@endsection
