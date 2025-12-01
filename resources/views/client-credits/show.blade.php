@extends('layouts.master')
@section('title','Details credit')
@section('content')
<div class="mb-3 d-flex gap-2 flex-wrap">
  <a href="{{ route('client-credits.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Retour
  </a>
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#refundModal">
    <i class="bi bi-cash-coin"></i> Nouveau remboursement
  </button>
</div>

@include('components.flash')

<div class="card border-0 shadow-lg overflow-hidden mb-4">
  <div class="p-4 text-white" style="background: radial-gradient(circle at 20% 20%, #1b6fd8, #0d274f);">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div>
        <div class="text-uppercase small opacity-75">Credit exceptionnel</div>
        <h2 class="mb-1">{{ optional($credit->client)->name ?? 'Client inconnu' }}</h2>
        <div class="d-flex align-items-center gap-3">
          <span class="badge bg-light text-dark">#{{ $credit->id }}</span>
          <span class="badge bg-primary-subtle text-white">Le {{ optional($credit->credited_at)->format('d/m/Y H:i') }}</span>
        </div>
      </div>
      <div class="d-flex flex-column text-end">
        <div class="fs-5 text-uppercase opacity-75">Montant initial</div>
        <div class="display-6 fw-bold">{{ number_format((float)$credit->amount, 2, ',', ' ') }} GNF</div>
      </div>
    </div>
  </div>
  <div class="card-body p-4">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="p-3 rounded-3 border bg-light">
          <div class="text-muted small text-uppercase mb-1">Rembourse</div>
          <div class="h4 mb-2 text-success">{{ number_format($refunded, 2, ',', ' ') }} GNF</div>
          <div class="text-muted">Mode : {{ $credit->method ?: '—' }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 rounded-3 border bg-light">
          <div class="text-muted small text-uppercase mb-1">Reste</div>
          <div class="h4 mb-2 text-warning">{{ number_format($remaining, 2, ',', ' ') }} GNF</div>
          <div class="text-muted">Notes : {{ $credit->notes ?: 'Aucune note' }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 rounded-3 border bg-light">
          <div class="text-muted small text-uppercase mb-1">Client</div>
          <div class="h5 mb-1">{{ optional($credit->client)->name ?? 'Inconnu' }}</div>
          <div class="text-muted small">Credit le {{ optional($credit->credited_at)->format('d/m/Y') }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Remboursements</h5>
      <small class="text-muted">{{ $credit->refunds->count() }} enregistrement(s)</small>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Montant</th>
          <th>Mode</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        @forelse($credit->refunds as $refund)
          <tr>
            <td>{{ optional($refund->refunded_at)->format('d/m/Y H:i') }}</td>
            <td class="fw-semibold">{{ number_format((float)$refund->amount, 2, ',', ' ') }} GNF</td>
            <td>{{ $refund->method ?: '—' }}</td>
            <td class="text-muted" style="max-width: 360px;">{{ $refund->notes ?: '—' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-4">Aucun remboursement enregistre.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal remboursement -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="refundModalLabel"><i class="bi bi-cash-coin"></i> Enregistrer un remboursement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form method="post" action="{{ route('client-credits.refunds.store', $credit->id) }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Montant (GNF)</label>
            <input type="text" name="amount" inputmode="decimal" data-money-helper="refund-amount-helper" class="form-control amount-input @error('amount') is-invalid @enderror" required value="{{ old('amount') }}">
            <div class="form-text text-muted" id="refund-amount-helper"></div>
            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Date remboursement</label>
            <input type="datetime-local" name="refunded_at" class="form-control @error('refunded_at') is-invalid @enderror" value="{{ old('refunded_at') ?? now()->format('Y-m-d\TH:i') }}">
            @error('refunded_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Mode</label>
            <input type="text" name="method" class="form-control @error('method') is-invalid @enderror" value="{{ old('method') }}">
            @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="alert alert-info mb-0">
            Reste a rembourser : <strong>{{ number_format($remaining, 2, ',', ' ') }} GNF</strong>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
