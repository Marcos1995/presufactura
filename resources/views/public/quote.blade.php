<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.favicon')
    <title>Presupuesto {{ $quote->number }}</title>
    @include('layouts.partials.theme-boot')
    @include('layouts.partials.fonts')
    @include('layouts.partials.app-css')
</head>
<body class="guest-body">
    <div class="guest-container public-quote-page">
        <header class="guest-header">
            <span class="public-issuer">{{ $quote->issuerName() }}</span>
            @include('layouts.partials.theme-switch')
        </header>
        <section class="public-hero">
            <p class="public-hero__label">Total del presupuesto</p>
            <p class="public-hero__sum">{{ number_format($quote->total, 2, ',', '.') }} €</p>
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
                        <h1>Presupuesto {{ $quote->number }}</h1>
                        <p class="proforma-tag">Documento proforma — sin validez fiscal</p>
                    </div>
                    <span class="badge badge-{{ $quote->status }}">{{ $quote->statusLabel() }}</span>
                </div>

                <div class="invoice-meta">
                    <div>
                        <strong>Para:</strong> {{ $quote->client->name }}<br>
                        <strong>Emisión:</strong> {{ $quote->issue_date->format('d/m/Y') }}
                    </div>
                    <div>
                        <strong>Válido hasta:</strong> {{ $quote->valid_until?->format('d/m/Y') ?? '—' }}
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
                        @foreach ($quote->lineItems as $item)
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
                    <span>Total presupuesto</span>
                    <strong>{{ number_format($quote->total, 2, ',', '.') }} €</strong>
                </div>

                @if ($quote->canAccept())
                <form method="POST" action="{{ route('quotes.public.accept', $quote->public_token) }}" class="accept-form">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Aceptar presupuesto</button>
                </form>
                <form method="POST" action="{{ route('quotes.public.reject', $quote->public_token) }}" class="accept-form" style="margin-top:8px">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-block">Rechazar</button>
                </form>
                @elseif ($quote->status === 'accepted')
                    <p class="text-muted text-center">Presupuesto aceptado. Gracias.</p>
                @elseif ($quote->status === 'rejected')
                    <p class="text-muted text-center">Presupuesto rechazado.</p>
                @elseif ($quote->status === 'expired')
                    <p class="text-muted text-center">Este presupuesto ha caducado.</p>
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
