<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Página no encontrada — PresuFactura</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.app-css')
</head>
<body class="guest-body">
    <div class="guest-container">
        <header class="guest-header">
            <a href="{{ url('/') }}" class="brand-logo">
                <img src="{{ asset('images/logo-icon.svg') }}" alt="" class="brand-logo__icon" width="32" height="32">
                <span class="brand-logo__text">PresuFactura</span>
            </a>
        </header>
        <main class="guest-main">
            <div class="auth-card auth-card--center">
                <h1>Página no encontrada</h1>
                <p>El enlace no existe o ya no está disponible.</p>
                <p><a href="{{ url('/') }}" class="btn btn-primary">Volver al inicio</a></p>
            </div>
        </main>
    </div>
</body>
</html>
