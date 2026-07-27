@extends('layouts.panel')

@section('title', 'Configuración — ' . config('app.name'))
@section('heading', 'Configuración')

@section('content')
<div class="settings-preview">
    <dl class="settings-list">
        <dt>Nombre</dt>
        <dd>{{ auth()->user()->name }}</dd>
        <dt>Email</dt>
        <dd>{{ auth()->user()->email }}</dd>
        <dt>Plan</dt>
        <dd>
            {{ auth()->user()->plan === 'pro' ? 'Pro' : 'Free (3 docs/mes)' }}
            @if (!auth()->user()->isPro())
                <form method="POST" action="{{ route('stripe.checkout') }}" class="inline-form" style="margin-left:1rem">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Actualizar a Pro — 12 €/mes</button>
                </form>
            @endif
        </dd>
        <dt>IVA por defecto</dt>
        <dd>{{ number_format(auth()->user()->default_vat_rate, 0) }}%</dd>
    </dl>
</div>
@endsection
