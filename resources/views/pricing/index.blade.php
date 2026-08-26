@extends('layouts.marketing')

@section('title', 'Precios — ' . config('app.name'))
@section('meta_description', 'PresuFactura es completamente gratis: documentos ilimitados, recordatorios automáticos y Veri*Factu opcional. Sin tarjeta ni suscripción.')

@section('content')
<section class="landing-pricing landing-pricing-page reveal">
    <h1>Gratis, con todo incluido</h1>
    <p class="hero-sub">Sin planes de pago. Todas las funciones para todos los usuarios.</p>
    <div class="pricing-grid pricing-grid--single">
        <div class="pricing-card pricing-pro">
            <span class="pricing-badge">Todo incluido</span>
            <h3>Gratis</h3>
            <p class="price">0 €<span>/mes</span></p>
            <ul>
                <li>Documentos ilimitados (presupuestos y facturas)</li>
                <li>Clientes ilimitados</li>
                <li>PDF proforma o fiscal + email</li>
                <li>Enlace público de presupuestos y facturas</li>
                <li>Recordatorios automáticos al cliente (+3/+7/+14 días)</li>
                <li>Email «¿cobraste?» día +10</li>
                <li>Botón «He pagado» para clientes</li>
                <li>Veri*Factu: certificado, QR, hash SIF y envío AEAT</li>
            </ul>
            @if ($loggedIn)
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-block">Ir al panel</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-block" data-analytics="signup_cta_click">Crear cuenta gratis</a>
            @endif
        </div>
    </div>
</section>
@endsection
