@extends('layouts.guest')

@section('title', 'Configuración — Paso 1 — ' . config('app.name'))

@section('content')
<div class="onboarding-card">
    <div class="onboarding-steps">
        <span class="step active">1. Datos fiscales</span>
        <span class="step">2. Cobro</span>
        <span class="step">3. Preferencias</span>
    </div>

    <h1>Bienvenido a PresuFactura</h1>
    <p class="text-muted">Configura tus datos fiscales para empezar a facturar.</p>

    <form method="POST" action="{{ route('onboarding.store', 1) }}" class="form">
        @csrf

        <div class="form-group">
            <label for="business_name">Nombre comercial *</label>
            <input type="text" id="business_name" name="business_name" value="{{ old('business_name', $user->business_name) }}" required>
            @error('business_name')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="tax_id">NIF/CIF *</label>
            <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id', $user->tax_id) }}" required>
            @error('tax_id')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="address">Dirección *</label>
            <textarea id="address" name="address" rows="2" required>{{ old('address', $user->address) }}</textarea>
            @error('address')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="city">Ciudad *</label>
                <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" required>
                @error('city')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="postal_code">Código postal *</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" required>
                @error('postal_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
            @error('phone')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">Continuar</button>
    </form>
</div>
@endsection
