<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.favicon')
    <title>Factura {{ $invoice->number }}</title>
    @include('layouts.partials.theme-boot')
    @include('layouts.partials.fonts')
    @include('layouts.partials.app-css')
</head>
<body class="guest-body">
    <div class="guest-container public-quote-page">
        <header class="guest-header">
            <span class="public-issuer">{{ $invoice->issuerName() }}</span>
            @include('layouts.partials.theme-switch')
        </header>
        <section class="public-hero">
            <p class="public-hero__label">Total a pagar</p>
            <p class="public-hero__sum">{{ number_format($invoice->total, 2, ',', '.') }} €</p>
        </section>

        <main class="guest-main public-quote-main">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="auth-card public-quote-card">
                <div class="public-doc-header">
                    <div>
                        <h1>Factura {{ $invoice->number }}</h1>
                        @if ($invoice->isFiscal())
                            <p class="fiscal-tag">Factura verificable — Veri*Factu</p>
                        @else
                            <p class="proforma-tag">Documento proforma — sin validez fiscal</p>
                        @endif
                    </div>
                    <span class="badge badge-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span>
                </div>

                @include('partials.verifactu-qr')

                <div class="invoice-meta">
                    <div>
                        <strong>Para:</strong> {{ $invoice->client->name }}<br>
                        <strong>Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}
                    </div>
                    <div>
                        <strong>Vencimiento:</strong> {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                    </div>
                </div>

                <table class="data-table data-table-lines">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th class="text-right">Cant.</th>
                            <th class="text-right">Precio</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->lineItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td data-label="Cant." class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                            <td data-label="Precio" class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                            <td data-label="Total" class="text-right">{{ number_format($item->line_total, 2, ',', '.') }} €</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="public-doc-total">
                    <span>Subtotal</span>
                    <strong>{{ number_format($invoice->subtotal, 2, ',', '.') }} €</strong>
                </div>
                <div class="public-doc-total">
                    <span>IVA</span>
                    <strong>{{ number_format($invoice->vat_amount, 2, ',', '.') }} €</strong>
                </div>
                @if ((float) $invoice->recargo_amount > 0)
                <div class="public-doc-total">
                    <span>Recargo</span>
                    <strong>{{ number_format($invoice->recargo_amount, 2, ',', '.') }} €</strong>
                </div>
                @endif
                @if ((float) $invoice->irpf_amount > 0)
                <div class="public-doc-total">
                    <span>IRPF</span>
                    <strong>− {{ number_format($invoice->irpf_amount, 2, ',', '.') }} €</strong>
                </div>
                @endif
                <div class="public-doc-total">
                    <span>Total factura</span>
                    <strong>{{ number_format($invoice->total, 2, ',', '.') }} €</strong>
                </div>

                @if ($invoice->issuerIban())
                <div class="iban-box">
                    <strong>IBAN para transferencia:</strong><br>
                    {{ $invoice->issuerIban() }}
                </div>
                @endif

                @if ($invoice->canClaimPaid())
                <form method="POST" action="{{ route('invoices.public.claim-paid', $invoice->public_token) }}" class="accept-form" onsubmit="return confirm('¿Confirmas que has realizado el pago?')">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block btn-lg">He pagado</button>
                </form>
                @elseif ($invoice->status === 'payment_pending')
                    <p class="text-muted text-center">Hemos recibido tu aviso de pago. El emisor lo revisará.</p>
                @elseif ($invoice->status === 'paid')
                    <p class="text-muted text-center">Factura pagada. Gracias.</p>
                @elseif ($invoice->status === 'expired')
                    <p class="text-muted text-center">Esta factura está vencida. Contacta con el emisor.</p>
                @endif
            </div>
        </main>

        <footer class="guest-footer">
            @include('layouts.partials.legal-footer')
        </footer>
    </div>
    @include('layouts.partials.theme-toggle-script')
</body>
</html>
