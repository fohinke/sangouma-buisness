<div class="position-relative">
  <input type="text" class="form-control" placeholder="{{ $placeholder }}" wire:model.live.debounce.300ms="query">
  @if(!empty($results))
    <div class="list-group position-absolute w-100 shadow-sm" style="z-index:1000; max-height: 260px; overflow:auto;">
      @foreach($results as $c)
        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" wire:click="pick({{ $c['id'] }})">
          <span>{{ $c['name'] }}</span>
          <small class="text-muted">{{ $c['email'] }} • {{ $c['phone'] }}</small>
        </button>
      @endforeach
    </div>
  @endif
</div>

