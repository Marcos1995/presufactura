@extends('layouts.panel')

@section('title', 'Suscripción — ' . config('app.name'))
@section('heading', 'Suscripción')

@section('content')
<div class="card card-narrow">
    <dl class="settings-list">
        <dt>Plan actual</dt>
        <dd>
            @if ($user->isPro())
                <span class="badge badge-paid">Pro — 12 €/mes</span>
            @else
                <span class="badge badge-draft">Free — 3 docs/mes</span>
            @endif
        </dd>

        <dt>Documentos este mes</dt>
        <dd>{{ $user->documentsThisMonthCount() }} @if(!$user->isPro()) / 3 @endif</dd>

        @if ($user->plan_expires_at)
        <dt>Plan expira</dt>
        <dd>{{ $user->plan_expires_at->format('d/m/Y') }}</dd>
        @endif
    </dl>

    <div class="subscription-actions">
        @if ($user->isPro() && $user->stripe_customer_id)
            <form method="POST" action="{{ route('subscription.portal') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Gestionar suscripción en Stripe</button>
            </form>
            <p class="text-muted">Cambia método de pago, descarga facturas o cancela tu suscripción.</p>
        @elseif (!$user->isPro())
            <form method="POST" action="{{ route('stripe.checkout') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Actualizar a Pro — 12 €/mes</button>
            </form>
            <p class="text-muted">Documentos ilimitados y recordatorios automáticos.</p>
        @endif

        <a href="{{ route('pricing') }}" class="btn btn-secondary">Ver comparativa de planes</a>
    </div>
</div>
@endsection
