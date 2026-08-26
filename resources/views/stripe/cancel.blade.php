@extends('layouts.panel')

@section('title', 'Cuenta — ' . config('app.name'))
@section('heading', 'Cuenta')

@section('content')
<div class="card card-narrow">
    <p>PresuFactura es gratis y no tiene planes de pago. Todas las funciones están en tu cuenta.</p>
    <div class="form-actions">
        <a href="{{ route('settings.index') }}" class="btn btn-secondary">Ir a configuración</a>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al dashboard</a>
    </div>
</div>
@endsection
