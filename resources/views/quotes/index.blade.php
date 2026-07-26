@extends('layouts.panel')

@section('title', 'Presupuestos — ' . config('app.name'))
@section('heading', 'Presupuestos')

@section('content')
<div class="empty-state">
    <h2>Sin presupuestos</h2>
    <p>Crea presupuestos y conviértelos en facturas con un click.</p>
    <p class="text-muted">Disponible en Fase 4.</p>
</div>
@endsection
