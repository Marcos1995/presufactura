@extends('layouts.guest')

@section('title', 'Recuperar contraseña — ' . config('app.name'))

@section('content')
<div class="auth-card">
    <h1>Recuperar contraseña</h1>
    <p>Introduce tu email y te enviaremos un enlace para restablecer la contraseña.</p>

    <form method="POST" action="{{ route('password.email') }}" class="form">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Enviar enlace</button>
    </form>

    <p class="auth-switch"><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
</div>
@endsection
