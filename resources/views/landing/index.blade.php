@extends('layouts.marketing')

@section('title', config('app.name') . ' — Presupuestos y facturas para autónomos con Veri*Factu')
@section('meta_description', 'Presupuestos, facturas proforma o fiscales con Veri*Factu, PDF con QR y envío a AEAT. Para autónomos y pymes en España. Empieza gratis, sin tarjeta.')
@section('og_title', 'PresuFactura — De presupuesto a cobro en minutos')
@section('og_description', 'Presupuestos con enlace público, facturas con Veri*Factu opcional, recordatorios automáticos. Diseñado para autónomos en España.')

@section('content')
<section class="landing-hero">
    <div class="hero-bg-orbs" aria-hidden="true">
        <div class="hero-orb hero-orb--1"></div>
        <div class="hero-orb hero-orb--2"></div>
        <div class="hero-orb hero-orb--3"></div>
    </div>
    <p class="hero-badge reveal">Para autónomos y pymes en España</p>
    <h1 class="reveal reveal-delay-1">De presupuesto a cobro<br>en minutos, no en horas</h1>
    <p class="hero-sub reveal reveal-delay-2">Crea presupuestos y facturas, envíalos por email con PDF y activa Veri*Factu cuando quieras cumplir con Hacienda. Sin instalaciones. Empieza gratis.</p>
    <div class="hero-actions reveal reveal-delay-3">
        @if ($loggedIn)
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al panel</a>
        @else
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Empezar gratis — 3 docs/mes</a>
            <a href="#precios" class="btn btn-secondary btn-lg">Ver precios</a>
        @endif
    </div>
    <p class="hero-note reveal reveal-delay-3">Sin tarjeta · Configuración en 3 minutos · Veri*Factu opcional</p>
    <div class="trust-strip reveal reveal-delay-4">
        <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> IVA incluido</span>
        <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> PDF + email</span>
        <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Enlace público</span>
        <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Veri*Factu</span>
    </div>
</section>

<section class="landing-screenshots">
    <div class="screenshots-inner">
        <h2 class="reveal">Todo tu flujo de cobro en un solo sitio</h2>
        <p class="section-sub reveal">Dashboard, documentos y recordatorios pensados para autónomos que facturan solos. <em>Pulsa las pestañas ↓</em></p>
        <div class="screenshot-mock reveal">
            <div class="mock-sidebar">
                <span class="mock-brand">
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="" width="24" height="24">
                    PresuFactura
                </span>
                <button type="button" class="mock-nav active" data-mock-tab="dashboard">Dashboard</button>
                <button type="button" class="mock-nav" data-mock-tab="invoices">Facturas</button>
                <button type="button" class="mock-nav" data-mock-tab="quotes">Presupuestos</button>
            </div>
            <div class="mock-main">
                <div data-mock-panel="dashboard">
                    <div class="mock-panel-title">Vista general</div>
                    <div class="mock-stats">
                        <div class="mock-stat"><span>Por cobrar</span><strong data-count-to="2450" data-count-prefix="" data-count-suffix=" €" data-count-decimals="0">0 €</strong></div>
                        <div class="mock-stat danger"><span>Vencido</span><strong data-count-to="380" data-count-suffix=" €" data-count-decimals="0">0 €</strong></div>
                        <div class="mock-stat success"><span>Cobrado mes</span><strong data-count-to="5120" data-count-suffix=" €" data-count-decimals="0">0 €</strong></div>
                        <div class="mock-stat"><span>Docs mes</span><strong>2 / 3</strong></div>
                    </div>
                    <div class="mock-table">
                        <div class="mock-row head"><span>Número</span><span>Cliente</span><span>Estado</span><span>Total</span></div>
                        <div class="mock-row"><span>FAC-2026-012</span><span>Acme SL</span><span class="badge-sent">Enviada</span><span>1.210 €</span></div>
                        <div class="mock-row"><span>PRE-2026-008</span><span>López Design</span><span class="badge-accepted">Aceptada</span><span>890 €</span></div>
                        <div class="mock-row"><span>FAC-2026-011</span><span>Studio Norte</span><span class="badge-paid">Pagada</span><span>450 €</span></div>
                    </div>
                </div>
                <div data-mock-panel="invoices" hidden>
                    <div class="mock-panel-title">Facturas recientes</div>
                    <div class="mock-table">
                        <div class="mock-row head"><span>Número</span><span>Cliente</span><span>Estado</span><span>Total</span></div>
                        <div class="mock-row"><span>FAC-2026-012</span><span>Acme SL</span><span class="badge-sent">Enviada</span><span>1.210 €</span></div>
                        <div class="mock-row"><span>FAC-2026-011</span><span>Studio Norte</span><span class="badge-paid">Pagada</span><span>450 €</span></div>
                        <div class="mock-row"><span>FAC-2026-010</span><span>María Ruiz</span><span class="badge-sent">Enviada</span><span>320 €</span></div>
                    </div>
                </div>
                <div data-mock-panel="quotes" hidden>
                    <div class="mock-panel-title">Presupuestos activos</div>
                    <div class="mock-table">
                        <div class="mock-row head"><span>Número</span><span>Cliente</span><span>Estado</span><span>Total</span></div>
                        <div class="mock-row"><span>PRE-2026-008</span><span>López Design</span><span class="badge-accepted">Aceptada</span><span>890 €</span></div>
                        <div class="mock-row"><span>PRE-2026-007</span><span>TechStart</span><span class="badge-sent">Enviada</span><span>2.400 €</span></div>
                        <div class="mock-row"><span>PRE-2026-006</span><span>Consulting Pro</span><span class="badge-sent">Enviada</span><span>650 €</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="landing-features">
    <h2 class="reveal">Por qué autónomos eligen PresuFactura</h2>
    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
            <h3>Presupuesto → factura</h3>
            <p>El cliente acepta online. Conviertes a factura borrador con un click.</p>
        </div>
        <div class="feature-card reveal reveal-delay-1">
            <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg></div>
            <h3>Email + PDF automático</h3>
            <p>PDF adjunto, enlace público y badge fiscal con QR si activas Veri*Factu.</p>
        </div>
        <div class="feature-card reveal reveal-delay-2">
            <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
            <h3>Recordatorios Pro</h3>
            <p>Avisos al cliente + email «¿cobraste?» para que no se te escape ningún pago.</p>
        </div>
        <div class="feature-card reveal reveal-delay-3">
            <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20M2 12h20"/></svg></div>
            <h3>Hecho para España</h3>
            <p>IVA, IBAN, NIF/CIF y soporte Veri*Factu con certificado electrónico y envío a AEAT.</p>
        </div>
    </div>
</section>

<section class="landing-pricing reveal" id="precios">
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

<section class="landing-faq reveal" id="faq">
    <div class="faq-section-inner">
        <h2>Preguntas frecuentes</h2>
        @include('partials.faq-list', ['faqs' => $landingFaqs])
        <p class="faq-more"><a href="{{ route('help') }}">Ver todas las preguntas →</a></p>
    </div>
</section>

<section class="landing-cta reveal">
    <h2>Empieza a facturar más ágil hoy</h2>
    <p>Registro gratuito. Sin permanencia. Cancela Pro cuando quieras.</p>
    @if (!$loggedIn)
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Crear cuenta gratis</a>
    @else
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al panel</a>
    @endif
</section>
@endsection
