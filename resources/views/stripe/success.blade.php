@extends('layouts.panel')

@section('title', 'Cuenta — ' . config('app.name'))
@section('heading', 'Cuenta')

@section('content')
<div class="card card-narrow">
    <p>PresuFactura es gratis. Todas las funciones están disponibles en tu cuenta, sin suscripción.</p>
    <div class="form-actions">
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al dashboard</a>
    </div>
</div>
@endsection
