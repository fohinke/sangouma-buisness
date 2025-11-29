<div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><i class="bi bi-receipt"></i> Ventes</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('sales.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvelle vente
      </a>
      @can('view reports')
        <a href="{{ route('sales.export') }}" class="btn btn-outline-secondary">
          <i class="bi bi-download"></i> Exporter CSV
        </a>
      @endcan
    </div>
  </div>

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Rechercher (code, client)" wire:model.live.debounce.300ms="search">
          </div>
        </div>
        <div class="col-md-3">
          <select class="form-select" wire:model.live="status">
            <option value="">Statut paiement (tous)</option>
            <option value="en_attente">En attente</option>
            <option value="partiellement_payee">Partiellement payée</option>
            <option value="payee">Payée</option>
            <option value="livree">Livrée</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" wire:model.live="delivery">
            <option value="">Livraison (toutes)</option>
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="livree">Livrée</option>
          </select>
        </div>
        <div class="col-md-2">
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
      <div class="text-muted small">{{ $sales->total() }} résultats</div>
      <div>{{ $sales->links() }}</div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th role="button" wire:click="sortBy('code')">
              Code
              @if($sortField==='code')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('client')">
              Client
              @if($sortField==='client')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('sold_at')">
              Date
              @if($sortField==='sold_at')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Statut paiement</th>
            <th>Livraison</th>
            <th class="text-end">Total TTC (GNF)</th>
            <th class="text-end">Payé (GNF)</th>
            <th class="text-end">Reste (GNF)</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($sales as $s)
            @php
              $totalQty = (int) $s->items->sum('qty');
              $delivQty = (int) $s->items->sum('delivered_qty');
              $dl = $totalQty === 0 ? 'en_attente' : ($delivQty === 0 ? 'en_attente' : ($delivQty < $totalQty ? 'en_cours' : 'livree'));
              $paid = (float) ($s->paid_amount ?? 0);
              $rest = max(0, (float) ($s->total_ttc ?? 0) - $paid);
            @endphp
            <tr>
              <td><a href="{{ route('sales.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->code }}</a></td>
              <td>{{ optional($s->client)->name }}</td>
              <td>{{ optional($s->sold_at)->format('Y-m-d') }}</td>
              <td>@include('components.status-badge', ['value' => $s->status])</td>
              <td>@include('components.status-badge', ['value' => $dl])</td>
              <td class="text-end">{{ number_format((float)$s->total_ttc, 2, ',', ' ') }} GNF</td>
              <td class="text-end">{{ number_format($paid, 2, ',', ' ') }} GNF</td>
              <td class="text-end">{{ number_format($rest, 2, ',', ' ') }} GNF</td>
              <td class="text-end">
                <a href="{{ route('sales.show', $s) }}" class="btn btn-sm btn-outline-secondary">Détails</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">Aucune vente trouvée.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">{{ $sales->total() }} résultats</div>
      <div>{{ $sales->links() }}</div>
    </div>
  </div>
</div>
