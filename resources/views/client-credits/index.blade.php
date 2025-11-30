@extends('layouts.master')
@section('title','Credit exception')
@section('content')
<div>
  @include('components.flash')
  <livewire:client-credits-table />
</div>
@endsection
