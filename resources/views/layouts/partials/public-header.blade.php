@php
    $loggedIn = auth()->check();
@endphp
<header class="landing-header">
    <div class="landing-header-inner">
        @include('layouts.partials.logo', ['href' => route('landing')])
        <button type="button" class="nav-toggle" aria-label="Menú" aria-expanded="false">☰</button>
        <nav class="landing-nav" aria-label="Principal">
            <a href="{{ route('tools.verifactu') }}">Asistente Veri*Factu</a>
            <a href="{{ route('tools.cartas') }}">Cartas Hacienda</a>
            <a href="{{ route('guides.index') }}">Guías</a>
            <a href="{{ route('pricing') }}">Precios</a>
            <a href="{{ route('help') }}">Ayuda</a>
            @include('layouts.partials.theme-switch')
            @if ($loggedIn)
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al panel</a>
            @else
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Empezar gratis</a>
            @endif
        </nav>
    </div>
</header>
