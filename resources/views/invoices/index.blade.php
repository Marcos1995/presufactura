@extends('layouts.panel')

@section('title', 'Facturas — ' . config('app.name'))
@section('heading', 'Facturas')

@section('content')
<div class="page-toolbar">
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">Nueva factura</a>
</div>

@if ($invoices->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/></svg>
        </div>
        <h2>Sin facturas</h2>
        <p>Crea tu primera factura proforma y envíala a tu cliente por email.</p>
        <div class="empty-actions">
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">Nueva factura</a>
        </div>
    </div>
@else
    <div class="card">
        <table class="data-table data-table-list">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>AEAT</th>
                    <th class="text-right">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                <tr data-href="{{ route('invoices.show', $invoice) }}">
                    <td data-label="Número"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                    <td data-label="Cliente">{{ $invoice->client->name }}</td>
                    <td data-label="Fecha">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                    <td data-label="Estado"><span class="badge badge-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span></td>
                    <td data-label="AEAT">
                        @if (($verifactuAvailable ?? false) && $invoice->billingRecord)
                            <span class="badge badge-{{ $invoice->billingRecord->aeat_status }}">{{ $invoice->billingRecord->aeatStatusLabel() }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Total" class="text-right">{{ number_format($invoice->total, 2, ',', '.') }} €</td>
                    <td class="table-actions">
                        <a href="{{ route('invoices.show', $invoice) }}">Ver</a>
                        <a href="{{ route('invoices.pdf', $invoice) }}">PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
