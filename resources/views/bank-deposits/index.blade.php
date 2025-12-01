@extends('layouts.master')
@section('title','Depot bancaire')
@section('content')
@include('components.flash')
<livewire:bank-deposits-table />
@endsection
