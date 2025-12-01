@extends('layouts.master')
@section('title','Detail depot bancaire')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="text-uppercase small text-muted mb-1">Depot bancaire</div>
    <h1 class="h4 mb-0">#{{ $deposit->id }} {!! $deposit->reference ? '<span class="badge bg-primary-subtle text-primary">Ref '.$deposit->reference.'</span>' : '' !!}</h1>
    <div class="text-muted small">Crée le {{ optional($deposit->created_at)->format('d/m/Y H:i') }}</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('bank-deposits.index') }}" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Retour
    </a>
  </div>
</div>
@include('components.flash')

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small mb-1">Montant</div>
        <div class="h3 mb-0">{{ number_format((float) $deposit->amount, 2, ',', ' ') }} GNF</div>
        <div class="text-success small mt-1"><i class="bi bi-check-circle-fill"></i> Dépôt confirmé</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small mb-1">Banque</div>
        <div class="fw-semibold">{{ $deposit->bank_name }}</div>
        <div class="text-muted small mt-1">Compte : {{ $deposit->account_number ?: '—' }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small mb-1">Date & méthode</div>
        <div class="fw-semibold">{{ optional($deposit->deposited_at)->format('d/m/Y H:i') }}</div>
        <div class="text-muted small mt-1">Méthode : {{ $deposit->method ?: '—' }}</div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white">
    <div class="fw-semibold">Notes</div>
    <div class="text-muted small">Informations complémentaires sur le dépôt</div>
  </div>
  <div class="card-body">
    <div class="p-3 bg-light rounded">
      {{ $deposit->notes ?: 'Aucune note' }}
    </div>
  </div>
</div>
@endsection
