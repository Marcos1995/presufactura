@extends('layouts.panel')

@section('title', 'Factura ' . $invoice->number . ' — ' . config('app.name'))
@section('heading', 'Factura ' . $invoice->number)

@section('content')
<div class="page-toolbar">
    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-secondary">Descargar PDF</a>
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Volver</a>
    @if ($invoice->status === 'draft')
    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="inline-form" onsubmit="return confirm('¿Eliminar esta factura?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Eliminar</button>
    </form>
    @endif
</div>

@if ($invoice->status === 'draft')
    @include('invoices._form', [
        'action' => route('invoices.update', $invoice),
        'method' => 'PUT',
        'invoice' => $invoice,
        'clients' => $clients,
        'defaultVatRate' => auth()->user()->default_vat_rate,
        'lineItems' => $invoice->lineItems,
    ])
@else
    <div class="card">
        <div class="invoice-meta">
            <div>
                <strong>Cliente:</strong> {{ $invoice->client->name }}<br>
                <strong>Email:</strong> {{ $invoice->client->email }}<br>
                <strong>Estado:</strong> <span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
            <div>
                <strong>Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                <strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}
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
                @foreach ($invoice->lineItems as $item)
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
                    <td colspan="4" class="text-right"><strong>Subtotal</strong></td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right"><strong>IVA</strong></td>
                    <td class="text-right">{{ number_format($invoice->vat_amount, 2, ',', '.') }} €</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ number_format($invoice->total, 2, ',', '.') }} €</strong></td>
                </tr>
            </tfoot>
        </table>

        @if ($invoice->notes)
            <p class="invoice-notes"><strong>Notas:</strong> {{ $invoice->notes }}</p>
        @endif
    </div>
@endif
@endsection

@if ($invoice->status === 'draft')
@push('scripts')
<script src="{{ asset('js/invoice-lines.js') }}"></script>
@endpush
@endif
