@extends('layouts.master')
@section('title','Produits')
@section('content')
<div>
    @include('components.flash')
    <livewire:products-table />
@endsection
