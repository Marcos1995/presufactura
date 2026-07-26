@extends('layouts.panel')

@section('title', 'Dashboard — ' . config('app.name'))
@section('heading', 'Dashboard')

@section('content')
<div class="dashboard-grid">
    <div class="stat-card">
        <span class="stat-label">Por cobrar</span>
        <span class="stat-value">—</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Vencido</span>
        <span class="stat-value">—</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Cobrado este mes</span>
        <span class="stat-value">—</span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Documentos este mes</span>
        <span class="stat-value">0 / 3</span>
    </div>
</div>

<div class="empty-state">
    <h2>Bienvenido a PresuFactura</h2>
    <p>Configura tu perfil fiscal y crea tu primera factura o presupuesto.</p>
    <div class="empty-actions">
        <a href="{{ route('settings.index') }}" class="btn btn-secondary">Configuración</a>
        <a href="{{ route('clients.index') }}" class="btn btn-primary">Añadir cliente</a>
    </div>
</div>
@endsection
