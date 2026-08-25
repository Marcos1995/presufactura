<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.fonts')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-body {{ ($mainClass ?? '') === 'guest-main-wide' ? 'guest-body-top' : '' }}">
    <div class="hero-bg-orbs guest-orbs" aria-hidden="true">
        <div class="hero-orb hero-orb--1"></div>
        <div class="hero-orb hero-orb--2"></div>
        <div class="hero-orb hero-orb--3"></div>
    </div>
    <div class="guest-container">
        <header class="guest-header">
            @include('layouts.partials.logo')
        </header>
        <main class="guest-main {{ $mainClass ?? '' }}">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
        <footer class="guest-footer">
            @include('layouts.partials.legal-footer')
        </footer>
    </div>
    @include('layouts.partials.cookie-banner')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
