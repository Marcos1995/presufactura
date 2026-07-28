<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'PresuFactura: presupuestos y facturas proforma para autónomos y pequeñas empresas en España. Envía PDF, cobra más rápido.')">
    <meta name="robots" content="index, follow">
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

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body class="landing-body">
    <header class="landing-header">
        <div class="landing-header-inner">
            <a href="{{ route('landing') }}" class="logo">{{ config('app.name') }}</a>
            <nav class="landing-nav">
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
        <p><strong>{{ config('app.name') }}</strong> — Presupuestos y facturas proforma para autónomos</p>
        @include('layouts.partials.legal-footer')
    </footer>
    @include('layouts.partials.cookie-banner')
    @stack('scripts')
</body>
</html>
