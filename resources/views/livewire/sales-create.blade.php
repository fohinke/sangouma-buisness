<div>
  <h1 class="mb-3"><i class="bi bi-plus-circle"></i> Nouvelle vente </h1>
  @include('components.flash')

  <div class="mb-3">
    <label class="form-label">Client</label>
    <select wire:model="client_id" class="form-select mb-2">
      <option value="">-- Choisir --</option>
      @foreach($clients as $id=>$name)
        <option value="{{ $id }}">{{ $name }}</option>
      @endforeach
    </select>
    @error('client_id') <div class="text-danger small">{{ $message }}</div> @enderror
    <div class="row g-2">
      <div class="col-md-6"><livewire:client-search /></div>
      <div class="col-md-6"><livewire:quick-create-client /></div>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Notes</label>
    <input wire:model.live="notes" class="form-control" placeholder="Notes">
  </div>

  <div class="mb-3">
    <label class="form-label">Ajouter un produit</label>
    <livewire:product-search placeholder="Rechercher un produit (nom, référence)" />
  </div>

  <table class="table align-middle">
    <thead><tr><th>Produit</th><th style="width:140px">Qté</th><th style="width:160px">PU</th><th style="width:160px">Sous-total</th><th style="width:60px"></th></tr></thead>
    <tbody>
      @foreach($items as $i => $row)
        @php $prod = \App\Models\Product::find($row['product_id']); $stock = (int)($prod->stock ?? 0); @endphp
        <tr>
          <td>
            <div>{{ $row['name'] ?? ($prod->name ?? 'Produit') }}</div>
            <input type="hidden" wire:model.live="items.{{ $i }}.product_id">
            @error('items.'.$i.'.product_id') <div class="text-danger small">{{ $message }}</div> @enderror
          </td>
          <td>
            <input type="number" class="form-control" min="1" max="{{ $stock }}" wire:model.live="items.{{ $i }}.qty">
            <div class="form-text">Max: {{ $stock }}</div>
            @error('items.'.$i.'.qty') <div class="text-danger small">{{ $message }}</div> @enderror
          </td>
          <td>
            <input type="number" step="0.01" class="form-control" wire:model.live="items.{{ $i }}.unit_price">
            @error('items.'.$i.'.unit_price') <div class="text-danger small">{{ $message }}</div> @enderror
          </td>
          <td>
            @php $st = ((int)($row['qty'] ?? 0))*((float)($row['unit_price'] ?? 0)); @endphp
            <input class="form-control" value="{{ number_format($st,2,',',' ') }}" readonly>
          </td>
          <td>
            <button class="btn btn-sm btn-danger" type="button" onclick="if(!confirm('Supprimer cette ligne ?')) event.stopImmediatePropagation();" wire:click="removeItem({{ $i }})">x</button>
          </td>
        </tr>
      @endforeach
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
    <button type="button" class="btn btn-primary" wire:click="save"><i class="bi bi-check2-circle"></i> Enregistrer</button>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Annuler</a>
  </div>
</div>
