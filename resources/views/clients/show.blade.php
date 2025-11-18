@extends('layouts.master')
@section('title', $client->name)
@section('content')
<div>
  <h1>{{ $client->name }}</h1>
  <div class="mb-3"><strong>Téléphone:</strong> {{ $client->phone }} | <strong>Email:</strong> {{ $client->email }}</div>
  <h4>Historique des ventes</h4>
  <table class="table table-sm table-striped">
    <thead><tr><th>Code</th><th>Date</th><th>Statut</th><th>Total</th><th>Payé</th><th>Reste</th></tr></thead>
    <tbody>
      @foreach($sales as $s)
        @php $paid=(float)($s->paid_amount??0); $total=(float)($s->total_ttc??0); @endphp
        <tr>
          <td><a href="{{ route('sales.show',$s) }}">{{ $s->code }}</a></td>
          <td>{{ optional($s->sold_at)->format('Y-m-d') }}</td>
          <td>{{ $s->status }}</td>
          <td>{{ number_format($total,2,',',' ') }}</td>
          <td>{{ number_format($paid,2,',',' ') }}</td>
          <td>{{ number_format(max(0,$total-$paid),2,',',' ') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  {{ $sales->links() }}
</div>
@endsection
