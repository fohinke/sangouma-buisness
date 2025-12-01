<div class="position-relative">
  <input type="text" class="form-control" placeholder="{{ $placeholder }}" wire:model.live.debounce.300ms="query">

  @if(!empty($results))
    <div class="list-group position-absolute w-100 shadow-sm" style="z-index:2000; max-height: 260px; overflow:auto;">
      @foreach($results as $c)
        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" wire:click="pick({{ $c['id'] }})">
          <span>{{ $c['name'] }}</span>
          <small class="text-muted">{{ $c['email'] ?: '—' }} · {{ $c['phone'] ?: '—' }}</small>
        </button>
      @endforeach
    </div>
  @endif

  @if($showCreate && trim($query) !== '')
    <div class="mt-2 border rounded p-3 bg-light">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-semibold">Nouveau client</div>
        <span class="badge bg-secondary">Livewire</span>
      </div>
      <form wire:submit.prevent="createClient" class="row g-2">
        <div class="col-12">
          <input type="text" class="form-control @error('new_name') is-invalid @enderror" placeholder="Nom complet" wire:model.live="new_name" required>
          @error('new_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <input type="email" class="form-control @error('new_email') is-invalid @enderror" placeholder="Email (optionnel)" wire:model.live="new_email">
          @error('new_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <input type="text" class="form-control @error('new_phone') is-invalid @enderror" placeholder="Telephone (optionnel)" wire:model.live="new_phone">
          @error('new_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12 d-flex justify-content-between align-items-center">
          <small class="text-muted">Aucun resultat pour "{{ $query }}". Creer et selectionner ce client.</small>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Creer
          </button>
        </div>
      </form>
    </div>
  @endif
</div>
