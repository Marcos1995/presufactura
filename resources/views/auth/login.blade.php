@extends('layouts.guest')

@section('title', 'Iniciar sesión — ' . config('app.name'))

@section('content')
<div class="auth-card">
    <h1>Iniciar sesión</h1>

    <form method="POST" action="{{ route('login') }}" class="form">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
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

        <p class="auth-switch"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>

        <div class="form-group form-check">
            <label>
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Recordarme
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
    </form>

    <p class="auth-switch">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
</div>
@endsection
