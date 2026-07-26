@extends('layouts.panel')

@section('title', 'Configuración — ' . config('app.name'))
@section('heading', 'Configuración')

@section('content')
<div class="settings-preview">
    <p>Datos fiscales, IVA, prefijos de numeración y recordatorios.</p>
    <p class="text-muted">Edición completa en Fase 2.</p>

    <dl class="settings-list">
        <dt>Nombre</dt>
        <dd>{{ auth()->user()->name }}</dd>
        <dt>Email</dt>
        <dd>{{ auth()->user()->email }}</dd>
        <dt>Plan</dt>
        <dd>{{ auth()->user()->plan === 'pro' ? 'Pro' : 'Free (3 docs/mes)' }}</dd>
        <dt>IVA por defecto</dt>
        <dd>{{ number_format(auth()->user()->default_vat_rate, 0) }}%</dd>
    </dl>
</div>
@endsection
