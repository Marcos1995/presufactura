@extends('layouts.guest')

@section('title', 'Registro — ' . config('app.name'))

@section('content')
<div class="auth-card">
    <h1>Crear cuenta</h1>

    <form method="POST" action="{{ route('register') }}" class="form">
        @csrf

        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
    </form>

    <p class="auth-switch">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</div>
@endsection
