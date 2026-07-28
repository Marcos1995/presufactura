@extends('layouts.guest')

@section('title', 'Restablecer contraseña — ' . config('app.name'))

@section('content')
<div class="auth-card">
    <h1>Restablecer contraseña</h1>
    <p>Elige una nueva contraseña para tu cuenta.</p>

    <form method="POST" action="{{ route('password.update', ['token' => $token]) }}" class="form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
            @error('email')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password" required>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Restablecer contraseña</button>
    </form>

    <p class="auth-switch"><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
</div>
@endsection
