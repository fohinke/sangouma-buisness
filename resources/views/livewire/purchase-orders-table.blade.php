<div>
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
      <h1 class="mb-1">
        <i class="bi bi-bag-check"></i>
        Commandes fournisseurs
      </h1>
      <div class="text-muted small">
        Gérez vos commandes, statuts, réceptions et montants en un coup d'œil.
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      @can('view reports')
        <a href="{{ route('purchase-orders.export') }}" class="btn btn-outline-secondary">
          <i class="bi bi-download"></i> Exporter CSV
        </a>
      @endcan
      <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvelle commande
      </a>
    </div>
  </div>

  @include('components.flash')

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label text-muted small mb-1">Recherche</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text"
                   class="form-control"
                   placeholder="Rechercher (code, fournisseur)"
                   wire:model.live.debounce.300ms="search">
          </div>
        </div>

        <div class="col-md-2">
          <label class="form-label text-muted small mb-1">Statut commande</label>
          <select class="form-select" wire:model.live="status">
            <option value="">Tous</option>
            <option value="en_attente">En attente</option>
            <option value="payee">Payée</option>
            <option value="livree">Réceptionnée</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label text-muted small mb-1">Statut réception</label>
          <select class="form-select" wire:model.live="delivery">
            <option value="">Toutes</option>
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="livree">Réceptionnée</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label text-muted small mb-1">Nombre par page</label>
          <select class="form-select" wire:model.live="perPage">
            @foreach($allowedPerPage as $n)
              <option value="{{ $n }}">{{ $n }} / page</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2 text-md-end">
          <label class="form-label text-muted small mb-1 d-block">Résultats</label>
          <div class="fw-semibold">
            {{ $orders->total() }} commande{{ $orders->total() > 1 ? 's' : '' }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Liste des commandes fournisseurs
      </div>
      <div>
        {{ $orders->links() }}
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th role="button" wire:click="sortBy('code')">
              Code
              @if($sortField === 'code')
                <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Fournisseur</th>
            <th role="button" wire:click="sortBy('ordered_at')">
              Date
              @if($sortField === 'ordered_at')
                <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Statut</th>
            <th>Réception</th>
            <th class="text-end">Total TTC</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $o)
            @php
              $t = (int) $o->items->sum('qty');
              $r = (int) $o->items->sum('received_qty');
              $deliveryStatus = $t === 0
                ? 'en_attente'
                : ($r === 0 ? 'en_attente' : ($r < $t ? 'en_cours' : 'livree'));
              $isLate = $o->ordered_at
                && $o->status === 'en_attente'
                && $o->ordered_at->lt(now()->subDays($this->lateAfterDays));
              $orderStatusForBadge = $isLate ? 'en_retard' : $o->status;
            @endphp
            <tr>
              <td>
                <a href="{{ route('purchase-orders.show', $o) }}" class="fw-semibold text-decoration-none">
                  {{ $o->code }}
                </a>
              </td>
              <td>
                <div class="fw-semibold">{{ optional($o->supplier)->name ?? '-' }}</div>
              </td>
              <td>
                {{ optional($o->ordered_at)->format('Y-m-d') ?? '-' }}
              </td>
              <td>
                @include('components.status-badge', ['value' => $orderStatusForBadge])
              </td>
              <td>
                @include('components.status-badge', ['value' => $deliveryStatus])
              </td>
              <td class="text-end">
                <span class="fw-semibold">
                  {{ number_format((float) $o->total_ttc, 2, ',', ' ') }} GNF
                </span>
              </td>
              <td class="text-end">
                <a href="{{ route('purchase-orders.show', $o) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                Aucune commande trouvée. Créez une nouvelle commande ou ajustez vos filtres.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        Affichage de {{ $orders->firstItem() }} à {{ $orders->lastItem() }} sur {{ $orders->total() }} commande{{ $orders->total() > 1 ? 's' : '' }}.
      </div>
      <div>
        {{ $orders->links() }}
      </div>
    </div>
  </div>
</div>

