<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.fonts')
    @include('layouts.partials.app-css')
    <script>
        try { if (localStorage.getItem('pf-sidebar') === 'collapsed') document.documentElement.classList.add('sidebar-collapsed'); } catch (e) {}
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @stack('head')
</head>
<body class="panel-body">
    <div class="panel-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                @include('layouts.partials.logo', ['href' => route('dashboard'), 'variant' => 'sidebar'])
                <button type="button" class="menu-fold" id="sidebar-toggle" aria-expanded="true" aria-label="Plegar menú" title="Plegar menú" style="appearance:none;-webkit-appearance:none;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);color:#fff;width:32px;height:32px;padding:0;border-radius:8px;cursor:pointer;flex-shrink:0">
                    <svg class="menu-fold__icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    <span class="nav-link__label">Dashboard</span>
                </a>
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" title="Clientes">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="nav-link__label">Clientes</span>
                </a>
                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" title="Facturas">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                    <span class="nav-link__label">Facturas</span>
                </a>
                <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}" title="Presupuestos">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
                    <span class="nav-link__label">Presupuestos</span>
                </a>
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" title="Configuración">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    <span class="nav-link__label">Configuración</span>
                </a>
                @if (auth()->user()->isDemoAdmin())
                <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.index', 'funnel.index') ? 'active' : '' }}" title="Admin">
                    <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                    <span class="nav-link__label">Admin</span>
                </a>
                @endif
            </nav>
            <div class="sidebar-footer">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-link sidebar-logout" title="Cerrar sesión">
                        <svg class="nav-link__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span class="nav-link__label">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>
        <div class="panel-content">
            <header class="panel-header">
                <h1>@yield('heading', 'Panel')</h1>
            </header>
            <main class="panel-main">
                @if (auth()->user()->isDemoAdmin())
                    <div class="alert alert-info">Catálogo de ejemplo: solo tu cuenta ve estos documentos de demostración. El SIF se genera sin envío a AEAT.</div>
                @endif
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
    @include('partials.bug-report')
    @include('layouts.partials.cookie-banner')
    <script>
    (function () {
        var btn = document.getElementById('sidebar-toggle');
        if (!btn || btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';
        var apply = function (collapsed) {
            document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
            var label = collapsed ? 'Mostrar menú' : 'Plegar menú';
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
            try { localStorage.setItem('pf-sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) {}
        };
        btn.addEventListener('click', function () {
            apply(!document.documentElement.classList.contains('sidebar-collapsed'));
        });
        apply(document.documentElement.classList.contains('sidebar-collapsed'));
    })();
    </script>
    @include('layouts.partials.app-js')
    @stack('scripts')
</body>
</html>
