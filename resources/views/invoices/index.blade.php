@extends('layouts.panel')

@section('title', 'Facturas — ' . config('app.name'))
@section('heading', 'Facturas')

@section('content')
<div class="empty-state">
    <h2>Sin facturas</h2>
    <p>Crea tu primera factura proforma.</p>
    <p class="text-muted">Disponible en Fase 2.</p>
</div>
@endsection
