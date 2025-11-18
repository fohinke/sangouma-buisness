<div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0"><i class="bi bi-people-gear"></i> Utilisateurs</h1>
    <button type="button" class="btn btn-primary" wire:click="openCreate">
      <i class="bi bi-plus-circle"></i> Nouvel utilisateur
    </button>
  </div>

  <div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text"
                   class="form-control"
                   placeholder="Rechercher (nom, email)"
                   wire:model.live.debounce.300ms="search">
          </div>
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
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th role="button" wire:click="sortBy('name')">
              Nom
              @if($sortField === 'name')
                <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th role="button" wire:click="sortBy('email')">
              Email
              @if($sortField === 'email')
                <i class="bi bi-caret-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-fill text-muted"></i>
              @endif
            </th>
            <th>Rôles</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            @php $roles = $user->getRoleNames(); @endphp
            <tr>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>
                @if($roles->isEmpty())
                  <span class="text-muted small">Aucun rôle</span>
                @else
                  @foreach($roles as $role)
                    <span class="badge bg-secondary">{{ $role }}</span>
                  @endforeach
                @endif
              </td>
              <td class="text-end">
                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        wire:click="openEdit({{ $user->id }})">
                  <i class="bi bi-pencil"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">
                Aucun utilisateur trouvé.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <div class="text-muted small">
        {{ $users->total() }} utilisateur{{ $users->total() > 1 ? 's' : '' }}
      </div>
      <div>
        {{ $users->links() }}
      </div>
    </div>
  </div>

  <!-- Modal Create/Edit User -->
  <div class="modal fade @if($showModal) show d-block @endif"
       wire:key="modal-{{ $modalKey }}"
       tabindex="-1"
       @if($showModal) style="display:block" @endif
       aria-hidden="{{ $showModal ? 'false' : 'true' }}">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ $editingId ? 'Modifier l’utilisateur' : 'Nouvel utilisateur' }}
          </h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="closeModal"></button>
        </div>
        <form wire:submit.prevent="saveUser">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Nom</label>
              <input type="text"
                     class="form-control @error('name') is-invalid @enderror"
                     wire:model.blur="name">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email"
                     class="form-control @error('email') is-invalid @enderror"
                     wire:model.blur="email">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">
                Mot de passe
                @if($editingId)
                  <span class="text-muted small">(laisser vide pour ne pas changer)</span>
                @endif
              </label>
              <input type="password"
                     class="form-control @error('password') is-invalid @enderror"
                     wire:model.blur="password">
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Confirmation du mot de passe</label>
              <input type="password"
                     class="form-control"
                     wire:model.blur="password_confirmation">
            </div>
            @if(!empty($availableRoles))
              <div class="mb-2">
                <label class="form-label">Rôles</label>
                @foreach($availableRoles as $roleName)
                  <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           value="{{ $roleName }}"
                           id="role-{{ $roleName }}"
                           wire:model="selectedRoles">
                    <label class="form-check-label" for="role-{{ $roleName }}">{{ $roleName }}</label>
                  </div>
                @endforeach
              </div>
            @endif
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
