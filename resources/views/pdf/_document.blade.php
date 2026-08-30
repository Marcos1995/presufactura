<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @include('pdf._styles')
</head>
<body>
<table class="page-frame" cellpadding="0" cellspacing="0">
<tr class="spacer-top"><td colspan="3">&nbsp;</td></tr>
<tr>
    <td class="page-gutter">&nbsp;</td>
    <td class="page-content">

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="header-left">
                @if (!empty($logoDataUri))
                    <div class="logo"><img src="{{ $logoDataUri }}" alt=""></div>
                @endif
                <div class="issuer-name">{{ $document->user->business_name ?: $document->user->name }}</div>
                <div class="issuer-line">
                    @if ($document->user->tax_id)NIF {{ $document->user->tax_id }}<br>@endif
                    @if ($document->user->address){{ $document->user->address }}<br>@endif
                    @if ($document->user->city){{ $document->user->postal_code }} {{ $document->user->city }}<br>@endif
                    {{ $document->user->email }}@if ($document->user->phone) · {{ $document->user->phone }}@endif
                </div>
            </td>
            <td class="header-right">
                <div class="doc-kicker">{{ ($isFiscal ?? false) ? 'Factura' : 'Documento proforma' }}</div>
                <div class="doc-title">{{ $docTitle }}</div>
                <div class="doc-number">{{ $document->number }}</div>
                <span class="badge {{ ($isFiscal ?? false) ? 'badge-fiscal' : 'badge-proforma' }}">{{ $proformaBadge }}</span>
            </td>
        </tr>
    </table>

    @if (!empty($qrDataUri))
    <table class="spacer-row" width="100%"><tr><td>&nbsp;</td></tr></table>
    <table class="qr-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="qr-cell">
                <img src="{{ $qrDataUri }}" alt="QR Veri*Factu" class="qr-image" width="121" height="121">
            </td>
            <td class="qr-legend">
                <strong>VERI*FACTU</strong><br>
                Factura verificable en la sede electrónica de la AEAT
            </td>
        </tr>
    </table>
    @endif

    <table class="spacer-row-lg" width="100%"><tr><td>&nbsp;</td></tr></table>

    <table class="parties-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="box-label">Emisor</div>
                <div class="box-name">{{ $document->user->business_name ?: $document->user->name }}</div>
                <div class="box-text">
                    @if ($document->user->tax_id)NIF: {{ $document->user->tax_id }}<br>@endif
                    @if ($document->user->address){{ $document->user->address }}<br>@endif
                    @if ($document->user->city){{ $document->user->postal_code }} {{ $document->user->city }}<br>@endif
                    {{ $document->user->email }}
                    @if ($document->user->phone)<br>{{ $document->user->phone }}@endif
                </div>
            </td>
            <td>
                <div class="box-label">Cliente</div>
                <div class="box-name">{{ $document->client->name }}</div>
                <div class="box-text">
                    @if ($document->client->tax_id)NIF: {{ $document->client->tax_id }}<br>@endif
                    @if ($document->client->address){{ $document->client->address }}<br>@endif
                    {{ $document->client->email }}
                    @if ($document->client->phone)<br>{{ $document->client->phone }}@endif
                </div>
            </td>
        </tr>
    </table>

    <table class="spacer-row" width="100%"><tr><td>&nbsp;</td></tr></table>

    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td width="24%">
                <span class="meta-label">Fecha emisión</span>
                <span class="meta-value">{{ $document->issue_date->format('d/m/Y') }}</span>
            </td>
            <td width="24%">
                <span class="meta-label">Estado</span>
                <span class="meta-value">{{ $document->statusLabel() }}</span>
            </td>
            @if (!empty($metaExtra))
            <td width="24%">
                <span class="meta-label">{{ $metaExtra['label'] }}</span>
                <span class="meta-value">{{ $metaExtra['value'] }}</span>
            </td>
            <td width="28%" style="text-align:right;">
                <span class="meta-label">Importe total</span>
                <span class="meta-total">{{ number_format($document->total, 2, ',', '.') }} €</span>
            </td>
            @else
            <td width="52%" colspan="2" style="text-align:right;">
                <span class="meta-label">Importe total</span>
                <span class="meta-total">{{ number_format($document->total, 2, ',', '.') }} €</span>
            </td>
            @endif
        </tr>
    </table>

    <table class="spacer-row" width="100%"><tr><td>&nbsp;</td></tr></table>

    <table class="lines-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width:44%;">Descripción</th>
                <th class="r" style="width:11%;">Cant.</th>
                <th class="r" style="width:15%;">Precio</th>
                <th class="r" style="width:10%;">IVA</th>
                <th class="r" style="width:20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lineItems as $index => $item)
            <tr class="{{ $index % 2 === 1 ? 'alt' : '' }}">
                <td>{{ $item->description }}</td>
                <td class="r">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                <td class="r">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                <td class="r">{{ number_format($item->vat_rate, 0) }}%</td>
                <td class="r">{{ number_format($item->line_total, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="spacer-row" width="100%"><tr><td>&nbsp;</td></tr></table>

    <table class="bottom-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="notes-area">
                @if ($document->notes)
                    <div class="notes-inner">
                        <strong>Notas</strong>
                        {{ $document->notes }}
                    </div>
                @endif
            </td>
            <td class="totals-area">
                <table class="totals-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="lbl">Subtotal</td>
                        <td class="val">{{ number_format($document->subtotal, 2, ',', '.') }} €</td>
                    </tr>
                    <tr>
                        <td class="lbl">IVA</td>
                        <td class="val">{{ number_format($document->vat_amount, 2, ',', '.') }} €</td>
                    </tr>
                    <tr class="grand">
                        <td class="lbl">Total</td>
                        <td class="val">{{ number_format($document->total, 2, ',', '.') }} €</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if (!empty($showIban) && $document->user->iban)
        <table class="spacer-row" width="100%"><tr><td>&nbsp;</td></tr></table>
        <div class="iban-inner">
            <strong>Datos para transferencia bancaria</strong>
            <span class="iban-code">{{ $document->user->iban }}</span>
        </div>
    @endif

    <table class="spacer-row-lg" width="100%"><tr><td>&nbsp;</td></tr></table>

    <div class="footer">
        @if ($isFiscal ?? false)
            Factura generada con PresuFactura (Veri*Factu). Registro SIF conforme RRSIF.<br>
            Verificable mediante el código QR en la sede de la Agencia Tributaria.
        @else
            Documento proforma generado con PresuFactura. No válido como factura fiscal.<br>
            El emisor es responsable de cumplir la normativa fiscal aplicable.
        @endif
        <div class="footer-site">presufactura.es</div>
    </div>

    </td>
    <td class="page-gutter">&nbsp;</td>
</tr>
<tr class="spacer-bottom"><td colspan="3">&nbsp;</td></tr>
</table>
</body>
</html>
