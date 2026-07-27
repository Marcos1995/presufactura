<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; line-height: 1.4; }
        .header { margin-bottom: 24px; border-bottom: 2px solid #2563eb; padding-bottom: 12px; }
        .header h1 { font-size: 20px; color: #2563eb; margin-bottom: 4px; }
        .proforma-badge { display: inline-block; background: #fef3c7; color: #92400e; padding: 2px 8px; font-size: 10px; font-weight: bold; border-radius: 4px; }
        .parties { width: 100%; margin-bottom: 24px; }
        .parties td { vertical-align: top; width: 50%; padding: 0 8px 0 0; }
        .parties h3 { font-size: 10px; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
        .meta { margin-bottom: 20px; }
        .meta table { width: auto; }
        .meta td { padding: 2px 16px 2px 0; }
        .meta .label { color: #6b7280; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.lines th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #d1d5db; }
        table.lines td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        table.lines .num { text-align: right; }
        .totals { width: 240px; margin-left: auto; margin-bottom: 20px; }
        .totals td { padding: 4px 8px; }
        .totals .label { text-align: right; color: #6b7280; }
        .totals .value { text-align: right; font-weight: bold; }
        .totals .grand td { border-top: 2px solid #111; font-size: 13px; padding-top: 8px; }
        .notes { margin-bottom: 20px; padding: 8px; background: #f9fafb; border-radius: 4px; }
        .disclaimer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #d1d5db; font-size: 9px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PRESUPUESTO PROFORMA</h1>
        <span class="proforma-badge">NO VÁLIDO COMO DOCUMENTO FISCAL</span>
    </div>

    <table class="parties">
        <tr>
            <td>
                <h3>Emisor</h3>
                <strong>{{ $document->user->business_name ?: $document->user->name }}</strong><br>
                @if ($document->user->tax_id) NIF: {{ $document->user->tax_id }}<br>@endif
                @if ($document->user->address) {{ $document->user->address }}<br>@endif
                @if ($document->user->city) {{ $document->user->postal_code }} {{ $document->user->city }}<br>@endif
                {{ $document->user->email }}
                @if ($document->user->phone)<br>{{ $document->user->phone }}@endif
            </td>
            <td>
                <h3>Cliente</h3>
                <strong>{{ $document->client->name }}</strong><br>
                @if ($document->client->tax_id) NIF: {{ $document->client->tax_id }}<br>@endif
                @if ($document->client->address) {{ $document->client->address }}<br>@endif
                {{ $document->client->email }}
                @if ($document->client->phone)<br>{{ $document->client->phone }}@endif
            </td>
        </tr>
    </table>

    <div class="meta">
        <table>
            <tr>
                <td class="label">Número:</td>
                <td><strong>{{ $document->number }}</strong></td>
                <td class="label">Fecha emisión:</td>
                <td>{{ $document->issue_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Estado:</td>
                <td>{{ ucfirst($document->status) }}</td>
                @if ($document->valid_until)
                <td class="label">Válido hasta:</td>
                <td>{{ $document->valid_until->format('d/m/Y') }}</td>
                @endif
            </tr>
        </table>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="num">Cant.</th>
                <th class="num">Precio</th>
                <th class="num">IVA %</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lineItems as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="num">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                <td class="num">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($item->vat_rate, 0) }}%</td>
                <td class="num">{{ number_format($item->line_total, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="value">{{ number_format($document->subtotal, 2, ',', '.') }} €</td>
        </tr>
        <tr>
            <td class="label">IVA:</td>
            <td class="value">{{ number_format($document->vat_amount, 2, ',', '.') }} €</td>
        </tr>
        <tr class="grand">
            <td class="label">TOTAL:</td>
            <td class="value">{{ number_format($document->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    @if ($document->notes)
        <div class="notes"><strong>Notas:</strong> {{ $document->notes }}</div>
    @endif

    <div class="disclaimer">
        Documento proforma generado con PresuFactura. No válido como documento fiscal. Sin Verifactu v1.
        El emisor es responsable de cumplir la normativa fiscal aplicable.
    </div>
</body>
</html>
