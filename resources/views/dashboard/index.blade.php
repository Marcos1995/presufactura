@extends('layouts.panel')

@section('title', 'Dashboard — ' . config('app.name'))
@section('heading', 'Dashboard')

@section('content')
<div class="dashboard-grid">
    <div class="stat-card">
        <span class="stat-label">Por cobrar</span>
        <span class="stat-value">{{ number_format($pendingTotal, 2, ',', '.') }} €</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Vencido</span>
        <span class="stat-value stat-danger">{{ number_format($overdueTotal, 2, ',', '.') }} €</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Cobrado este mes</span>
        <span class="stat-value stat-success">{{ number_format($collectedMonth, 2, ',', '.') }} €</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Documentos este mes</span>
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
            <tr>
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
    <h2>Bienvenido a PresuFactura</h2>
    <p>Configura tu perfil fiscal y crea tu primera factura o presupuesto.</p>
    <div class="empty-actions">
        <a href="{{ route('settings.index') }}" class="btn btn-secondary">Configuración</a>
        <a href="{{ route('clients.index') }}" class="btn btn-primary">Añadir cliente</a>
    </div>
</div>
@endif
@endsection
