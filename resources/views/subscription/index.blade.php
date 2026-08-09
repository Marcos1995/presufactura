@extends('layouts.panel')

@section('title', 'Cuenta — ' . config('app.name'))
@section('heading', 'Cuenta')

@section('content')
<div class="card">
    <dl class="detail-list">
        <dt>Acceso</dt>
        <dd><span class="badge badge-paid">Gratis — todo incluido</span></dd>

        <dt>Documentos este mes</dt>
        <dd>{{ $user->documentsThisMonthCount() }}</dd>
    </dl>

    <p class="text-muted" style="margin-top:1rem;">PresuFactura no tiene planes de pago. Todas las funciones están disponibles para tu cuenta.</p>

    <div class="form-actions" style="margin-top:1.25rem;">
        <a href="{{ route('settings.index') }}" class="btn btn-primary">Ir a configuración</a>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Volver al dashboard</a>
    </div>
</div>
@endsection
