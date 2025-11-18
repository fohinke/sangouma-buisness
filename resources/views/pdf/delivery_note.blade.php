<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
        .accent { height: 4px; background: linear-gradient(90deg,#0ea5a5,#2563eb); margin: 6px 0 10px; border-radius: 2px; }
        .brand { font-size: 18px; font-weight: 700; }
        .doc { text-align: right; }
        .doc h1 { margin: 0; font-size: 20px; }
        .meta { display: flex; gap: 16px; margin-bottom: 8px; }
        .box { border: 1px solid #ccc; padding: 8px; border-radius: 6px; width: 50%; }
        .label { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #f4f6f8; text-align: left; font-weight: 600; border-bottom: 1px solid #ddd; padding: 8px; }
        tbody td { border-bottom: 1px solid #eee; padding: 8px; }
        .text-end { text-align: right; }
        .footer { margin-top: 14px; color: #666; font-size: 11px; text-align: center; }
    </style>
    <title>Bon de livraison {{ $sale->code }}</title>
    </head>
<body>
    @php
        $co = (object) config('company');
        $logo = $co->logo_path ? public_path($co->logo_path) : null;
    @endphp
    <div class="header">
        <div class="brand">
            @if($logo && file_exists($logo))
                <img src="{{ $logo }}" alt="Logo" style="height:56px; vertical-align:middle; margin-right:12px;">
            @endif
            {{ $co->name ?: '' }}
            <div style="font-weight:400; font-size:11px; color:#555; margin-top:4px;">
                @if($co->address) {{ $co->address }} @endif
                @if($co->city) — {{ $co->city }} @endif
                @if($co->phone) — Tél: {{ $co->phone }} @endif
                @if($co->email) — {{ $co->email }} @endif
            </div>
        </div>
        <div class="doc"><h1>Bon de livraison</h1><div>N° {{ $sale->code }}</div></div>
    </div>
    <div class="accent"></div>
    <div class="meta">
        <div class="box">
            <div class="label">Client</div>
            <div><strong>{{ $sale->client->name }}</strong></div>
            @if($sale->client->phone)<div>Tél: {{ $sale->client->phone }}</div>@endif
        </div>
        <div class="box">
            <div class="label">Détails</div>
            <div>Date livraison: <strong>{{ optional($sale->delivered_at)->format('Y-m-d') }}</strong></div>
            @if($sale->carrier)<div>Transporteur: <strong>{{ $sale->carrier }}</strong></div>@endif
        </div>
    </div>
    <table>
        <thead><tr><th>Produit</th><th class="text-end">Qté</th><th class="text-end">Livré</th></tr></thead>
        <tbody>
            @foreach($sale->items as $it)
              <tr><td>{{ $it->product->name }}</td><td class="text-end">{{ $it->qty }}</td><td class="text-end">{{ $it->delivered_qty }}</td></tr>
            @endforeach
        </tbody>
    </table>
    
    @php
        $contact = array_values(array_filter([
            $co->address,
            $co->city,
            $co->phone ? ('Tél: '.$co->phone) : null,
            $co->email,
        ]));
    @endphp
    @if(count($contact))
        <div class="footer">{{ implode(' • ', $contact) }}</div>
    @endif</body>
</html>
