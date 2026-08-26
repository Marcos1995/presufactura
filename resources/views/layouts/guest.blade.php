<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.fonts')
    <meta name="description" content="@yield('meta_description', '')">
    <meta name="robots" content="@yield('robots', 'noindex, nofollow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    @include('layouts.partials.app-css')
</head>
<body class="guest-body {{ ($mainClass ?? '') === 'guest-main-wide' ? 'guest-body-top' : '' }}">
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
    @include('layouts.partials.app-js')
</body>
</html>
