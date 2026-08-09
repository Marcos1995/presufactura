@extends('layouts.guest')

@section('title', 'Configuración — Paso 3 — ' . config('app.name'))

@section('content')
<div class="onboarding-card">
    <div class="onboarding-steps">
        <span class="step done">1. Datos fiscales</span>
        <span class="step done">2. Cobro</span>
        <span class="step active">3. Preferencias</span>
    </div>

    <h1>Preferencias</h1>
    <p class="text-muted">IVA, prefijos y recordatorios. Puedes cambiarlos después en Configuración.</p>

    <form method="POST" action="{{ route('onboarding.store', 3) }}" class="form">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="default_vat_rate">IVA por defecto (%)</label>
                <input type="number" id="default_vat_rate" name="default_vat_rate" step="0.01" min="0" max="100" value="{{ old('default_vat_rate', $user->default_vat_rate) }}" required>
                @error('default_vat_rate')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="default_due_days">Días vencimiento</label>
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

        <div class="onboarding-reminders">
            <h2 class="form-section-title">Recordatorios automáticos</h2>
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
        </div>

        <button type="submit" class="btn btn-primary btn-block">Finalizar configuración</button>
    </form>
</div>
@endsection
