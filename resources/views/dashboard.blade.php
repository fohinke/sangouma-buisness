@extends('layouts.master')
@section('title','Dashboard')
@section('content')
<div class="container">
  <h1>Dashboard</h1>
  <div class="row g-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="h6">Ventes (mois)</div><div class="h4">{{ number_format($kpis['sales_month'] ?? 0,2,',',' ') }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="h6">Achats (mois)</div><div class="h4">{{ number_format($kpis['purchases_month'] ?? 0,2,',',' ') }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="h6">Paiements reçus</div><div class="h4">{{ number_format($kpis['payments_received'] ?? 0,2,',',' ') }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="h6">Produits bas stock</div><div class="h4">{{ $kpis['low_stock_count'] ?? 0 }}</div></div></div></div>
  </div>

  <div class="row g-3 mt-3">
    <div class="col-md-6"><canvas id="salesChart"></canvas></div>
    <div class="col-md-6"><canvas id="topProductsChart"></canvas></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Séries préparées côté serveur
const salesLabels = @json($salesSeries->pluck('d'));
const salesValues = @json($salesSeries->pluck('t'));

const ctxSales = document.getElementById('salesChart');
if (ctxSales) {
  new Chart(ctxSales, {
    type: 'line',
    data: {
      labels: salesLabels,
      datasets: [{
        label: 'Ventes 30j',
        data: salesValues,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.15)',
        tension: 0.2,
        fill: true,
      }]
    },
    options: { scales: { y: { beginAtZero: true } } }
  });
}

const topLabels = @json($topProducts->pluck('n'));
const topValues = @json($topProducts->pluck('q'));
const ctxTop = document.getElementById('topProductsChart');
if (ctxTop) {
  new Chart(ctxTop, {
    type: 'bar',
    data: {
      labels: topLabels,
      datasets: [{
        label: 'Qté vendues',
        data: topValues,
        backgroundColor: '#198754'
      }]
    },
    options: { indexAxis: 'y', scales: { x: { beginAtZero: true } } }
  });
}
</script>
@endsection
