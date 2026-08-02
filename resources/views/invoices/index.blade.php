@extends('layouts.panel')

@section('title', 'Facturas — ' . config('app.name'))
@section('heading', 'Facturas')

@section('content')
<div class="page-toolbar">
    @if (auth()->user()->canCreateDocument())
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">Nueva factura</a>
    @else
        <span class="text-muted">Límite Free alcanzado (3 docs/mes)</span>
    @endif
</div>

@if ($invoices->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/></svg>
        </div>
        <h2>Sin facturas</h2>
        <p>Crea tu primera factura proforma y envíala a tu cliente por email.</p>
        @if (auth()->user()->canCreateDocument())
        <div class="empty-actions">
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">Nueva factura</a>
        </div>
        @endif
    </div>
@else
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th class="text-right">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                <tr data-href="{{ route('invoices.show', $invoice) }}">
                    <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                    <td>{{ $invoice->client->name }}</td>
                    <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                    <td><span class="badge badge-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span></td>
                    <td class="text-right">{{ number_format($invoice->total, 2, ',', '.') }} €</td>
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
