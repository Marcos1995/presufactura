<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    @include('layouts.partials.app-css')
</head>
<body class="guest-body">
    <div class="guest-container">
        <header class="guest-header">
            <span class="logo">{{ config('app.name') }}</span>
        </header>
        <main class="guest-main">
            <div class="auth-card">
                <h1>{{ $title }}</h1>
                <p>{{ $message }}</p>
            </div>
        </main>
    </div>
</body>
</html>
