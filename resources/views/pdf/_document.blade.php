<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @include('pdf._styles')
</head>
<body>
    <div class="accent-bar"></div>

    <table class="doc-header">
        <tr>
            <td class="brand-cell">
                @if (!empty($logoDataUri))
                    <div class="logo-wrap">
                        <img src="{{ $logoDataUri }}" alt="">
                    </div>
                @endif
                <div class="brand-name">{{ $document->user->business_name ?: $document->user->name }}</div>
                <div class="brand-meta">
                    @if ($document->user->tax_id)NIF: {{ $document->user->tax_id }} · @endif
                    {{ $document->user->email }}
                    @if ($document->user->phone) · {{ $document->user->phone }}@endif
                </div>
            </td>
            <td class="doc-title-cell">
                <div class="doc-type">{{ $docTitle }}</div>
                <div class="doc-number">{{ $document->number }}</div>
                <span class="proforma-badge">{{ $proformaBadge }}</span>
            </td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td class="party-box">
                <div class="party-label">Emisor</div>
                <div class="party-name">{{ $document->user->business_name ?: $document->user->name }}</div>
                <div class="party-details">
                    @if ($document->user->tax_id)NIF: {{ $document->user->tax_id }}<br>@endif
                    @if ($document->user->address){{ $document->user->address }}<br>@endif
                    @if ($document->user->city){{ $document->user->postal_code }} {{ $document->user->city }}<br>@endif
                    {{ $document->user->email }}
                    @if ($document->user->phone)<br>{{ $document->user->phone }}@endif
                </div>
            </td>
            <td class="party-spacer"></td>
            <td class="party-box">
                <div class="party-label">Cliente</div>
                <div class="party-name">{{ $document->client->name }}</div>
                <div class="party-details">
                    @if ($document->client->tax_id)NIF: {{ $document->client->tax_id }}<br>@endif
                    @if ($document->client->address){{ $document->client->address }}<br>@endif
                    {{ $document->client->email }}
                    @if ($document->client->phone)<br>{{ $document->client->phone }}@endif
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-box">
        <tr>
            <td width="25%">
                <span class="meta-label">Fecha emisión</span>
                <span class="meta-value">{{ $document->issue_date->format('d/m/Y') }}</span>
            </td>
            <td width="25%">
                <span class="meta-label">Estado</span>
                <span class="meta-value">{{ $document->statusLabel() }}</span>
            </td>
            @if (!empty($metaExtra))
            <td width="25%">
                <span class="meta-label">{{ $metaExtra['label'] }}</span>
                <span class="meta-value">{{ $metaExtra['value'] }}</span>
            </td>
            @endif
            <td width="25%" style="text-align:right;">
                <span class="meta-label">Total documento</span>
                <span class="meta-value" style="color:#2563eb;font-size:12px;">{{ number_format($document->total, 2, ',', '.') }} €</span>
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:42%;">Descripción</th>
                <th class="num" style="width:12%;">Cant.</th>
                <th class="num" style="width:16%;">Precio</th>
                <th class="num" style="width:10%;">IVA</th>
                <th class="num" style="width:20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lineItems as $item)
            <tr>
                <td class="desc">{{ $item->description }}</td>
                <td class="num">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                <td class="num">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($item->vat_rate, 0) }}%</td>
                <td class="num">{{ number_format($item->line_total, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="bottom-section">
        <tr>
            <td class="notes-cell">
                @if ($document->notes)
                    <div class="notes-box">
                        <strong>Notas</strong>
                        {{ $document->notes }}
                    </div>
                @endif
            </td>
            <td class="totals-cell">
                <table class="totals">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="value">{{ number_format($document->subtotal, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="label">IVA</td>
                        <td class="value">{{ number_format($document->vat_amount, 2, ',', '.') }} €</td>
                    </tr>
                    <tr class="grand">
                        <td class="label">Total</td>
                        <td class="value">{{ number_format($document->total, 2, ',', '.') }} €</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if (!empty($showIban) && $document->user->iban)
        <div class="iban-box">
            <strong>Datos para transferencia</strong>
            <span class="iban-value">{{ $document->user->iban }}</span>
        </div>
    @endif

    <div class="disclaimer">
        Documento proforma generado con PresuFactura. No válido como factura fiscal. Sin Verifactu v1.<br>
        El emisor es responsable de cumplir la normativa fiscal aplicable.
        <div class="footer-brand">presufactura.es</div>
    </div>
</body>
</html>
