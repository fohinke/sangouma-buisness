@extends('layouts.master')
@section('title','Ajouter un credit')
@section('content')
<div class="card shadow-sm border-0">
  <div class="card-body">
    <h1 class="h4 mb-3">Ajouter un credit exceptionnel</h1>
    @include('components.flash')
    <form method="post" action="{{ route('client-credits.store') }}" class="row g-3">
      @csrf
      <div class="col-md-6">
        <label class="form-label">Client</label>
        <input type="hidden" name="client_id" id="client_id_field" value="{{ old('client_id') }}">
        <livewire:client-search :placeholder="'Chercher ou creer un client (nom, email, telephone)'" />
        <div id="selected-client-box" class="alert alert-info py-2 px-3 d-flex justify-content-between align-items-center mb-0 mt-2 d-none">
          <span class="fw-semibold" id="selected-client-name"></span>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="reset-client">Changer</button>
        </div>
        @error('client_id')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Montant (GNF)</label>
        <input type="text" inputmode="decimal" name="amount" id="amount-create" data-money-helper="amount-helper-create" class="form-control amount-input" required value="{{ old('amount') }}">
        <div class="form-text text-muted" id="amount-helper-create"></div>
        @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3">
        <label class="form-label">Date credit</label>
        <input type="datetime-local" name="credited_at" class="form-control" value="{{ old('credited_at') ?? now()->format('Y-m-d\TH:i') }}">
        @error('credited_at')<div class="text-danger small">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-4">
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
        <a href="{{ route('client-credits.index') }}" class="btn btn-outline-secondary">Annuler</a>
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
</div>
@endsection
@push('body')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('client_id_field');
    const box = document.getElementById('selected-client-box');
    const nameEl = document.getElementById('selected-client-name');
    const resetBtn = document.getElementById('reset-client');
    const initialName = @json($clients[old('client_id')] ?? '');
    if (input && input.value && initialName) {
      nameEl.textContent = initialName;
      box?.classList.remove('d-none');
    }
    resetBtn?.addEventListener('click', function () {
      if (input) input.value = '';
      box?.classList.add('d-none');
      nameEl.textContent = '';
    });

    window.addEventListener('clientSelected', function(event) {
      const detail = event.detail || {};
      if (input) input.value = detail.id || '';
      if (detail.name) {
        nameEl.textContent = detail.name;
        box?.classList.remove('d-none');
      } else {
        box?.classList.add('d-none');
        nameEl.textContent = '';
      }
    });
  });
</script>
@endpush
