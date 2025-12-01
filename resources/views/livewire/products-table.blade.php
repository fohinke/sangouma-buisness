<div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><i class="bi bi-box-seam"></i> Produits</h1>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" id="lowStockTopSwitch" wire:model.live="lowStockOnly">
      <label class="form-check-label" for="lowStockTopSwitch">Stock bas</label>
    </div>
  </div>

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Rechercher (nom, référence)" wire:model.live.debounce.300ms="search">
          </div>
        </div>
        <div class="col-md-2">
          <div class="input-group">
            <span class="input-group-text bg-white">GNF</span>
            <input type="number" step="0.01" class="form-control" placeholder="Min" wire:model.live.debounce.300ms="minPrice">
          </div>
        </div>
        <div class="col-md-2">
          <div class="input-group">
            <span class="input-group-text bg-white">GNF</span>
            <input type="number" step="0.01" class="form-control" placeholder="Max" wire:model.live.debounce.300ms="maxPrice">
          </div>
        </div>
        <div class="col-md-4 d-flex justify-content-end align-items-center gap-2">
          <button type="button" class="btn btn-primary" wire:click="openCreate">
            <i class="bi bi-plus-circle"></i> Nouveau produit
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
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
            <th role="button" wire:click="sortBy('sku')">
              Référence
              @if($sortField==='sku')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('supplier')">
              Fournisseur
              @if($sortField==='supplier')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" class="text-end" wire:click="sortBy('purchase_price')">
              Prix achat
              @if($sortField==='purchase_price')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" class="text-end" wire:click="sortBy('sale_price')">
              Prix vente
              @if($sortField==='sale_price')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" class="text-center" wire:click="sortBy('stock')">
              Stock
              @if($sortField==='stock')
                <i class="bi bi-caret-{{ $sortDirection==='asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $p)
            @php
              $variant = 'success';
              if (!is_null($p->min_stock) && $p->stock <= $p->min_stock) $variant = 'danger';
              elseif (!is_null($p->min_stock) && $p->stock < $p->min_stock + 5) $variant = 'warning';
              $rowClass = $variant === 'danger' ? 'table-danger' : ($variant === 'warning' ? 'table-warning' : '');
            @endphp
            <tr class="{{ $rowClass }} {{ $p->is_active ? '' : 'table-secondary' }}" wire:key="p-{{ $p->id }}">
              <td>
                <a href="{{ route('products.show', $p) }}" class="fw-semibold text-decoration-none">
                  {{ $p->name }}
                </a>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary border">{{ $p->sku }}</span>
              </td>
              <td>{{ $p->supplier->name ?? '—' }}</td>
              <td class="text-end">{{ number_format((float) $p->purchase_price, 2, ',', ' ') }} GNF</td>
              <td class="text-end">{{ number_format((float) $p->sale_price, 2, ',', ' ') }} GNF</td>
              <td class="text-center">
                <span class="badge bg-{{ $variant }}">{{ $p->stock }}</span>
              </td>
              <td class="text-end d-flex justify-content-end gap-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click.prevent.stop="openEdit({{ $p->id }})">
                  <i class="bi bi-pencil"></i>
                </button>
                @if($p->is_active)
                  <button type="button" class="btn btn-sm btn-outline-warning" wire:click="toggleActive({{ $p->id }})">
                    <i class="bi bi-toggle-off"></i> Désactiver
                  </button>
                @else
                  <button type="button" class="btn btn-sm btn-outline-success" wire:click="toggleActive({{ $p->id }})">
                    <i class="bi bi-toggle-on"></i> Activer
                  </button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Aucun produit trouvé.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">{{ $products->total() }} résultats</div>
      <div>{{ $products->links() }}</div>
    </div>
  </div>
  
  <!-- Modal Create/Edit Product -->
  <div class="modal fade @if($showModal) show d-block @endif" id="productModal" wire:key="modal-{{ $modalKey }}" tabindex="-1" @if($showModal) style="display:block" @endif aria-hidden="{{ $showModal ? 'false' : 'true' }}">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $editingId ? 'Modifier le produit' : 'Nouveau produit' }}</h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
        </div>
        <form wire:submit.prevent="saveProduct">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Référence</label>
                <input type="text" class="form-control @error('sku') is-invalid @enderror" wire:model.blur="sku">
                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Fournisseur</label>
                <select class="form-select @error('supplier_id') is-invalid @enderror" wire:model.blur="supplier_id">
                  <option value="">Sélectionner un fournisseur</option>
                  @foreach($modalSupplierOptions as $opt)
                    <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                  @endforeach
                </select>
                @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Prix achat (GNF)</label>
                <input type="text" inputmode="decimal" class="form-control amount-input @error('purchase_price') is-invalid @enderror" wire:model.live="purchase_price" data-money-helper="purchase-helper-{{ $modalKey }}">
                <div class="form-text text-muted" id="purchase-helper-{{ $modalKey }}"></div>
                @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Prix vente (GNF)</label>
                <input type="text" inputmode="decimal" class="form-control amount-input @error('sale_price') is-invalid @enderror" wire:model.live="sale_price" data-money-helper="sale-helper-{{ $modalKey }}">
                <div class="form-text text-muted" id="sale-helper-{{ $modalKey }}"></div>
                @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Stock</label>
                <input type="number" class="form-control @error('stock') is-invalid @enderror" wire:model.blur="stock">
                @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Seuil (min stock)</label>
                <input type="number" class="form-control @error('min_stock') is-invalid @enderror" wire:model.blur="min_stock">
                @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
