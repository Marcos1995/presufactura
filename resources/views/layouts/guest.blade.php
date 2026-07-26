<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-body">
    <div class="guest-container">
        <header class="guest-header">
            <a href="{{ url('/') }}" class="logo">{{ config('app.name') }}</a>
        </header>
        <main class="guest-main">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
        <footer class="guest-footer">
            <p>Documentos generados a efectos informativos. El usuario es responsable de cumplir la normativa fiscal aplicable.</p>
        </footer>
    </div>
</body>
</html>
