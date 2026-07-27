@extends('layouts.panel')

@section('title', 'Configuración — ' . config('app.name'))
@section('heading', 'Configuración')

@section('content')
<div class="card card-narrow">
    <form method="POST" action="{{ route('settings.update') }}" class="form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h2 class="form-section-title">Datos fiscales</h2>

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
            <label for="address">Dirección</label>
            <textarea id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
            @error('address')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}">
                @error('city')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="postal_code">Código postal</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
                @error('postal_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
            @error('phone')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="iban">IBAN</label>
            <input type="text" id="iban" name="iban" value="{{ old('iban', $user->iban) }}" placeholder="ES00 0000 0000 0000 0000 0000">
            @error('iban')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="logo">Logo</label>
            @if ($user->logo_path)
                <div class="logo-preview">
                    <img src="{{ asset('storage/'.$user->logo_path) }}" alt="Logo" height="48">
                </div>
            @endif
            <input type="file" id="logo" name="logo" accept="image/*">
            @error('logo')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <h2 class="form-section-title">Preferencias de documentos</h2>

        <div class="form-row">
            <div class="form-group">
                <label for="default_vat_rate">IVA por defecto (%)</label>
                <input type="number" id="default_vat_rate" name="default_vat_rate" step="0.01" min="0" max="100" value="{{ old('default_vat_rate', $user->default_vat_rate) }}" required>
                @error('default_vat_rate')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="default_due_days">Días vencimiento factura</label>
                <input type="number" id="default_due_days" name="default_due_days" min="1" max="365" value="{{ old('default_due_days', $user->default_due_days) }}" required>
                @error('default_due_days')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="invoice_prefix">Prefijo facturas</label>
                <input type="text" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $user->invoice_prefix) }}" required>
                @error('invoice_prefix')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="quote_prefix">Prefijo presupuestos</label>
                <input type="text" id="quote_prefix" name="quote_prefix" value="{{ old('quote_prefix', $user->quote_prefix) }}" required>
                @error('quote_prefix')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <h2 class="form-section-title">Recordatorios (plan Pro)</h2>

        <div class="form-row form-row-4">
            <div class="form-group">
                <label for="reminder_day_1">Cliente día +</label>
                <input type="number" id="reminder_day_1" name="reminder_day_1" min="1" max="90" value="{{ old('reminder_day_1', $user->reminder_day_1) }}" required>
                @error('reminder_day_1')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="reminder_day_2">Cliente día +</label>
                <input type="number" id="reminder_day_2" name="reminder_day_2" min="1" max="90" value="{{ old('reminder_day_2', $user->reminder_day_2) }}" required>
                @error('reminder_day_2')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="reminder_day_3">Cliente día +</label>
                <input type="number" id="reminder_day_3" name="reminder_day_3" min="1" max="90" value="{{ old('reminder_day_3', $user->reminder_day_3) }}" required>
                @error('reminder_day_3')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="owner_reminder_day">Autónomo día +</label>
                <input type="number" id="owner_reminder_day" name="owner_reminder_day" min="1" max="90" value="{{ old('owner_reminder_day', $user->owner_reminder_day) }}" required>
                @error('owner_reminder_day')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="settings-plan-box">
            <strong>Plan:</strong> {{ $user->isPro() ? 'Pro' : 'Free (3 docs/mes)' }}
            <a href="{{ route('subscription.index') }}" class="btn btn-secondary btn-sm">Gestionar suscripción</a>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar configuración</button>
        </div>
    </form>
</div>
@endsection
