<div>
  <h1 class="mb-3"><i class="bi bi-bag-plus"></i> Nouvelle commande fournisseur </h1>

  <div class="row g-3 mb-1">
    <div class="col-md-6">
      <label class="form-label">Fournisseur</label>
      <select class="form-select mb-2" wire:model="supplier_id">
        <option value="">-- Choisir --</option>
        @foreach($suppliers as $id => $name)
          <option value="{{ $id }}">{{ $name }}</option>
        @endforeach
      </select>
      @error('supplier_id') <div class="text-danger small">{{ $message }}</div> @enderror

      @if($supplier_id)
        <div class="mt-2 small text-success">
          Fournisseur sélectionné : <strong>{{ $suppliers[$supplier_id] ?? '' }}</strong>
        </div>
      @endif
    </div>

    <div class="col-md-6">
      <label class="form-label">Notes</label>
      <input class="form-control" wire:model.live="notes" placeholder="Notes">
    </div>

    <div class="row g-2 mb-3">
      <div class="col-12 mb-2">
        <livewire:supplier-search />
      </div>
      <div class="col-12">
        <livewire:quick-create-supplier />
      </div>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Ajouter un produit</label>
    <livewire:product-search placeholder="Rechercher un produit (nom, référence)" />
  </div>

  <table class="table align-middle">
    <thead>
      <tr>
        <th>Produit</th>
        <th style="width:140px">Qté</th>
        <th style="width:160px">PU (achat)</th>
        <th style="width:160px">Sous-total</th>
        <th style="width:60px"></th>
      </tr>
    </thead>
    <tbody>
    @forelse($items as $i => $row)
      @php $subtotal = ((int)($row['qty'] ?? 0)) * ((float)($row['unit_price'] ?? 0)); @endphp
      <tr>
        <td>
          @php $p = \App\Models\Product::find($row['product_id']); @endphp
          <div>{{ $row['name'] ?? ($p->name ?? 'Produit') }}</div>
          <input type="hidden" wire:model.live="items.{{ $i }}.product_id">
        </td>
        <td>
          <input type="number" class="form-control" min="1" wire:model.live="items.{{ $i }}.qty">
          @error('items.'.$i.'.qty') <div class="text-danger small">{{ $message }}</div> @enderror
        </td>
        <td>
          <input type="number" step="0.01" class="form-control" wire:model.live="items.{{ $i }}.unit_price">
          @error('items.'.$i.'.unit_price') <div class="text-danger small">{{ $message }}</div> @enderror
        </td>
        <td>
          <input class="form-control" value="{{ number_format($subtotal,2,',',' ') }}" readonly>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-danger" onclick="if(!confirm('Supprimer cette ligne ?')) event.stopImmediatePropagation();" wire:click="removeItem({{ $i }})">x</button>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="5" class="text-center text-muted">
          Aucun article pour le moment — recherchez un produit ci-dessus.
        </td>
      </tr>
    @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" class="text-end"><strong>Total</strong></td>
        <td><input class="form-control" value="{{ number_format($total,2,',',' ') }}" readonly></td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  @error('items') <div class="text-danger mb-2">{{ $message }}</div> @enderror

  <div class="d-flex gap-2">
    <button type="button" class="btn btn-primary" wire:click="save">
      <i class="bi bi-check2-circle"></i> Enregistrer
    </button>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Annuler</a>
  </div>
</div>

