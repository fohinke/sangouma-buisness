@extends('layouts.master')
@section('title', $supplier->name)
@section('content')
<div>
    <h1>{{ $supplier->name }}</h1>
    @include('components.flash')
    <div class="mb-3">
        <div><strong>Téléphone:</strong> {{ $supplier->phone }}</div>
        <div><strong>Email:</strong> {{ $supplier->email }}</div>
        <div><strong>Adresse:</strong> {{ $supplier->address }}</div>
    </div>

    <h4>Historique des commandes</h4>
    <table class="table table-sm table-hover align-middle">
        <thead>
            <tr>
                <th>Code</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Total</th>
                <th>Payé</th>
                <th>Reste</th>
            </tr>
        </thead>
        <tbody>
        @foreach($orders as $o)
            @php $paid = (float)($o->paid_amount ?? 0); $total = (float)($o->total_ttc ?? 0); @endphp
            <tr>
                <td><a href="{{ route('purchase-orders.show', $o) }}">{{ $o->code }}</a></td>
                <td>{{ optional($o->ordered_at)->format('Y-m-d') }}</td>
                <td>@include('components.status-badge', ['value' => $o->status])</td>
                <td>{{ number_format($total, 2, ',', ' ') }}</td>
                <td>{{ number_format($paid, 2, ',', ' ') }}</td>
                <td>{{ number_format(max(0,$total-$paid), 2, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
</div>
@endsection

