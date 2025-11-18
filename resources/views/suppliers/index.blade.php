@extends('layouts.master')
@section('title','Fournisseurs')

@section('content')
<div>
    @include('components.flash')
    <livewire:suppliers-table />
</div>
@endsection

