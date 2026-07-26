@extends('layouts.panel')

@section('title', 'Clientes — ' . config('app.name'))
@section('heading', 'Clientes')

@section('content')
<div class="empty-state">
    <h2>Sin clientes</h2>
    <p>Añade tus clientes para crear facturas y presupuestos.</p>
    <p class="text-muted">Disponible en Fase 2.</p>
</div>
@endsection
