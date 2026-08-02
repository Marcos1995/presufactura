@extends('layouts.panel')

@section('title', 'Presupuesto ' . $quote->number . ' — ' . config('app.name'))
@section('heading', 'Presupuesto ' . $quote->number)

@section('content')
<div class="page-toolbar">
    @if ($quote->canSend())
    <form method="POST" action="{{ route('quotes.send', $quote) }}" class="inline-form" onsubmit="return confirm('¿Enviar presupuesto por email al cliente?')">
        @csrf
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
    @endif
    @if ($quote->canConvert())
    <form method="POST" action="{{ route('quotes.convert', $quote) }}" class="inline-form" onsubmit="return confirm('¿Convertir a factura?')">
        @csrf
        <button type="submit" class="btn btn-success">Convertir a factura</button>
    </form>
    @endif
    <a href="{{ route('quotes.index') }}" class="btn btn-secondary">Volver</a>
    @if ($quote->status === 'draft')
    <form method="POST" action="{{ route('quotes.destroy', $quote) }}" class="inline-form" onsubmit="return confirm('¿Eliminar?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Eliminar</button>
    </form>
    @endif
</div>

    @if (in_array($quote->status, ['sent', 'accepted', 'expired']))
<div class="card card-narrow public-link-box">
    <strong>Enlace público</strong>
    <div class="public-link-row">
        <input type="text" readonly id="quote-public-link" value="{{ $quote->publicUrl() }}" class="public-link-input" onclick="this.select()">
        <button type="button" class="btn btn-secondary btn-sm" data-copy="#quote-public-link">Copiar</button>
        <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-secondary btn-sm">PDF</a>
    </div>
</div>
@endif

@if ($quote->status === 'draft')
    @include('quotes._form', [
        'action' => route('quotes.update', $quote),
        'method' => 'PUT',
        'quote' => $quote,
        'clients' => $clients,
        'defaultVatRate' => auth()->user()->default_vat_rate,
        'lineItems' => $quote->lineItems,
    ])
@else
    <div class="card">
        <div class="invoice-meta">
            <div>
                <strong>Cliente:</strong> {{ $quote->client->name }}<br>
                <strong>Estado:</strong> <span class="badge badge-{{ $quote->status }}">{{ $quote->statusLabel() }}</span>
                @if ($quote->accepted_at)
                    <br><strong>Aceptado:</strong> {{ $quote->accepted_at->format('d/m/Y H:i') }}
                @endif
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
                    <th class="text-right">IVA %</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote->lineItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                    <td class="text-right">{{ number_format($item->vat_rate, 0) }}%</td>
                    <td class="text-right">{{ number_format($item->line_total, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ number_format($quote->total, 2, ',', '.') }} €</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif
@endsection

@if ($quote->status === 'draft')
@push('scripts')
<script src="{{ asset('js/invoice-lines.js') }}"></script>
@endpush
@endif
