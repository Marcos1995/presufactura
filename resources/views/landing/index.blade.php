<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Presupuestos y facturas para autónomos</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="landing-body">
    <header class="landing-header">
        <a href="{{ route('landing') }}" class="logo">{{ config('app.name') }}</a>
        <nav class="landing-nav">
            @if ($loggedIn)
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Panel</a>
            @else
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Registrarse</a>
            @endif
        </nav>
    </header>

    <section class="landing-hero">
        <h1>Presupuestos y facturas proforma en minutos</h1>
        <p class="hero-sub">Para autónomos en España. Crea, envía, cobra. Sin complicaciones.</p>
        <div class="hero-actions">
            @if ($loggedIn)
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Ir al panel</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Empezar gratis</a>
                <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">Iniciar sesión</a>
            @endif
        </div>
    </section>

    <section class="landing-features">
        <h2>Todo lo que necesitas</h2>
        <div class="features-grid">
            <div class="feature-card">
                <h3>Presupuestos con enlace público</h3>
                <p>Comparte con tu cliente. Aceptación online con un click.</p>
            </div>
            <div class="feature-card">
                <h3>Facturas proforma + PDF</h3>
                <p>Genera y envía por email con PDF adjunto.</p>
            </div>
            <div class="feature-card">
                <h3>Recordatorios de cobro</h3>
                <p>Automáticos al cliente y aviso al autónomo (plan Pro).</p>
            </div>
            <div class="feature-card">
                <h3>Dashboard de cobros</h3>
                <p>Por cobrar, vencido y cobrado del mes en un vistazo.</p>
            </div>
        </div>
    </section>

    <section class="landing-pricing" id="precios">
        <h2>Precios</h2>
        <div class="pricing-grid">
            <div class="pricing-card">
                <h3>Free</h3>
                <p class="price">0 €<span>/mes</span></p>
                <ul>
                    <li>3 documentos al mes</li>
                    <li>Clientes ilimitados</li>
                    <li>PDF proforma</li>
                    <li>Enlace público presupuestos</li>
                </ul>
                @if (!$loggedIn)
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-block">Empezar</a>
                @endif
            </div>
            <div class="pricing-card pricing-pro">
                <h3>Pro</h3>
                <p class="price">12 €<span>/mes</span></p>
                <ul>
                    <li>Documentos ilimitados</li>
                    <li>Recordatorios automáticos al cliente</li>
                    <li>Email «¿cobraste?» día +10</li>
                    <li>Todo lo del plan Free</li>
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

    <footer class="landing-footer">
        <p><strong>{{ config('app.name') }}</strong> — Documentos proforma. Sin Verifactu v1.</p>
        <p>Los documentos generados son a efectos informativos. El usuario es responsable de cumplir la normativa fiscal aplicable.</p>
    </footer>
</body>
</html>
