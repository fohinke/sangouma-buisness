<div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Depot bancaire</h1>
    <button class="btn btn-primary" wire:click="openCreate">
      <i class="bi bi-plus-circle"></i> Nouveau depot
    </button>
  </div>

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label text-muted small mb-1">Recherche</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Ref, banque, compte, notes" wire:model.live.debounce.300ms="search">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small mb-1">Du</label>
          <input type="date" class="form-control" wire:model.live="from">
        </div>
        <div class="col-md-3">
          <label class="form-label text-muted small mb-1">Au</label>
          <input type="date" class="form-control" wire:model.live="to">
        </div>
        <div class="col-md-2">
          <label class="form-label text-muted small mb-1">Par page</label>
          <select class="form-select" wire:model.live="perPage">
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        {{ $deposits->total() }} resultat(s) |
        Total filtre : <strong>{{ number_format($totalAmount, 2, ',', ' ') }} GNF</strong>
      </div>
      <div>{{ $deposits->links() }}</div>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th role="button" wire:click="sortBy('reference')">
              Reference
              @if($sortField==='reference')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('bank_name')">
              Banque
              @if($sortField==='bank_name')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Compte</th>
            <th class="text-end" role="button" wire:click="sortBy('amount')">
              Montant
              @if($sortField==='amount')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('deposited_at')">
              Date depot
              @if($sortField==='deposited_at')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Methode</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($deposits as $d)
            <tr>
              <td>{{ $d->reference ?: '—' }}</td>
              <td>{{ $d->bank_name }}</td>
              <td>{{ $d->account_number ?: '—' }}</td>
              <td class="text-end">{{ number_format((float) $d->amount, 2, ',', ' ') }} GNF</td>
              <td>{{ optional($d->deposited_at)->format('d/m/Y H:i') }}</td>
              <td>{{ $d->method ?: '—' }}</td>
              <td class="text-end">
                <a href="{{ route('bank-deposits.show', $d->id) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Aucun depot enregistre.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">{{ $deposits->total() }} resultat(s)</div>
      <div>{{ $deposits->links() }}</div>
    </div>
  </div>

  <!-- Modal creation depot -->
  <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1" style="@if($showModal) display:block; background: rgba(0,0,0,.35); @endif" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg" wire:key="deposit-modal-{{ $modalKey }}">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-piggy-bank"></i> Nouveau depot</h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
        </div>
        <form wire:submit.prevent="saveDeposit" class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Reference (optionnel)</label>
              <input type="text" class="form-control @error('reference') is-invalid @enderror" wire:model.live="reference">
              @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Banque</label>
              <input type="text" class="form-control @error('bank_name') is-invalid @enderror" wire:model.live="bank_name" required>
              @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Compte (optionnel)</label>
              <input type="text" class="form-control @error('account_number') is-invalid @enderror" wire:model.live="account_number">
              @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Montant (GNF)</label>
              <input type="text" inputmode="decimal" class="form-control amount-input @error('amount') is-invalid @enderror" wire:model.live="amount" wire:blur="formatAmount" data-money-helper="deposit-modal-amount">
              <div class="form-text text-muted" id="deposit-modal-amount">
                @if($amountPreview) Aperçu: {{ $amountPreview }} @endif
              </div>
              @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Date depot</label>
              <input type="datetime-local" class="form-control @error('deposited_at') is-invalid @enderror" wire:model.live="deposited_at">
              @error('deposited_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Methode (optionnel)</label>
              <input type="text" class="form-control @error('method') is-invalid @enderror" wire:model.live="method">
              @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea class="form-control @error('notes') is-invalid @enderror" rows="3" wire:model.live="notes"></textarea>
              @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" wire:click="closeModal">Annuler</button>
          <button type="button" class="btn btn-primary" wire:click="saveDeposit">Enregistrer</button>
        </div>
      </div>
    </div>
  </div>
</div>
