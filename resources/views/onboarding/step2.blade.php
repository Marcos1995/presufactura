@extends('layouts.guest')

@section('title', 'Configuración — Paso 2 — ' . config('app.name'))

@section('content')
<div class="onboarding-card">
    <div class="onboarding-steps">
        <span class="step done">1. Datos fiscales</span>
        <span class="step active">2. Cobro</span>
        <span class="step">3. Preferencias</span>
    </div>

    <h1>Datos de cobro</h1>
    <p class="text-muted">Tu IBAN aparecerá en las facturas para que tus clientes puedan pagarte.</p>

    <form method="POST" action="{{ route('onboarding.store', 2) }}" class="form" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="iban">IBAN *</label>
            <input type="text" id="iban" name="iban" value="{{ old('iban', $user->iban) }}" placeholder="ES00 0000 0000 0000 0000 0000" required>
            @error('iban')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="logo">Logo (opcional)</label>
            <input type="file" id="logo" name="logo" accept="image/*">
            @error('logo')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Continuar</button>
    </form>
</div>
@endsection
