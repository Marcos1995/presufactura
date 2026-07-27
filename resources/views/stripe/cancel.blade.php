@extends('layouts.panel')

@section('title', 'Suscripción cancelada — ' . config('app.name'))
@section('heading', 'Pago cancelado')

@section('content')
<div class="card card-narrow">
    <p>Has cancelado el proceso de pago. Puedes volver a intentarlo cuando quieras.</p>
    <div class="form-actions">
        <a href="{{ route('settings.index') }}" class="btn btn-secondary">Volver a configuración</a>
        <a href="{{ route('landing') }}#precios" class="btn btn-primary">Ver planes</a>
    </div>
</div>
@endsection
