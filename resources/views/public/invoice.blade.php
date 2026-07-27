<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura {{ $invoice->number }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-body">
    <div class="guest-container public-quote-page">
        <header class="guest-header">
            <span class="logo">{{ $invoice->user->business_name ?: $invoice->user->name }}</span>
        </header>

        <main class="guest-main public-quote-main">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="auth-card public-quote-card">
                <h1>Factura {{ $invoice->number }}</h1>
                <p class="proforma-tag">Documento proforma — sin validez fiscal</p>

                <div class="invoice-meta">
                    <div>
                        <strong>Para:</strong> {{ $invoice->client->name }}<br>
                        <strong>Estado:</strong> {{ $invoice->statusLabel() }}
                    </div>
                    <div>
                        <strong>Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                        <strong>Vencimiento:</strong> {{ $invoice->due_date?->format('d/m/Y') }}
                    </div>
                </div>

                <table class="data-table">
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
                            <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                            <td class="text-right">{{ number_format($item->line_total, 2, ',', '.') }} €</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total</strong></td>
                            <td class="text-right"><strong>{{ number_format($invoice->total, 2, ',', '.') }} €</strong></td>
                        </tr>
                    </tfoot>
                </table>

                @if ($invoice->user->iban)
                <div class="iban-box">
                    <strong>IBAN para transferencia:</strong><br>
                    {{ $invoice->user->iban }}
                </div>
                @endif

                @if ($invoice->canClaimPaid())
                <form method="POST" action="{{ route('invoices.public.claim-paid', $invoice->public_token) }}" class="accept-form" onsubmit="return confirm('¿Confirmas que has realizado el pago?')">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block">He pagado</button>
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
</body>
</html>
