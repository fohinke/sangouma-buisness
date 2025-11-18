@extends('layouts.master')
@section('title','Commande ' . $order->code)
@section('content')
<div class="container-fluid">
  @include('components.flash')

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0"><i class="bi bi-bag-check"></i> Commande {{ $order->code }}</h1>
    <div class="btn-group">
      <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.invoice',$order) }}"><i class="bi bi-filetype-pdf"></i> Facture</a>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-8">
      <div class="card card-kpi">
        <div class="card-body d-flex flex-wrap gap-4">
          <div>
            <div class="text-muted small">Fournisseur</div>
            <div class="fw-semibold">{{ $order->supplier->name }}</div>
          </div>
          <div>
            <div class="text-muted small">Date</div>
            <div class="fw-semibold">{{ optional($order->ordered_at)->format('Y-m-d') }}</div>
          </div>
          <div>
            <div class="text-muted small">Statut</div>
            @php
              $statusMap = [
                'en_attente' => 'secondary',
                'recu' => 'success',
                'annulee' => 'danger',
              ];
              $labelMap = [
                'en_attente' => 'En attente',
                'recu' => 'Reçue',
                'annulee' => 'Annulée',
              ];
              $badge = $statusMap[$order->status] ?? 'primary';
              $label = $labelMap[$order->status] ?? ucfirst($order->status);
            @endphp
            <span class="badge text-bg-{{ $badge }}">{{ $label }}</span>
          </div>
          <div>
            @php
              $totalQty = (int) $order->items->sum('qty');
              $recvQty = (int) $order->items->sum('received_qty');
              $rRatio = $totalQty > 0 ? min(100, round($recvQty / $totalQty * 100)) : 0;
              [$rBadge, $rLabel] = match(true) {
                $totalQty == 0 => ['secondary','Aucun article'],
                $recvQty == 0 => ['warning','Non réceptionnée'],
                $recvQty < $totalQty => ['info','Partiellement réceptionnée'],
                default => ['success','Réceptionnée'],
              };
            @endphp
            <div class="text-muted small">Réception</div>
            <span class="badge text-bg-{{ $rBadge }}">{{ $rLabel }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      @php
        $paid = (float) $paid;
        $total = (float) ($order->total_ttc ?? 0);
        $balance = max(0, $total - $paid);
        $ratio = $total > 0 ? min(100, round($paid / $total * 100)) : 0;
      @endphp
      <div class="card card-kpi">
        <div class="card-body">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted small">Total TTC</span>
            <span class="fw-semibold">{{ number_format($total,2,',',' ') }} GNF</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted small">Payé</span>
            <span class="fw-semibold text-success">{{ number_format($paid,2,',',' ') }} GNF</span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted small">Reste</span>
            <span class="fw-semibold text-danger">{{ number_format($balance,2,',',' ') }} GNF</span>
          </div>
          <div class="progress mt-2" role="progressbar" aria-label="Progression paiement" aria-valuenow="{{ $ratio }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar bg-success" style="width: {{ $ratio }}%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-box-seam"></i> Articles</strong></div>
        <div class="card-body p-0">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Produit</th>
                <th class="text-end">Qté</th>
                <th class="text-end">Reçu</th>
                <th class="text-end">PU (GNF)</th>
                <th class="text-end">Sous-total (GNF)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($order->items as $it)
                <tr>
                  <td>{{ $it->product->name }}</td>
                  <td class="text-end">{{ $it->qty }}</td>
                  <td class="text-end">{{ $it->received_qty }}</td>
                  <td class="text-end">{{ number_format($it->unit_price,2,',',' ') }} GNF</td>
                  <td class="text-end">{{ number_format($it->subtotal,2,',',' ') }} GNF</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td colspan="4" class="text-end fw-semibold">Total</td>
                <td class="text-end fw-semibold">{{ number_format($order->total_ttc,2,',',' ') }} GNF</td>
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
          @php
            $total2 = (float) ($order->total_ttc ?? 0);
            $balance2 = max(0, $total2 - (float) $paid);
          @endphp
          <div class="mb-2 small text-muted">
            Total payé :
            <span class="text-success fw-semibold">{{ number_format((float)$paid,2,',',' ') }} GNF</span>
            &nbsp;•&nbsp;
            Reste :
            <span class="text-danger fw-semibold">{{ number_format($balance2,2,',',' ') }} GNF</span>
          </div>
          @can('process payments')
          @if($balance2 > 0)
            <form id="po-payment-form" method="post" action="{{ route('purchase-orders.payments.store',$order) }}" class="row g-2">
              @csrf
              <div class="col-6">
                <input id="po-amount" type="number" step="0.01" name="amount" class="form-control" placeholder="Montant" required max="{{ $balance2 }}">
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
                <small id="poAmountError" class="text-danger d-none">
                  Montant supérieur au reste à payer ({{ number_format($balance2,2,',',' ') }} GNF).
                </small>
              </div>
              <div class="col-12 d-flex gap-2">
                <button id="po-payment-submit" class="btn btn-primary">
                  <i class="bi bi-plus-circle"></i> Ajouter
                </button>
              </div>
            </form>
          @endif
          @endcan
          <hr class="my-3">
          <div class="small text-muted mb-2">Historique des paiements</div>
          @php $payments = $order->payments->sortByDesc('paid_at'); @endphp
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
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($payments as $p)
                    <tr>
                      <td>{{ optional($p->paid_at)->format('Y-m-d') }}</td>
                      <td>{{ $p->method ?? '-' }}</td>
                      <td class="text-end">{{ number_format((float)$p->amount,2,',',' ') }} GNF</td>
                      <td>{{ $p->notes }}</td>
                      <td class="text-end">
                        <form method="post" action="{{ route('purchase-orders.payments.destroy',[$order,$p]) }}" class="d-inline" onsubmit="return confirm('Supprimer ce paiement ?')">
                          @csrf
                          @method('delete')
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
      <div class="card">
        <div class="card-header bg-white"><strong><i class="bi bi-inboxes"></i> Réception</strong></div>
        <div class="card-body">
          @php $remainingTotal = (int) $order->items->sum(fn($i) => max(0, ($i->qty - $i->received_qty))); @endphp
          @if($remainingTotal > 0)
            <form method="post" action="{{ route('purchase-orders.receptions.store',$order) }}">
              @csrf
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Produit</th>
                    <th class="text-end">À recevoir</th>
                    <th style="width:120px">Qté</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($order->items as $it)
                    @php $remaining = $it->qty - $it->received_qty; @endphp
                    <tr>
                      <td>{{ $it->product->name }}</td>
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
                  <label class="form-label">Livreur</label>
                  <input name="delivered_by" class="form-control" placeholder="Livreur">
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
              <i class="bi bi-check2-circle"></i> Réception terminée.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('body')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('po-payment-form');
  if (!form) return;
  const amount = form.querySelector('input[name="amount"]');
  const submit = document.getElementById('po-payment-submit');
  const max = parseFloat(amount.getAttribute('max') || '0');
  const err = document.getElementById('poAmountError');
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

