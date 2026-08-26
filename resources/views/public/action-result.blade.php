<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
    @include('layouts.partials.fonts')
    @include('layouts.partials.app-css')
</head>
<body class="guest-body">
    <div class="guest-container">
        <header class="guest-header">
            @include('layouts.partials.logo')
        </header>
        <main class="guest-main">
            <div class="auth-card auth-card--center">
                <h1>{{ $title }}</h1>
                <p>{{ $message }}</p>
                <p><a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a></p>
            </div>
        </main>
        <footer class="guest-footer">
            @include('layouts.partials.legal-footer')
        </footer>
    </div>
</body>
</html>
