@extends('layouts.guest')

@section('title', 'Verificar email — ' . config('app.name'))

@section('content')
<div class="auth-card">
    <h1>Revisa tu bandeja</h1>
    <p>Te hemos enviado un enlace de verificación a <strong>{{ auth()->user()->email }}</strong>. Haz clic en el enlace para activar tu cuenta.</p>
    <p class="text-muted">Si no lo ves, revisa la carpeta de spam.</p>

    <form method="POST" action="{{ route('verification.send') }}" class="form">
        @csrf
        <button type="submit" class="btn btn-primary btn-block">Reenviar email de verificación</button>
    </form>

    <p class="auth-switch">
        <form method="POST" action="{{ route('logout') }}" class="inline-form">
            @csrf
            <button type="submit" class="btn-link">Cerrar sesión</button>
        </form>
    </p>
</div>
@endsection
