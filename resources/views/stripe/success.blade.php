@extends('layouts.panel')

@section('title', 'Suscripción activada — ' . config('app.name'))
@section('heading', 'Plan Pro activado')

@section('content')
<div class="card card-narrow">
    <p>Tu suscripción Pro se ha activado correctamente. Ya puedes crear documentos ilimitados y usar recordatorios automáticos.</p>
    <div class="form-actions">
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al dashboard</a>
    </div>
</div>
@endsection
