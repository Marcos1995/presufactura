@extends('layouts.marketing')

@section('title', config('app.name') . ' — Presupuestos y facturas para autónomos con Veri*Factu')
@section('meta_description', 'Presupuestos, facturas proforma o fiscales con Veri*Factu, PDF con QR y envío a AEAT. Para autónomos y pymes en España. Completamente gratis, sin tarjeta.')
@section('og_title', 'PresuFactura — De presupuesto a cobro en minutos')
@section('og_description', 'Presupuestos con enlace público, facturas con Veri*Factu opcional, recordatorios automáticos. Todo gratis para autónomos en España.')

@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "PresuFactura",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "Web",
    "url": "https://presufactura.es",
    "description": "Presupuestos y facturas para autónomos en España, con Veri*Factu opcional.",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "EUR"
    }
}
</script>
    @include('partials.faq-jsonld', ['faqs' => $landingFaqs])
@endpush

@section('content')
<section class="landing-hero">
    <div class="hero-bg-orbs" aria-hidden="true">
        <div class="hero-orb hero-orb--1"></div>
        <div class="hero-orb hero-orb--2"></div>
        <div class="hero-orb hero-orb--3"></div>
    </div>
    <div class="hero-inner">
        <div class="hero-copy">
            <p class="hero-badge reveal">Veri*Factu opcional · Autónomos en España</p>
            <h1 class="reveal reveal-delay-1">De presupuesto a cobro<br>en minutos, no en horas</h1>
            <p class="hero-sub reveal reveal-delay-2">Crea presupuestos y facturas, envíalos por email con PDF y activa Veri*Factu cuando quieras cumplir con Hacienda. Sin instalaciones. Completamente gratis.</p>
            <div class="hero-actions reveal reveal-delay-3">
                @if ($loggedIn)
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al panel</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg" data-analytics="signup_cta_click">Empezar gratis</a>
                    <a href="#precios" class="btn btn-secondary btn-lg">Ver qué incluye</a>
                @endif
            </div>
            <p class="hero-note reveal reveal-delay-3">Sin tarjeta · Sin límites · Veri*Factu opcional</p>
            <div class="trust-strip reveal reveal-delay-4">
                <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> IVA incluido</span>
                <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> PDF + email</span>
                <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Enlace público</span>
                <span class="trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> QR Veri*Factu</span>
            </div>
        </div>
        @include('partials.landing-product-demo')
    </div>
</section>

<section class="landing-pricing reveal" id="verifactu">
    <h2>Facturas verificables ante Hacienda</h2>
    <p class="section-sub">Actívalo en Configuración cuando quieras. Sin Veri*Factu los documentos son proforma; con certificado .p12 las facturas enviadas cumplen el RRSIF.</p>
    <div class="pricing-grid pricing-grid--single">
        <div class="pricing-card pricing-pro">
            <span class="pricing-badge">Veri*Factu</span>
            <ul>
                <li>Certificado electrónico .p12 del autónomo</li>
                <li>Hash encadenado y registro SIF por factura</li>
                <li>Código QR en el PDF y envío SOAP a la AEAT</li>
                <li>Anulación y rectificativas cuando Hacienda acepta el alta</li>
            </ul>
            @if ($loggedIn)
                <a href="{{ route('settings.index') }}" class="btn btn-primary btn-block">Activar en Configuración</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-block" data-analytics="signup_cta_click">Crear cuenta y activarlo</a>
            @endif
        </div>
    </div>
</section>

<section class="landing-screenshots" id="capturas">
    <div class="screenshots-inner">
        <h2>Capturas de la app</h2>
        <p class="section-sub">Presupuesto, factura y QR Veri*Factu. Así queda al usarlo.</p>
        <div class="shot-grid">
            <figure>
                <img src="{{ asset('images/hero-presupuesto.jpg') }}" alt="Pantalla de presupuestos: cliente acepta online" width="1280" height="720">
                <figcaption>Presupuesto aceptado</figcaption>
            </figure>
            <figure>
                <img src="{{ asset('images/hero-factura.jpg') }}" alt="Pantalla de facturas enviadas con PDF" width="1280" height="720">
                <figcaption>Factura y PDF</figcaption>
            </figure>
            <figure>
                <img src="{{ asset('images/hero-verifactu.jpg') }}" alt="Factura con código QR Veri*Factu" width="1280" height="720">
                <figcaption>QR Veri*Factu</figcaption>
            </figure>
        </div>
    </div>
</section>

<section class="landing-flow">
    <h2 class="reveal">Tres pasos. Cobras.</h2>
    <p class="section-sub reveal">El flujo de un autónomo, sin Excel ni plantillas sueltas.</p>
    <ol class="flow-track">
        <li class="flow-step reveal">
            <span class="flow-num">01</span>
            <h3>Presupuesto</h3>
            <p>Líneas, IVA e IBAN. El cliente lo ve y lo acepta en un enlace público.</p>
        </li>
        <li class="flow-step reveal reveal-delay-1">
            <span class="flow-num">02</span>
            <h3>Factura</h3>
            <p>Un clic la convierte a borrador. PDF por email. Veri*Factu si lo activas.</p>
        </li>
        <li class="flow-step reveal reveal-delay-2">
            <span class="flow-num">03</span>
            <h3>Cobro</h3>
            <p>Recordatorios al cliente y «¿cobraste?» a ti. El cliente puede pulsar «He pagado».</p>
        </li>
    </ol>
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
            <h3>Recordatorios automáticos</h3>
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
    <h2>Gratis, con todo incluido</h2>
    <p class="section-sub">Sin planes de pago. Todas las funciones para todos los usuarios.</p>
    <div class="pricing-grid pricing-grid--single">
        <div class="pricing-card pricing-pro">
            <span class="pricing-badge">Todo incluido</span>
            <h3>Gratis</h3>
            <p class="price">0 €<span>/mes</span></p>
            <ul>
                <li>Documentos ilimitados</li>
                <li>Clientes ilimitados</li>
                <li>PDF + email + enlace público</li>
                <li>Recordatorios automáticos al cliente</li>
                <li>Email «¿cobraste?» y botón «He pagado»</li>
                <li>Veri*Factu opcional (certificado + AEAT)</li>
            </ul>
            @if ($loggedIn)
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-block">Ir al panel</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-block">Crear cuenta gratis</a>
            @endif
        </div>
    </div>
</section>

<section class="landing-faq reveal" id="faq">
    <div class="faq-section-inner">
        <h2>Preguntas frecuentes</h2>
        @include('partials.faq-list', ['faqs' => $landingFaqs])
        <p class="faq-more"><a href="{{ route('help') }}">Ver todas las preguntas →</a> · <a href="{{ route('guides.index') }}">Guías para autónomos →</a></p>
    </div>
</section>

<section class="landing-cta reveal">
    <h2>Empieza a facturar más ágil hoy</h2>
    <p>Registro gratuito. Sin tarjeta. Sin permanencia.</p>
    @if (!$loggedIn)
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg" data-analytics="signup_cta_click">Crear cuenta gratis</a>
    @else
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al panel</a>
    @endif
</section>
@endsection
