@extends('layouts.marketing')

@section('title', 'Precios — ' . config('app.name'))
@section('meta_description', 'Planes Free y Pro de PresuFactura. Empieza gratis con 3 documentos al mes o Pro por 12 €/mes con recordatorios automáticos.')

@section('content')
<section class="landing-pricing landing-pricing-page">
    <h1>Precios simples, sin sorpresas</h1>
    <p class="hero-sub">Empieza gratis. Actualiza cuando lo necesites.</p>
    <div class="pricing-grid">
        <div class="pricing-card">
            <h3>Free</h3>
            <p class="price">0 €<span>/mes</span></p>
            <ul>
                <li>3 documentos al mes</li>
                <li>Clientes ilimitados</li>
                <li>PDF proforma</li>
                <li>Enlace público presupuestos y facturas</li>
                <li>Email al enviar documentos</li>
            </ul>
            @if (!$loggedIn)
                <a href="{{ route('register') }}" class="btn btn-secondary btn-block">Empezar gratis</a>
            @endif
        </div>
        <div class="pricing-card pricing-pro">
            <h3>Pro</h3>
            <p class="price">12 €<span>/mes</span></p>
            <ul>
                <li>Documentos ilimitados</li>
                <li>Recordatorios automáticos al cliente (+3/+7/+14 días)</li>
                <li>Email «¿cobraste?» día +10</li>
                <li>Botón «He pagado» para clientes</li>
                <li>Todo lo del plan Free</li>
            </ul>
            @if ($loggedIn && !auth()->user()->isPro())
                <form method="POST" action="{{ route('stripe.checkout') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block">Actualizar a Pro</button>
                </form>
            @elseif ($loggedIn)
                <a href="{{ route('subscription.index') }}" class="btn btn-secondary btn-block">Gestionar suscripción</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-block">Registrarse</a>
            @endif
        </div>
    </div>
</section>
@endsection
