@extends('layouts.master')
@section('title','Nouveau depot bancaire')
@section('content')
<div class="card shadow-sm border-0">
  <div class="card-body">
    <h1 class="h4 mb-3">Nouveau depot bancaire</h1>
    @include('components.flash')
    <form method="post" action="{{ route('bank-deposits.store') }}" class="row g-3">
      @csrf
      <div class="col-md-4">
        <label class="form-label">Reference (optionnel)</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
        @error('reference')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-4">
        <label class="form-label">Banque</label>
        <input type="text" name="bank_name" class="form-control" required value="{{ old('bank_name') }}">
        @error('bank_name')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-4">
        <label class="form-label">Compte (optionnel)</label>
        <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
        @error('account_number')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Montant (GNF)</label>
        <input type="text" name="amount" inputmode="decimal" data-money-helper="deposit-amount-helper" class="form-control amount-input" required value="{{ old('amount') }}">
        <div class="form-text text-muted" id="deposit-amount-helper"></div>
        @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Date depot</label>
        <input type="datetime-local" name="deposited_at" class="form-control" value="{{ old('deposited_at') ?? now()->format('Y-m-d\\TH:i') }}">
        @error('deposited_at')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Methode (optionnel)</label>
        <input type="text" name="method" class="form-control" value="{{ old('method') }}">
        @error('method')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
        @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('bank-deposits.index') }}" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
@endsection

