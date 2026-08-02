@extends('layouts.panel')

@section('title', 'Dashboard — ' . config('app.name'))
@section('heading', 'Dashboard')

@section('content')
<div class="dashboard-grid">
    <div class="stat-card stat-card--pending">
        <div class="stat-card__top">
            <span class="stat-label">Por cobrar</span>
            <span class="stat-icon stat-icon--pending">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
        </div>
        <span class="stat-value">{{ number_format($pendingTotal, 2, ',', '.') }} €</span>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__top">
            <span class="stat-label">Vencido</span>
            <span class="stat-icon stat-icon--danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            </span>
        </div>
        <span class="stat-value stat-danger">{{ number_format($overdueTotal, 2, ',', '.') }} €</span>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-card__top">
            <span class="stat-label">Cobrado este mes</span>
            <span class="stat-icon stat-icon--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
            </span>
        </div>
        <span class="stat-value stat-success">{{ number_format($collectedMonth, 2, ',', '.') }} €</span>
    </div>
    <div class="stat-card stat-card--docs">
        <div class="stat-card__top">
            <span class="stat-label">Documentos este mes</span>
            <span class="stat-icon stat-icon--docs">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            </span>
        </div>
        <span class="stat-value">{{ $docsThisMonth }}{{ $docsLimit ? ' / ' . $docsLimit : '' }}</span>
    </div>
</div>

@if ($recentDocuments->isNotEmpty())
<div class="card">
    <h2 class="section-title">Últimos documentos</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-right">Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recentDocuments as $doc)
            <tr data-href="{{ $doc->isInvoice() ? route('invoices.show', $doc) : route('quotes.show', $doc) }}">
                <td>{{ $doc->number }}</td>
                <td>{{ $doc->isInvoice() ? 'Factura' : 'Presupuesto' }}</td>
                <td>{{ $doc->client->name }}</td>
                <td><span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span></td>
                <td class="text-right">{{ number_format($doc->total, 2, ',', '.') }} €</td>
                <td class="table-actions">
                    @if ($doc->isInvoice())
                        <a href="{{ route('invoices.show', $doc) }}">Ver</a>
                    @else
                        <a href="{{ route('quotes.show', $doc) }}">Ver</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="empty-state">
    <div class="empty-state__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    </div>
    <h2>Bienvenido a PresuFactura</h2>
    <p>Configura tu perfil fiscal y crea tu primera factura o presupuesto.</p>
    <div class="empty-actions">
        <a href="{{ route('settings.index') }}" class="btn btn-secondary">Configuración</a>
        <a href="{{ route('clients.index') }}" class="btn btn-primary">Añadir cliente</a>
    </div>
</div>
@endif
@endsection
