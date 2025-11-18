<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
        .brand { font-size: 18px; font-weight: 700; }
        .doc { text-align: right; }
        .doc h1 { margin: 0; font-size: 20px; }
        .accent { height: 4px; background: linear-gradient(90deg,#0ea5a5,#2563eb); margin: 6px 0 10px; border-radius: 2px; }
        .meta { display: flex; gap: 16px; margin-bottom: 8px; }
        .box { border: 1px solid #ccc; padding: 8px; border-radius: 6px; width: 50%; }
        .label { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #f4f6f8; text-align: left; font-weight: 600; border-bottom: 1px solid #ddd; padding: 8px; }
        tbody td { border-bottom: 1px solid #eee; padding: 8px; }
        tbody tr:nth-child(even) td { background: #fafbfc; }
        .text-end { text-align: right; }
        .totals { margin-top: 10px; display: flex; justify-content: flex-end; }
        .totals table { width: 55%; border-collapse: collapse; }
        .totals td { padding: 6px 8px; }
        .totals tr td:first-child { color: #666; }
        .totals .grand td { font-weight: 700; border-top: 1px solid #ddd; }
        .footer { margin-top: 14px; color: #666; font-size: 11px; text-align: center; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .badge-success { color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; }
        .badge-warning { color: #92400e; background: #fef3c7; border: 1px solid #fde68a; }
        .watermark { position: fixed; top: 35%; left: 10%; right: 10%; text-align: center; font-size: 64px; color: rgba(4,120,87,.08); transform: rotate(-12deg); font-weight: 800; }
    </style>
    <title>Facture {{ $sale->code }}</title>
    </head>
<body>
    @php
        $co = (object) config('company');
        $logo = $co->logo_path ? public_path($co->logo_path) : null;
        $paid = (float) $sale->payments->sum('amount');
        $rest = max(0, (float) ($sale->total_ttc ?? 0) - $paid);
    @endphp
    <div class="header">
        <div class="brand">
            @if($logo && file_exists($logo))
                <img src="{{ $logo }}" alt="Logo" style="height:56px; vertical-align:middle; margin-right:12px;">
            @endif
            {{ $co->name ?: '' }}
            <div style="font-weight:400; font-size:11px; color:#555; margin-top:4px;">
                @if($co->address) {{ $co->address }} @endif
                @if($co->city) - {{ $co->city }} @endif
                @if($co->phone) - Tél: {{ $co->phone }} @endif
                @if($co->email) - {{ $co->email }} @endif
            </div>
        </div>
        <div class="doc">
            <h1>Facture</h1>
            <div>N° {{ $sale->code }}</div>
            <div style="margin-top:6px;">
                @if($rest <= 0)
                    <span class="badge badge-success">Payée</span>
                @else
                    <span class="badge badge-warning">Impayée</span>
                @endif
            </div>
        </div>
    </div>
    <div class="accent"></div>
    <div class="meta">
        <div class="box">
            <div class="label">Client</div>
            <div><strong>{{ $sale->client->name }}</strong></div>
            @if($sale->client->phone)<div>Tél: {{ $sale->client->phone }}</div>@endif
            @if($sale->client->email)<div>Email: {{ $sale->client->email }}</div>@endif
        </div>
        <div class="box">
            <div class="label">Détails</div>
            <div>Date: <strong>{{ optional($sale->sold_at)->format('Y-m-d') }}</strong></div>
            <div>Payé: <strong>{{ number_format($paid,2,',',' ') }} GNF</strong></div>
            <div>Reste: <strong>{{ number_format($rest,2,',',' ') }} GNF</strong></div>
        </div>
    </div>
    <table>
        <thead>
          <tr>
            <th>Produit</th>
            <th class="text-end">Qté</th>
            <th class="text-end">PU (GNF)</th>
            <th class="text-end">Sous-total (GNF)</th>
          </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $it)
            <tr>
                <td>{{ $it->product->name }}</td>
                <td class="text-end">{{ $it->qty }}</td>
                <td class="text-end">{{ number_format($it->unit_price,2,',',' ') }} GNF</td>
                <td class="text-end">{{ number_format($it->subtotal,2,',',' ') }} GNF</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="totals">
        <table>
            <tr>
              <td>Total HT</td>
              <td class="text-end">{{ number_format((float)($sale->total_ht ?? $sale->total_ttc),2,',',' ') }} GNF</td>
            </tr>
            <tr class="grand">
              <td>Total TTC</td>
              <td class="text-end">{{ number_format((float)$sale->total_ttc,2,',',' ') }} GNF</td>
            </tr>
            <tr>
              <td>Payé</td>
              <td class="text-end">{{ number_format($paid,2,',',' ') }} GNF</td>
            </tr>
            <tr>
              <td>Reste</td>
              <td class="text-end">{{ number_format($rest,2,',',' ') }} GNF</td>
            </tr>
        </table>
    </div>
    
    @if($rest <= 0)
        <div class="watermark">PAYÉE</div>
    @endif
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
    @endif
</body>
</html>

