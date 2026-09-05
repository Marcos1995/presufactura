<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @include('pdf._styles')
</head>
<body>
@php
    $issuer = $document->company;
    $issuerName = $issuer?->legal_name ?: ($document->user->business_name ?: $document->user->name);
    $issuerTaxId = $issuer?->tax_id ?: $document->user->tax_id;
    $issuerAddress = $issuer?->address ?: $document->user->address;
    $issuerCity = $issuer?->city ?: $document->user->city;
    $issuerPostal = $issuer?->postal_code ?: $document->user->postal_code;
    $issuerEmail = $issuer?->email ?: $document->user->email;
    $issuerPhone = $issuer?->phone ?: $document->user->phone;
    $issuerIban = $issuer?->iban ?: $document->user->iban;
    $issuerFooter = $issuer?->invoice_footer;
@endphp
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
                <div class="issuer-name">{{ $issuerName }}</div>
                <div class="issuer-line">
                    @if ($issuerTaxId)NIF {{ $issuerTaxId }}<br>@endif
                    @if ($issuerAddress){{ $issuerAddress }}<br>@endif
                    @if ($issuerCity){{ $issuerPostal }} {{ $issuerCity }}<br>@endif
                    {{ $issuerEmail }}@if ($issuerPhone) · {{ $issuerPhone }}@endif
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
                <div class="box-name">{{ $issuerName }}</div>
                <div class="box-text">
                    @if ($issuerTaxId)NIF: {{ $issuerTaxId }}<br>@endif
                    @if ($issuerAddress){{ $issuerAddress }}<br>@endif
                    @if ($issuerCity){{ $issuerPostal }} {{ $issuerCity }}<br>@endif
                    {{ $issuerEmail }}
                    @if ($issuerPhone)<br>{{ $issuerPhone }}@endif
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
                    @if ((float) $document->recargo_amount > 0)
                    <tr>
                        <td class="lbl">Recargo</td>
                        <td class="val">{{ number_format($document->recargo_amount, 2, ',', '.') }} €</td>
                    </tr>
                    @endif
                    @if ((float) $document->irpf_amount > 0)
                    <tr>
                        <td class="lbl">IRPF</td>
                        <td class="val">− {{ number_format($document->irpf_amount, 2, ',', '.') }} €</td>
                    </tr>
                    @endif
                    <tr class="grand">
                        <td class="lbl">Total</td>
                        <td class="val">{{ number_format($document->total, 2, ',', '.') }} €</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if (!empty($showIban) && $issuerIban)
        <table class="spacer-row" width="100%"><tr><td>&nbsp;</td></tr></table>
        <div class="iban-inner">
            <strong>Datos para transferencia bancaria</strong>
            <span class="iban-code">{{ $issuerIban }}</span>
        </div>
    @endif
    @if ($issuerFooter)
        <table class="spacer-row" width="100%"><tr><td>&nbsp;</td></tr></table>
        <div class="footer">{{ $issuerFooter }}</div>
    @endif

    <table class="spacer-row-lg" width="100%"><tr><td>&nbsp;</td></tr></table>

    <div class="footer">
        @if ($isFiscal ?? false)
            Factura generada con PresuFactura. El QR permite cotejar el registro en la sede de la AEAT cuando el envío haya sido aceptado.<br>
            No sustituye las declaraciones tributarias. El emisor es responsable del cumplimiento fiscal.
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
