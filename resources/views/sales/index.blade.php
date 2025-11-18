@extends('layouts.master')
@section('title','Ventes')
@section('content')
<div>
  @include('components.flash')
  <livewire:sales-table />
</div>
@endsection

