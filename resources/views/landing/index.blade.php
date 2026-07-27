@extends('layouts.marketing')

@section('title', config('app.name') . ' — Presupuestos y facturas proforma para autónomos')
@section('meta_description', 'Crea presupuestos, envía facturas proforma por email y cobra más rápido. Para autónomos y pymes en España. Empieza gratis, sin tarjeta.')
@section('og_title', 'PresuFactura — De presupuesto a cobro en minutos')
@section('og_description', 'Presupuestos con enlace público, facturas PDF, recordatorios automáticos. Diseñado para autónomos en España.')

@section('content')
<section class="landing-hero">
    <p class="hero-badge">Para autónomos y pymes en España</p>
    <h1>De presupuesto a cobro<br>en minutos, no en horas</h1>
    <p class="hero-sub">Crea presupuestos y facturas proforma, envíalos por email con PDF y haz seguimiento de cobros desde un panel claro. Sin instalaciones. Empieza gratis.</p>
    <div class="hero-actions">
        @if ($loggedIn)
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al panel</a>
        @else
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Empezar gratis — 3 docs/mes</a>
            <a href="#precios" class="btn btn-secondary btn-lg">Ver precios</a>
        @endif
    </div>
    <p class="hero-note">Sin tarjeta · Configuración en 3 minutos · Documentos proforma</p>
</section>

<section class="landing-screenshots">
    <div class="screenshots-inner">
        <h2>Todo tu flujo de cobro en un solo sitio</h2>
        <p class="section-sub">Dashboard, documentos y recordatorios pensados para autónomos que facturan solos.</p>
        <div class="screenshot-mock">
            <div class="mock-sidebar">
                <span class="mock-brand">PresuFactura</span>
                <span class="mock-nav active">Dashboard</span>
                <span class="mock-nav">Clientes</span>
                <span class="mock-nav">Facturas</span>
                <span class="mock-nav">Presupuestos</span>
            </div>
            <div class="mock-main">
                <div class="mock-stats">
                    <div class="mock-stat"><span>Por cobrar</span><strong>2.450 €</strong></div>
                    <div class="mock-stat danger"><span>Vencido</span><strong>380 €</strong></div>
                    <div class="mock-stat success"><span>Cobrado mes</span><strong>5.120 €</strong></div>
                    <div class="mock-stat"><span>Docs mes</span><strong>2 / 3</strong></div>
                </div>
                <div class="mock-table">
                    <div class="mock-row head"><span>Número</span><span>Cliente</span><span>Estado</span><span>Total</span></div>
                    <div class="mock-row"><span>FAC-2026-012</span><span>Acme SL</span><span class="badge-sent">Enviada</span><span>1.210 €</span></div>
                    <div class="mock-row"><span>PRE-2026-008</span><span>López Design</span><span class="badge-accepted">Aceptada</span><span>890 €</span></div>
                    <div class="mock-row"><span>FAC-2026-011</span><span>Studio Norte</span><span class="badge-paid">Pagada</span><span>450 €</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="landing-features">
    <h2>Por qué autónomos eligen PresuFactura</h2>
    <div class="features-grid">
        <div class="feature-card">
            <h3>Presupuesto → factura</h3>
            <p>El cliente acepta online. Conviertes a factura borrador con un click.</p>
        </div>
        <div class="feature-card">
            <h3>Email + PDF automático</h3>
            <p>Envía documentos proforma con PDF adjunto y enlace público para tu cliente.</p>
        </div>
        <div class="feature-card">
            <h3>Recordatorios Pro</h3>
            <p>Avisos al cliente + email «¿cobraste?» para que no se te escape ningún pago.</p>
        </div>
        <div class="feature-card">
            <h3>Hecho para España</h3>
            <p>IVA, IBAN, NIF/CIF. Proforma honesta — sin prometer Verifactu que no tenemos.</p>
        </div>
    </div>
</section>

<section class="landing-pricing" id="precios">
    <h2>Precios claros</h2>
    <p class="section-sub">Empieza gratis. Escala cuando tu negocio crece.</p>
    <div class="pricing-grid">
        <div class="pricing-card">
            <h3>Free</h3>
            <p class="price">0 €<span>/mes</span></p>
            <ul>
                <li>3 documentos al mes</li>
                <li>Clientes ilimitados</li>
                <li>PDF proforma + email</li>
                <li>Enlace público presupuestos y facturas</li>
            </ul>
            @if (!$loggedIn)
                <a href="{{ route('register') }}" class="btn btn-secondary btn-block">Empezar gratis</a>
            @endif
        </div>
        <div class="pricing-card pricing-pro">
            <span class="pricing-badge">Recomendado</span>
            <h3>Pro</h3>
            <p class="price">12 €<span>/mes</span></p>
            <ul>
                <li>Documentos ilimitados</li>
                <li>Recordatorios automáticos al cliente</li>
                <li>Email «¿cobraste?» día +10</li>
                <li>Botón «He pagado» para clientes</li>
            </ul>
            @if ($loggedIn && !auth()->user()->isPro())
                <form method="POST" action="{{ route('stripe.checkout') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block">Actualizar a Pro</button>
                </form>
            @elseif ($loggedIn)
                <span class="badge badge-paid">Plan activo</span>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-block">Registrarse</a>
            @endif
        </div>
    </div>
</section>

<section class="landing-faq" id="faq">
    <div class="faq-section-inner">
        <h2>Preguntas frecuentes</h2>
        @include('partials.faq-list', ['faqs' => $landingFaqs])
        <p class="faq-more"><a href="{{ route('help') }}">Ver todas las preguntas →</a></p>
    </div>
</section>

<section class="landing-cta">
    <h2>Empieza a facturar más ágil hoy</h2>
    <p>Registro gratuito. Sin permanencia. Cancela Pro cuando quieras.</p>
    @if (!$loggedIn)
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Crear cuenta gratis</a>
    @else
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al panel</a>
    @endif
</section>
@endsection
