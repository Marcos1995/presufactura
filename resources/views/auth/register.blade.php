@extends('layouts.auth-split')

@section('title', 'Registro — ' . config('app.name'))
@section('pitch', 'Empieza a facturar.')

@section('content')
<h2 class="font-bold text-2xl sm:text-3xl tracking-tight mb-2">Crear cuenta</h2>
<p class="text-sm text-gray-600 mb-6">Gratis, sin tarjeta y sin límites.</p>

@include('auth._google-button', ['analyticsEvent' => 'signup_cta_click'])

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf
    <div>
        <label class="block text-xs font-bold tracking-wider uppercase mb-1.5" for="name">Nombre</label>
        <input class="w-full px-3.5 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0A3D22]" id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
        @error('name')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <div>
        <label class="block text-xs font-bold tracking-wider uppercase mb-1.5" for="email">Correo electrónico</label>
        <input class="w-full px-3.5 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0A3D22]" id="email" name="email" type="email" value="{{ old('email') }}" required>
        @error('email')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <div>
        <label class="block text-xs font-bold tracking-wider uppercase mb-1.5" for="password">Contraseña</label>
        <input class="w-full px-3.5 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0A3D22]" id="password" name="password" type="password" required>
        @error('password')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <div>
        <label class="block text-xs font-bold tracking-wider uppercase mb-1.5" for="password_confirmation">Confirmar contraseña</label>
        <input class="w-full px-3.5 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#0A3D22]" id="password_confirmation" name="password_confirmation" type="password" required>
        @error('password_confirmation')<span class="form-error">{{ $message }}</span>@enderror
    </div>
    <button class="w-full bg-[#0A3D22] hover:bg-[#072d19] text-white py-3.5 rounded-xl text-sm font-bold" type="submit" data-analytics="signup_cta_click">Crear cuenta gratis</button>
</form>
<p class="mt-8 text-sm text-center">¿Ya tienes cuenta? <a class="text-[#0A3D22] font-bold hover:underline" href="{{ route('login') }}">Inicia sesión</a></p>
@endsection
