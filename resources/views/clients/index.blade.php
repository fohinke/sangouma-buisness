﻿@extends('layouts.master')
@section('title','Clients')
@section('content')
<div>
  @include('components.flash')
  <livewire:clients-table />
</div>
@endsection
