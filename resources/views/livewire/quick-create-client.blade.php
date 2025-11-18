<div class="mt-2">
  <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggle">
    <i class="bi bi-person-plus"></i> Nouveau client
  </button>

  @if($open)
    <div class="card mt-2">
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input class="form-control" wire:model="name" placeholder="Nom" required />
            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Téléphone</label>
            <input class="form-control" wire:model="phone" placeholder="Téléphone" required />
            @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Email (facultatif)</label>
            <input class="form-control" wire:model="email" placeholder="Email" />
            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Adresse</label>
            <input class="form-control" wire:model="address" placeholder="Adresse" />
          </div>
          <div class="col-12">
            <label class="form-label">Notes</label>
            <input class="form-control" wire:model="notes" placeholder="Notes" />
          </div>
          <div class="col-12 d-flex gap-2 mt-2">
            <button type="button" class="btn btn-primary" wire:click="save"><i class="bi bi-check2"></i> Créer</button>
            <button type="button" class="btn btn-secondary" wire:click="toggle">Annuler</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>

