<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.fonts')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @stack('head')
</head>
<body class="panel-body">
    <div class="panel-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                @include('layouts.partials.logo', ['href' => route('dashboard'), 'variant' => 'sidebar'])
                <span class="sidebar-plan-badge sidebar-plan-badge--{{ auth()->user()->isPro() ? 'pro' : 'free' }}">
                    {{ auth()->user()->isPro() ? 'Pro' : 'Free' }}
                </span>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Clientes
                </a>
                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                    Facturas
                </a>
                <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
                    Presupuestos
                </a>
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    Configuración
                </a>
                <a href="{{ route('subscription.index') }}" class="nav-link {{ request()->routeIs('subscription.*') ? 'active' : '' }}">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
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
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
