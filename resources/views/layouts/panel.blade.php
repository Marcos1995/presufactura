<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @stack('head')
</head>
<body class="panel-body">
    <div class="panel-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <a href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    Clientes
                </a>
                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    Facturas
                </a>
                <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                    Presupuestos
                </a>
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    Configuración
                </a>
                <a href="{{ route('subscription.index') }}" class="nav-link {{ request()->routeIs('subscription.*') ? 'active' : '' }}">
                    Suscripción
                </a>
            </nav>
            <div class="sidebar-footer">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-link">Cerrar sesión</button>
                </form>
            </div>
        </aside>
        <div class="panel-content">
            <header class="panel-header">
                <h1>@yield('heading', 'Panel')</h1>
            </header>
            <main class="panel-main">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
            <footer class="panel-footer">
                @include('layouts.partials.legal-footer')
            </footer>
        </div>
    </div>
    @include('layouts.partials.upgrade-modal')
    @if (session('show_upgrade_modal'))
    <script>
        $(function() {
            $('#upgrade-modal').show();
            $('#upgrade-modal-close, #upgrade-modal').on('click', function(e) {
                if (e.target === this) $('#upgrade-modal').hide();
            });
        });
    </script>
    @else
    <script>
        $(function() {
            $('#upgrade-modal-close, #upgrade-modal').on('click', function(e) {
                if (e.target === this) $('#upgrade-modal').hide();
            });
        });
    </script>
    @endif
    @include('layouts.partials.cookie-banner')
    @stack('scripts')
</body>
</html>
