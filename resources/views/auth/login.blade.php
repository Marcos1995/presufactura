@extends('layouts.auth-split')

@section('title', 'Iniciar sesión — ' . config('app.name'))
@section('pitch', 'Entra y factura.')

@section('content')
<h2 class="font-bold text-2xl sm:text-3xl tracking-tight mb-2">Iniciar sesión</h2>
<p class="text-sm text-gray-600 mb-6">Accede a tu panel de presupuestos y facturación.</p>

@include('auth._google-button')

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-xs font-bold tracking-wider uppercase mb-1.5" for="email">Correo electrónico</label>
        <input class="w-full px-3.5 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0A3D22]" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        @error('email')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-bold tracking-wider uppercase" for="password">Contraseña</label>
            <a class="text-sm text-[#0A3D22] font-semibold hover:underline" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </div>
        <input class="w-full px-3.5 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0A3D22]" id="password" name="password" type="password" required>
        @error('password')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
        Recordarme
    </label>
    <button class="w-full bg-[#0A3D22] hover:bg-[#072d19] text-white py-3.5 rounded-xl text-sm font-bold" type="submit">Entrar</button>
</form>
<p class="mt-8 text-sm text-center">¿No tienes cuenta? <a class="text-[#0A3D22] font-bold hover:underline" href="{{ route('register') }}">Regístrate</a></p>
@endsection
