@extends('layouts.master')
@section('title','Vente ' . $sale->code)
@section('content')
<div>
  @include('components.flash')

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0"><i class="bi bi-receipt"></i> Vente {{ $sale->code }}</h1>
    <div class="btn-group">
      <a class="btn btn-outline-secondary" href="{{ route('sales.invoice',$sale) }}"><i class="bi bi-filetype-pdf"></i> Facture</a>
      <a class="btn btn-outline-secondary" href="{{ route('sales.delivery-note',$sale) }}"><i class="bi bi-truck"></i> Bon de livraison</a>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body d-flex flex-wrap gap-4">
          <div>
            <div class="text-muted small">Client</div>
            <div class="fw-semibold">{{ optional($sale->client)->name }}</div>
          </div>
          <div>
            <div class="text-muted small">Date</div>
            <div class="fw-semibold">{{ optional($sale->sold_at)->format('Y-m-d') }}</div>
          </div>
          <div>
            <div class="text-muted small">Statut paiement</div>
            @include('components.status-badge', ['value' => $sale->status])
          </div>
          <div>
            @php
              $totalQty = (int) $sale->items->sum('qty');
              $delivQty = (int) $sale->items->sum('delivered_qty');
              $dl = $totalQty === 0 ? 'en_attente' : ($delivQty === 0 ? 'en_attente' : ($delivQty < $totalQty ? 'en_cours' : 'livree'));
            @endphp
            <div class="text-muted small">Livraison</div>
            @include('components.status-badge', ['value' => $dl])
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      @php $total = (float) ($sale->total_ttc ?? 0); @endphp
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted small">Total TTC</span>
            <span class="fw-semibold">{{ number_format($total,2,',',' ') }} GNF</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted small">Payé</span>
            <span class="fw-semibold text-success">{{ number_format((float)$paid,2,',',' ') }} GNF</span>
          </div>
          <div class="d-flex justify-content_between">
            <span class="text-muted small">Reste</span>
            <span class="fw-semibold text-danger">{{ number_format((float)$balance,2,',',' ') }} GNF</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-bag"></i> Articles</strong></div>
        <div class="card-body p-0">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Produit</th>
                <th class="text-end">Qté</th>
                <th class="text-end">Livré</th>
                <th class="text-end">PU (GNF)</th>
                <th class="text-end">Sous-total (GNF)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($sale->items as $it)
                <tr>
                  <td>{{ optional($it->product)->name }}</td>
                  <td class="text-end">{{ $it->qty }}</td>
                  <td class="text-end">{{ $it->delivered_qty }}</td>
                  <td class="text-end">{{ number_format((float)$it->unit_price,2,',',' ') }} GNF</td>
                  <td class="text-end">{{ number_format((float)$it->subtotal,2,',',' ') }} GNF</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td colspan="4" class="text-end fw-semibold">Total</td>
                <td class="text-end fw-semibold">{{ number_format((float)$sale->total_ttc,2,',',' ') }} GNF</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-4 d-flex flex-column gap-3">
      <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-cash-coin"></i> Paiements</strong></div>
        <div class="card-body">
          @php $total2 = (float) ($sale->total_ttc ?? 0); $balance2 = max(0, $total2 - (float) $paid); @endphp
          <div class="mb-2 small text-muted">
            Total payé :
            <span class="text-success fw-semibold">{{ number_format((float)$paid,2,',',' ') }} GNF</span>
            &nbsp;•&nbsp;
            Reste :
            <span class="text-danger fw-semibold">{{ number_format($balance2,2,',',' ') }} GNF</span>
          </div>
          @can('process payments')
          @if($balance2 > 0)
            <form id="sale-payment-form" method="post" action="{{ route('sales.payments.store',$sale) }}" class="row g-2">
              @csrf
              <div class="col-6">
                <input id="sale-amount" type="text" inputmode="decimal" name="amount" class="form-control amount-input" data-money-helper="sale-amount-helper" placeholder="Montant" required max="{{ $balance2 }}">
                <div class="form-text text-muted" id="sale-amount-helper"></div>
              </div>
              <div class="col-6">
                <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
              </div>
              <div class="col-6">
                <input name="method" class="form-control" placeholder="Méthode">
              </div>
              <div class="col-6">
                <input name="notes" class="form-control" placeholder="Notes">
              </div>
              <div class="col-12">
                <small id="saleAmountError" class="text-danger d-none">
                  Montant supérieur au reste à payer ({{ number_format($balance2,2,',',' ') }} GNF).
                </small>
              </div>
              <div class="col-12 d-flex gap-2">
                <button id="sale-payment-submit" class="btn btn-primary">
                  <i class="bi bi-plus-circle"></i> Ajouter
                </button>
              </div>
            </form>
          @endif
          @endcan
          <hr class="my-3">
          <div class="small text-muted mb-2">Historique des paiements</div>
          @php $payments = $sale->payments->sortByDesc('paid_at'); @endphp
          @if($payments->isEmpty())
            <div class="text-muted small">Aucun paiement enregistré.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Méthode</th>
                    <th class="text-end">Montant (GNF)</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($payments as $p)
                    <tr>
                      <td>{{ optional($p->paid_at)->format('Y-m-d') }}</td>
                      <td>{{ $p->method ?? '-' }}</td>
                      <td class="text-end">{{ number_format((float)$p->amount,2,',',' ') }} GNF</td>
                      <td>{{ $p->notes }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>

      <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-truck"></i> Livraison</strong></div>
        <div class="card-body">
          @php $remainingTotal = (int) $sale->items->sum(fn($i) => max(0, ($i->qty - $i->delivered_qty))); @endphp
          @can('manage sales')
          @if($remainingTotal > 0)
            <form method="post" action="{{ route('sales.deliver',$sale) }}">
              @csrf
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Produit</th>
                    <th class="text-end">À livrer</th>
                    <th style="width:120px">Qté</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($sale->items as $it)
                    @php $remaining = max(0, $it->qty - (int) $it->delivered_qty); @endphp
                    <tr>
                      <td>{{ optional($it->product)->name }}</td>
                      <td class="text-end">{{ $remaining }}</td>
                      <td>
                        <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $it->id }}">
                        <input type="number" name="items[{{ $loop->index }}][qty]" class="form-control" min="0" max="{{ $remaining }}" value="0">
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label">Date</label>
                  <input type="date" name="delivered_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-6">
                  <label class="form-label">Transporteur</label>
                  <input name="carrier" class="form-control" placeholder="Transporteur">
                </div>
              </div>
              <div class="mt-2">
                <button class="btn btn-primary">
                  <i class="bi bi-check2-circle"></i> Enregistrer
                </button>
              </div>
            </form>
          @else
            <div class="alert alert-success mb-0 py-2">
              <i class="bi bi-check2-circle"></i> Livraison terminée.
            </div>
          @endif
          @endcan
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('body')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('sale-payment-form');
  if (!form) return;
  const amount = form.querySelector('input[name="amount"]');
  const submit = document.getElementById('sale-payment-submit');
  const max = parseFloat(amount.getAttribute('max') || '0');
  const err = document.getElementById('saleAmountError');
  const validate = () => {
    const val = parseFloat(amount.value || '0');
    const invalid = val > max;
    amount.classList.toggle('is-invalid', invalid);
    if (err) err.classList.toggle('d-none', !invalid);
    if (submit) {
      submit.disabled = invalid;
      submit.classList.toggle('d-none', invalid);
    }
  };
  amount.addEventListener('input', validate);
  validate();
});
</script>
@endpush
