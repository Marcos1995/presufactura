<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presupuesto {{ $quote->number }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-body">
    <div class="guest-container public-quote-page">
        <header class="guest-header">
            <span class="logo">{{ $quote->user->business_name ?: $quote->user->name }}</span>
        </header>

        <main class="guest-main public-quote-main">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="auth-card public-quote-card">
                <h1>Presupuesto {{ $quote->number }}</h1>
                <p class="proforma-tag">Documento proforma — sin validez fiscal</p>

                <div class="invoice-meta">
                    <div>
                        <strong>Para:</strong> {{ $quote->client->name }}<br>
                        <strong>Estado:</strong> {{ $quote->statusLabel() }}
                    </div>
                    <div>
                        <strong>Emisión:</strong> {{ $quote->issue_date->format('d/m/Y') }}<br>
                        <strong>Válido hasta:</strong> {{ $quote->valid_until?->format('d/m/Y') }}
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
                        @foreach ($quote->lineItems as $item)
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
                            <td class="text-right"><strong>{{ number_format($quote->total, 2, ',', '.') }} €</strong></td>
                        </tr>
                    </tfoot>
                </table>

                @if ($quote->canAccept())
                <form method="POST" action="{{ route('quotes.public.accept', $quote->public_token) }}" class="accept-form">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block">Aceptar presupuesto</button>
                </form>
                @elseif ($quote->status === 'accepted')
                    <p class="text-muted text-center">Presupuesto aceptado. Gracias.</p>
                @elseif ($quote->status === 'expired')
                    <p class="text-muted text-center">Este presupuesto ha caducado.</p>
                @endif
            </div>
        </main>

        <footer class="guest-footer">
            <p>Documento proforma generado con PresuFactura. Sin Verifactu v1. El emisor es responsable de cumplir la normativa fiscal aplicable.</p>
        </footer>
    </div>
</body>
</html>
