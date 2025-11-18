<div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><i class="bi bi-truck"></i> Fournisseurs</h1>
    <div>
      <button type="button" class="btn btn-primary" wire:click="openCreate">
        <i class="bi bi-plus-circle"></i> Nouveau fournisseur
      </button>
    </div>
  </div>

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Rechercher (nom, téléphone, email)" wire:model.live.debounce.300ms="search">
          </div>
        </div>
        <div class="col-md-3">
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
      <div class="text-muted small"></div>
      <div>{{ $suppliers->links() }}</div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th role="button" wire:click="sortBy('name')">
              Nom
              @if($sortField==='name')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('phone')">
              Téléphone
              @if($sortField==='phone')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('email')">
              Email
              @if($sortField==='email')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($suppliers as $s)
            <tr>
              <td><a href="{{ route('suppliers.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->name }}</a></td>
              <td>{{ $s->phone }}</td>
              <td>{{ $s->email }}</td>
              <td>
                @php
                  $raw = strtolower((string)($s->status ?? ''));
                  $label = $raw === 'active' ? 'Actif' : ($raw === 'inactive' ? 'Inactif' : '—');
                @endphp
                <span class="badge bg-{{ $raw==='active' ? 'success' : 'secondary' }}">{{ $label }}</span>
              </td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEdit({{ $s->id }})"><i class="bi bi-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-outline-warning" wire:click="toggleStatus({{ $s->id }})" title="Activer/Désactiver">
                  @if(strtolower((string)($s->status ?? ''))==='active')
                    <i class="bi bi-toggle-off"></i>
                  @else
                    <i class="bi bi-toggle-on"></i>
                  @endif
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Aucun fournisseur trouvé.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">{{ $suppliers->total() }} résultats</div>
      <div>{{ $suppliers->links() }}</div>
    </div>
  </div>

  <!-- Modal Create Supplier -->
  <div class="modal fade @if($showModal) show d-block @endif" wire:key="modal-{{ $modalKey }}" tabindex="-1" @if($showModal) style="display:block" @endif aria-hidden="{{ $showModal ? 'false' : 'true' }}">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-truck"></i> {{ $editingId ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}</h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
        </div>
        <form wire:submit.prevent="saveSupplier">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Nom</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model.blur="phone">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.blur="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">Adresse</label>
                <input type="text" class="form-control @error('address') is-invalid @enderror" wire:model.blur="address">
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" wire:model.blur="notes" rows="3"></textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" wire:click="closeModal">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
