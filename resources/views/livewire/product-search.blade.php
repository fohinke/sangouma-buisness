<div class="position-relative">
  <input type="text" class="form-control" placeholder="{{ $placeholder }}" wire:model.live.debounce.300ms="query">
  @if(!empty($results))
    <div class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; max-height: 260px; overflow:auto;">
      @foreach($results as $p)
        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" wire:click="pick({{ $p['id'] }})">
          <span>{{ $p['name'] }} <small class="text-muted">[{{ $p['sku'] }}]</small></span>
          <span class="text-muted small">Stock: {{ $p['stock'] }} • PA: {{ number_format($p['purchase_price'],2,',',' ') }} • PV: {{ number_format($p['sale_price'],2,',',' ') }}</span>
        </button>
      @endforeach
    </div>
  @endif
</div>

