<div>
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3" style="background: linear-gradient(135deg, #0d274f, #1b6fd8); color: #fff; border-radius: 12px;">
      <div>
        <div class="text-uppercase small opacity-75">Credits exceptionnels</div>
        <h1 class="mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-wallet2"></i>
          <span>Credit exception</span>
        </h1>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <div class="badge bg-light text-dark px-3 py-2">Status : Solde / Partiel / En attente</div>
        <button type="button" class="btn btn-light text-primary fw-semibold" wire:click="openCreate">
          <i class="bi bi-plus-circle"></i> Ajouter un credit
        </button>
      </div>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Rechercher (client, methode, notes)" wire:model.live.debounce.300ms="search">
          </div>
        </div>
        <div class="col-md-2 ms-auto">
          <select class="form-select" wire:model.live="perPage">
            <option value="10">10 / page</option>
            <option value="15">15 / page</option>
            <option value="25">25 / page</option>
            <option value="50">50 / page</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">{{ $credits->total() }} resultats</div>
      <div>{{ $credits->links() }}</div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th role="button" wire:click="sortBy('client')">
              Client
              @if($sortField==='client')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('amount')" class="text-end">
              Montant initial
              @if($sortField==='amount')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th class="text-end">Rembourse</th>
            <th class="text-end">Reste</th>
            <th role="button" wire:click="sortBy('credited_at')">
              Date credit
              @if($sortField==='credited_at')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Mode</th>
           
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($credits as $c)
            @php
              $refunded = (float) ($c->refunded_amount ?? 0);
              $remaining = (float) ($c->remaining_amount ?? max(0, ($c->amount ?? 0) - $refunded));
              $status = 'En attente';
              if ($refunded >= (float) ($c->amount ?? 0) && ($c->amount ?? 0) > 0) {
                  $status = 'Solde';
              } elseif ($refunded > 0) {
                  $status = 'Partiel';
              }
            @endphp
            <tr>
              <td>{{ optional($c->client)->name }}</td>
              <td class="text-end">{{ number_format((float)$c->amount, 2, ',', ' ') }} GNF</td>
              <td class="text-end">{{ number_format($refunded, 2, ',', ' ') }} GNF</td>
              <td class="text-end fw-semibold">{{ number_format($remaining, 2, ',', ' ') }} GNF</td>
              <td>{{ optional($c->credited_at)->format('Y-m-d') }}</td>
              <td>{{ $c->method }}</td>

              <td>
                @if($status === 'Solde')
                  <span class="badge bg-success">Solde</span>
                @elseif($status === 'Partiel')
                  <span class="badge bg-warning text-dark">Partiel</span>
                @else
                  <span class="badge bg-secondary">En attente</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('client-credits.show', $c->id) }}" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-eye"></i> Details
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">Aucun credit enregistre.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">{{ $credits->total() }} resultats</div>
      <div>{{ $credits->links() }}</div>
    </div>
  </div>

  <!-- Modal ajout credit -->
  <div class="modal fade @if($showModal) show d-block @endif" wire:key="modal-{{ $modalKey }}" tabindex="-1" @if($showModal) style="display:block" @endif aria-hidden="{{ $showModal ? 'false' : 'true' }}">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Ajouter un credit</h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
        </div>
        <form wire:submit.prevent="saveCredit" action="{{ route('client-credits.store') }}" method="post">
          @csrf
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Client</label>
                <input type="hidden" name="client_id" wire:model.live="client_id">
                <livewire:client-search :placeholder="'Chercher ou creer un client (nom, email, telephone)'" wire:key="client-search-{{ $modalKey }}" />
                @if($client_name)
                  <div class="alert alert-info py-2 px-3 d-flex justify-content-between align-items-center mb-2 mt-2">
                    <span class="fw-semibold">{{ $client_name }}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="resetClient">Changer</button>
                  </div>
                @endif
                @error('client_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Montant (GNF)</label>
                <input type="text" inputmode="decimal" name="amount" id="amount-input-{{ $modalKey }}" data-money-helper="amount-helper-{{ $modalKey }}" class="form-control @error('amount') is-invalid @enderror amount-input" wire:model.live="amount" required>
                <div class="form-text text-muted" id="amount-helper-{{ $modalKey }}"></div>
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Date credit</label>
                <input type="datetime-local" name="credited_at" class="form-control @error('credited_at') is-invalid @enderror" wire:model.live="credited_at">
                @error('credited_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Methode</label>
                <input type="text" name="method" class="form-control @error('method') is-invalid @enderror" wire:model.live="method">
                @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" wire:model.live="notes" rows="3"></textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" wire:click="closeModal">Annuler</button>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
