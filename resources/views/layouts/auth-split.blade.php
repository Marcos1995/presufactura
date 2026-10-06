<!DOCTYPE html>
<html class="h-full" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    @include('layouts.partials.favicon')
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.theme-boot')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: "Public Sans", sans-serif; }
        :focus-visible { outline: 2px solid #0A3D22; outline-offset: 2px; }
        .theme-switch { position: relative; width: 3.25rem; height: 1.75rem; border: 0; border-radius: 999px; background: #032612; padding: 0; cursor: pointer; }
        .theme-switch__thumb { position: absolute; top: 3px; left: 3px; width: 1.35rem; height: 1.35rem; border-radius: 999px; background: #18E667; transition: transform .2s ease; }
        html[data-theme="dark"] .theme-switch__thumb { transform: translateX(1.45rem); }
        .theme-switch svg { position: absolute; top: 5px; width: 14px; height: 14px; }
        .theme-switch .sun { left: 6px; color: #032612; }
        .theme-switch .moon { right: 6px; color: #18E667; }
        html[data-theme="dark"] body { background: #07140d; }
        html[data-theme="dark"] .auth-form { background: #10281c !important; color: #e8f7ee; }
        html[data-theme="dark"] .auth-form h2, html[data-theme="dark"] .auth-form label { color: #e8f7ee; }
        .form-error { color: #C2410C; font-size: .8rem; display: block; margin-top: .25rem; }
        .alert { border-radius: 12px; padding: .75rem 1rem; margin-bottom: 1rem; font-size: .9rem; }
        .alert-success { background: #E7F9EE; color: #032612; }
        .alert-danger { background: #fee2e2; color: #7f1d1d; }
        .btn-google { width: 100%; display: flex; align-items: center; justify-content: center; gap: .75rem; background: #fff; border: 1px solid #d1d5db; color: #374151; font-weight: 600; font-size: .875rem; padding: .75rem 1rem; border-radius: 12px; text-decoration: none; }
        .btn-google__icon { width: 1rem; height: 1rem; }
        .btn { display: inline-flex; border-radius: 12px; padding: .4rem .75rem; font-weight: 700; font-size: .8rem; cursor: pointer; }
        .btn-primary { background: #0A3D22; color: #fff; border: 0; }
        .btn-secondary { background: #fff; color: #032612; border: 1px solid #d6d3d1; }
        .cookie-banner { position: fixed; inset: auto 0 0 0; z-index: 80; background: #fff; border-top: 1px solid #e7e5e4; padding: 1rem; }
        .cookie-banner[hidden] { display: none; }
        .cookie-banner-inner { max-width: 72rem; margin: 0 auto; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; }
        .cookie-banner p { margin: 0; }
        .cookie-banner-actions { display: flex; gap: .5rem; }
        .auth-divider { display: flex; align-items: center; gap: .75rem; margin: 1.5rem 0; color: #6b7280; font-size: .75rem; }
        .auth-divider::before, .auth-divider::after { content: ""; flex: 1; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body class="min-h-full bg-[#faf9f5] text-[#111827] antialiased">
<div class="w-full min-h-screen flex flex-col lg:flex-row">
    <div class="w-full lg:w-1/2 bg-[#18E667] text-[#032612] flex flex-col justify-between p-8 sm:p-12 lg:p-16">
        <div class="flex items-center justify-between gap-4">
            <p class="text-xs font-bold tracking-wider uppercase">Acceso autónomos · España</p>
            @include('layouts.partials.theme-switch')
        </div>
        <div class="my-10 lg:my-0">
            <h1 class="font-black text-4xl sm:text-5xl lg:text-6xl tracking-tight leading-[1.05]">@yield('pitch', 'Entra y factura.')</h1>
            <p class="font-black text-6xl sm:text-7xl tracking-tighter leading-none mt-6">0 €</p>
            <p class="font-extrabold text-lg sm:text-2xl mt-2">Sin tarjeta. Sin límites. Veri*Factu opcional.</p>
        </div>
        <p class="text-sm sm:text-base font-medium max-w-md border-t border-[#032612]/15 pt-6">Tus presupuestos, facturas y cobros siempre a mano, en cualquier dispositivo.</p>
    </div>
    <div class="auth-form w-full lg:w-1/2 bg-[#faf9f5] flex flex-col justify-center p-8 sm:p-12 lg:p-16">
        <div class="w-full max-w-md mx-auto">
            <a class="inline-flex items-center font-extrabold text-xl tracking-tight mb-8" href="{{ route('landing') }}">PresuFactura</a>
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
@include('layouts.partials.cookie-banner')
@include('layouts.partials.theme-toggle-script')
</body>
</html>
