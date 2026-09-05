@extends('layouts.panel')

@section('title', 'Factura ' . $invoice->number . ' — ' . config('app.name'))
@section('heading', 'Factura ' . $invoice->number)

@section('content')
<div class="page-toolbar">
    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-secondary">Descargar PDF</a>
    @if ($invoice->canSend())
    <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="inline-form" onsubmit="return confirm('¿Enviar factura por email al cliente?')">
        @csrf
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
    @endif
    @if ($invoice->canMarkPaid())
    <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}" class="inline-form" onsubmit="return confirm('¿Marcar como pagada?')">
        @csrf
        <button type="submit" class="btn btn-success">Marcar como pagada</button>
    </form>
    @endif
    @if ($invoice->canCancel())
    <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" class="inline-form" onsubmit="return confirm('¿Anular esta factura? Se generará un registro SIF de anulación.')">
        @csrf
        <button type="submit" class="btn btn-danger">Anular factura</button>
    </form>
    @endif
    @if (($verifactuAvailable ?? false) && $invoice->billingRecord?->canRetry())
    <form method="POST" action="{{ route('invoices.verifactu.retry', $invoice) }}" class="inline-form">
        @csrf
        <button type="submit" class="btn btn-secondary">Reintentar Veri*Factu</button>
    </form>
    @endif
    @if ($invoice->canCreateRectificativa())
    <form method="POST" action="{{ route('invoices.rectificativa', $invoice) }}" class="inline-form" onsubmit="return confirm('¿Crear factura rectificativa de esta factura?')">
        @csrf
        <button type="submit" class="btn btn-secondary">Emitir rectificativa</button>
    </form>
    @endif
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
        'defaultVatRate' => $invoice->company?->default_vat_rate ?? auth()->user()->default_vat_rate,
        'defaultIrpfRate' => $invoice->company?->default_irpf_rate ?? 0,
        'defaultRecargoRate' => $invoice->company?->default_recargo_rate ?? 0,
        'lineItems' => $invoice->lineItems,
    ])
@else
    @if (in_array($invoice->status, ['sent', 'expired', 'payment_pending', 'paid']))
    <div class="card card-narrow public-link-box">
        <strong>Enlace público</strong>
        <div class="public-link-row">
            <input type="text" readonly id="invoice-public-link" value="{{ $invoice->publicUrl() }}" class="public-link-input" onclick="this.select()">
            <button type="button" class="btn btn-secondary btn-sm" data-copy="#invoice-public-link">Copiar</button>
        </div>
    </div>
    @endif
    <div class="card">
        <div class="invoice-meta">
            <div>
                <strong>Cliente:</strong> {{ $invoice->client->name }}<br>
                <strong>Email:</strong> {{ $invoice->client->email }}<br>
                <strong>Estado:</strong> <span class="badge badge-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span>
                @if (($verifactuAvailable ?? false) && $invoice->billingRecord)
                    <br><strong>AEAT:</strong> <span class="badge badge-{{ $invoice->billingRecord->aeat_status }}">{{ $invoice->billingRecord->aeatStatusLabel() }}</span>
                @endif
                @if ($invoice->rectifiesDocument)
                    <br><strong>Rectifica:</strong> {{ $invoice->rectifiesDocument->number }}
                @endif
                @if ($invoice->paid_at)
                    <br><strong>Pagada:</strong> {{ $invoice->paid_at->format('d/m/Y H:i') }}
                @endif
                @if ($invoice->sent_at)
                    <br><strong>Enviada:</strong> {{ $invoice->sent_at->format('d/m/Y H:i') }}
                @endif
            </div>
            <div>
                <strong>Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                <strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}
            </div>
        </div>

        @include('partials.verifactu-qr')

        <table class="data-table data-table-lines">
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
                    <td data-label="Cant." class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td data-label="Precio" class="text-right">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                    <td data-label="IVA" class="text-right">{{ number_format($item->vat_rate, 0) }}%</td>
                    <td data-label="Total" class="text-right">{{ number_format($item->line_total, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @include('partials.document-totals', ['document' => $invoice])
            </tfoot>
        </table>

        @if ($invoice->notes)
            <p class="invoice-notes"><strong>Notas:</strong> {{ $invoice->notes }}</p>
        @endif

        @if ($invoice->canMarkPaid() || $invoice->status === 'paid')
        <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="form" style="margin-top:1.5rem;max-width:420px">
            @csrf
            <h2 class="form-section-title">Registrar cobro</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="amount">Importe *</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount', $invoice->total) }}" required>
                </div>
                <div class="form-group">
                    <label for="paid_on">Fecha *</label>
                    <input type="date" id="paid_on" name="paid_on" value="{{ old('paid_on', now()->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="method">Método</label>
                <input type="text" id="method" name="method" value="{{ old('method', 'transferencia') }}" maxlength="40">
            </div>
            <button type="submit" class="btn btn-secondary">Guardar cobro</button>
        </form>
        @endif
    </div>
@endif
@endsection

@if ($invoice->status === 'draft')
@push('scripts')
@include('layouts.partials.invoice-lines-js')
@endpush
@endif
