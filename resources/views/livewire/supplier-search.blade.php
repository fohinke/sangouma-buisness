<div class="position-relative">
  <input type="text" class="form-control" placeholder="{{ $placeholder }}" wire:model.live.debounce.300ms="query">
  @if(!empty($results))
    <div class="list-group position-absolute w-100 shadow-sm" style="z-index:1000; max-height: 260px; overflow:auto;">
      @foreach($results as $s)
        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" wire:click="pick({{ $s['id'] }})">
          <span>{{ $s['name'] }}</span>
          <small class="text-muted">{{ $s['email'] }} • {{ $s['phone'] }}</small>
        </button>
      @endforeach
    </div>
  @endif
</div>

