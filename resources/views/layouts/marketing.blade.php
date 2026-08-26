<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.fonts')
    <meta name="description" content="@yield('meta_description', 'PresuFactura: presupuestos y facturas para autónomos en España. Veri*Factu opcional, PDF con QR, envío a AEAT. Empieza gratis.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PresuFactura">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title')))">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description')))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-presufactura.svg'))">
    <meta property="og:locale" content="es_ES">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', trim($__env->yieldContent('title')))">
    <meta name="twitter:description" content="@yield('og_description', trim($__env->yieldContent('meta_description')))">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-presufactura.svg'))">

    @include('layouts.partials.app-css')
    @stack('head')
</head>
<body class="landing-body">
    <header class="landing-header">
        <div class="landing-header-inner">
            @include('layouts.partials.logo', ['href' => route('landing')])
            <button type="button" class="nav-toggle" aria-label="Menú" aria-expanded="false">☰</button>
            <nav class="landing-nav">
                <a href="{{ url('/#verifactu') }}">Veri*Factu</a>
                <a href="{{ route('guides.index') }}">Guías</a>
                <a href="{{ route('pricing') }}">Precios</a>
                <a href="{{ route('help') }}">Ayuda</a>
                @if (auth()->check())
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Panel</a>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Registrarse</a>
                @endif
            </nav>
        </div>
    </header>

    @if (session('status'))
        <div class="landing-flash">
            <div class="alert alert-success">{{ session('status') }}</div>
        </div>
    @endif

    @yield('content')

    <footer class="landing-footer">
        <p><strong>{{ config('app.name') }}</strong> — Presupuestos y facturas para autónomos con Veri*Factu opcional</p>
        @include('layouts.partials.legal-footer')
    </footer>
    @include('layouts.partials.cookie-banner')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @include('layouts.partials.app-js')
    @stack('scripts')
</body>
</html>
