@extends('layouts.panel')

@section('title', 'Configuración — ' . config('app.name'))
@section('heading', 'Configuración')

@section('content')
@php $company = $company ?? $user->currentCompany(); @endphp
@if (auth()->user()->isDemoAdmin())
    <p class="text-muted"><a href="{{ route('funnel.index') }}">Embudo de conversión (30 días)</a></p>
@endif
<div class="card card-narrow">
    <form method="POST" action="{{ route('settings.update') }}" class="form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h2 class="form-section-title">Datos fiscales{{ $company ? ' — '.$company->legal_name : '' }}</h2>

        <div class="form-group">
            <label for="business_name">Nombre comercial *</label>
            <input type="text" id="business_name" name="business_name" value="{{ old('business_name', $company->legal_name ?? $user->business_name) }}" required>
            @error('business_name')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="tax_id">NIF/CIF *</label>
            <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id', $company->tax_id ?? $user->tax_id) }}" required>
            @error('tax_id')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="address">Dirección</label>
            <textarea id="address" name="address" rows="2">{{ old('address', $company->address ?? $user->address) }}</textarea>
            @error('address')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" value="{{ old('city', $company->city ?? $user->city) }}">
                @error('city')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="postal_code">Código postal</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $company->postal_code ?? $user->postal_code) }}">
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
            <input type="text" id="iban" name="iban" value="{{ old('iban', $company->iban ?? $user->iban) }}" placeholder="ES00 0000 0000 0000 0000 0000">
            @error('iban')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="province">Provincia</label>
                <input type="text" id="province" name="province" value="{{ old('province', $company->province ?? '') }}">
            </div>
            <div class="form-group">
                <label for="country">País</label>
                <input type="text" id="country" name="country" maxlength="2" value="{{ old('country', $company->country ?? 'ES') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="logo">Logo</label>
            @if ($company->logo_path ?? $user->logo_path)
                <div class="logo-preview">
                    <img src="{{ asset('storage/'.($company->logo_path ?? $user->logo_path)) }}" alt="Logo" height="48">
                </div>
            @endif
            <input type="file" id="logo" name="logo" accept="image/*">
            @error('logo')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <h2 class="form-section-title">Preferencias de documentos</h2>

        <div class="form-row">
            <div class="form-group">
                <label for="default_vat_rate">IVA por defecto (%)</label>
                <input type="number" id="default_vat_rate" name="default_vat_rate" step="0.01" min="0" max="100" value="{{ old('default_vat_rate', $company->default_vat_rate ?? $user->default_vat_rate) }}" required>
                @error('default_vat_rate')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="default_due_days">Días vencimiento factura</label>
                <input type="number" id="default_due_days" name="default_due_days" min="1" max="365" value="{{ old('default_due_days', $company->default_due_days ?? $user->default_due_days) }}" required>
                @error('default_due_days')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="invoice_prefix">Prefijo facturas</label>
                <input type="text" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $company->invoice_prefix ?? $user->invoice_prefix) }}" required>
                @error('invoice_prefix')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="quote_prefix">Prefijo presupuestos</label>
                <input type="text" id="quote_prefix" name="quote_prefix" value="{{ old('quote_prefix', $company->quote_prefix ?? $user->quote_prefix) }}" required>
                @error('quote_prefix')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="vat_regime">Régimen IVA</label>
                <select id="vat_regime" name="vat_regime">
                    <option value="general" @selected(old('vat_regime', $company->vat_regime ?? 'general') === 'general')>General</option>
                    <option value="recargo" @selected(old('vat_regime', $company->vat_regime ?? '') === 'recargo')>Recargo de equivalencia</option>
                    <option value="exento" @selected(old('vat_regime', $company->vat_regime ?? '') === 'exento')>Exento</option>
                </select>
            </div>
            <div class="form-group">
                <label for="default_irpf_rate">IRPF por defecto (%)</label>
                <input type="number" id="default_irpf_rate" name="default_irpf_rate" step="0.01" min="0" max="100" value="{{ old('default_irpf_rate', $company->default_irpf_rate ?? 0) }}">
            </div>
            <div class="form-group">
                <label for="default_recargo_rate">Recargo por defecto (%)</label>
                <input type="number" id="default_recargo_rate" name="default_recargo_rate" step="0.01" min="0" max="100" value="{{ old('default_recargo_rate', $company->default_recargo_rate ?? 0) }}">
            </div>
        </div>

        <div class="form-group">
            <label for="invoice_footer">Pie de factura</label>
            <textarea id="invoice_footer" name="invoice_footer" rows="2">{{ old('invoice_footer', $company->invoice_footer ?? '') }}</textarea>
        </div>

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

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar configuración</button>
        </div>
    </form>
</div>

<div class="card card-narrow">
    <form method="POST" action="{{ route('companies.store') }}" class="form">
        @csrf
        <h2 class="form-section-title">Nueva empresa emisora</h2>
        <p class="text-muted">Puedes facturar con varias empresas. Tras crearla, el panel usará esa empresa.</p>
        <div class="form-group">
            <label for="new_legal_name">Razón social *</label>
            <input type="text" id="new_legal_name" name="legal_name" required>
        </div>
        <div class="form-group">
            <label for="new_tax_id">NIF/CIF *</label>
            <input type="text" id="new_tax_id" name="tax_id" required>
        </div>
        <input type="hidden" name="country" value="ES">
        <input type="hidden" name="vat_regime" value="general">
        <input type="hidden" name="default_vat_rate" value="21">
        <input type="hidden" name="default_due_days" value="30">
        <input type="hidden" name="invoice_prefix" value="FAC">
        <input type="hidden" name="quote_prefix" value="PRE">
        <div class="form-group">
            <label>
                <input type="checkbox" name="accept_terms" value="1">
                Acepto los <a href="{{ route('legal.terminos') }}" target="_blank" rel="noopener">términos de uso</a>
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-secondary">Crear empresa</button>
        </div>
    </form>
</div>

<div class="card card-narrow">
    @if ($verifactuAvailable ?? false)
    <form method="POST" action="{{ route('settings.verifactu.update') }}" class="form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h2 class="form-section-title">Veri*Factu</h2>
        <p class="text-muted">Va activado al crear la empresa. El QR tributario aparece cuando existe un registro SIF de alta. El envío SOAP a Hacienda requiere tu certificado FNMT (.p12): el mismo archivo vale en pruebas y en real; solo cambia <code>VERIFACTU_ENV</code>. Sin certificado el PDF sigue siendo proforma.</p>
        <p>
            <strong>Entorno AEAT:</strong>
            <span class="badge {{ \App\Support\VerifactuEnv::badgeClass() }}">{{ \App\Support\VerifactuEnv::labelFor($sif ?? null) }}</span>
            @if ($sif?->is_dev_cert)
                <span class="text-muted">— facturas fiscales de prueba en este usuario, sin envío a Hacienda.</span>
            @elseif (\App\Support\VerifactuEnv::isPreprod())
                <span class="text-muted">— usa certificado de pruebas. Guía: docs/VERIFACTU-ENTORNO-PRUEBAS.md</span>
            @endif
        </p>

        @if ($sif)
            <p>
                <strong>Estado certificado:</strong>
                <span class="badge {{ $sif->certificateStatusBadgeClass() }}">{{ $sif->certificateStatusLabel() }}</span>
                @if ($sif->cert_expires_at)
                    — caduca {{ $sif->cert_expires_at->format('d/m/Y') }}
                @endif
            </p>
            @if ($sif->certificateStatus() === 'expiring')
                <p class="form-error">Tu certificado caduca en menos de 30 días. Renueva el .p12 antes de que expire.</p>
            @endif
        @endif

        @if ($sif?->is_dev_cert)
            <p class="alert alert-info">Certificado de desarrollo (autofirmado). Sirve para XML, hash y QR en el panel. Hacienda no lo acepta: no existe un .p12 público de AEAT. Para preprod real sube tu certificado FNMT de pruebas.</p>
        @endif

        @if ($sandboxCheck)
            @if ($sandboxCheck['ok'])
                <p class="alert alert-success">Prueba Veri*Factu en este servidor: OK. Abre la factura DEMO-F-FIS: debe verse «Aceptada (pruebas)». Hacienda no recibe nada.</p>
            @else
                <p class="alert alert-warning">Sandbox incompleto. En SSH: <code>php artisan presufactura:prepare-test-user</code> y luego <code>php artisan presufactura:verifactu-check</code>.</p>
            @endif
        @endif

        @if ($sif?->enabled && ! $sif?->hasValidCertificate())
            <p class="form-error">Veri*Factu está activo pero falta un certificado válido. Sube un .p12 vigente para enviar a AEAT.</p>
        @endif

        <div class="form-group">
            <label>
                <input type="checkbox" name="verifactu_enabled" value="1" {{ old('verifactu_enabled', $sif?->enabled) ? 'checked' : '' }}>
                Activar Veri*Factu
            </label>
        </div>

        <div class="form-group">
            <label for="verifactu_mode">Modalidad</label>
            <select id="verifactu_mode" name="verifactu_mode">
                <option value="verifactu" {{ old('verifactu_mode', $sif?->mode ?? 'verifactu') === 'verifactu' ? 'selected' : '' }}>VERI*FACTU (envío AEAT)</option>
                <option value="no_verifactu" {{ old('verifactu_mode', $sif?->mode) === 'no_verifactu' ? 'selected' : '' }}>NO VERI*FACTU (conservación)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="cert_file">Certificado .p12</label>
            <input type="file" id="cert_file" name="cert_file" accept=".p12,.pfx">
            @if ($sif?->cert_path)
                <p class="text-muted">Certificado cargado. Sube uno nuevo para reemplazarlo.</p>
            @endif
            @error('cert_file')<span class="form-error">{{ $message }}</span>@enderror
            @error('cert_password')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="cert_password">Contraseña del certificado</label>
            <input type="password" id="cert_password" name="cert_password" autocomplete="new-password">
            <p class="text-muted">Se guarda cifrada para el envío a AEAT. Vuelve a indicarla si activas Veri*Factu en otro dispositivo.</p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Veri*Factu</button>
        </div>
    </form>
    @else
    <h2 class="form-section-title">Veri*Factu</h2>
    <p class="text-muted">Faltan las tablas en la base de datos. En Hostinger ábrelo por SSH (PuTTY o Terminal de hPanel), no desde el administrador de archivos:</p>
    <pre class="settings-cli">cd ~/domains/presufactura.es/public_html/laravel
php artisan migrate --force</pre>
    <p class="text-muted">Si el código no está al día, usa <code>./deploy.sh</code> (hace git pull y migrate). Luego recarga esta página y sube tu certificado .p12.</p>
    @endif
</div>

<div class="card card-narrow settings-export-box">
    <h2 class="form-section-title">Tus datos (RGPD)</h2>
    <p>Descarga una copia de tu perfil, clientes y documentos en JSON (sin PDFs). Máximo una exportación cada 24 horas.</p>
    <form method="POST" action="{{ route('settings.export') }}" id="export-data-form">
        @csrf
        <button type="submit" class="btn btn-secondary" id="export-data-btn">Descargar mis datos</button>
        <p class="text-muted" id="export-status" hidden>Preparando exportación…</p>
    </form>
</div>

<div class="card card-narrow danger-zone">
    <h2 class="form-section-title">Zona peligrosa</h2>
    <p>Eliminar tu cuenta borra de forma <strong>inmediata</strong> todos tus datos: clientes, documentos, recordatorios y configuración fiscal. Esta acción es irreversible.</p>
    <p class="text-muted">Según nuestra política de privacidad, también puedes solicitar la baja por email; desde aquí la eliminación es al instante.</p>
    <button type="button" class="btn btn-danger" id="delete-account-open">Eliminar mi cuenta</button>
</div>

<div class="modal-overlay" id="delete-account-modal" style="display:none">
    <div class="modal-card">
        <h2>Eliminar cuenta</h2>
        <p>Se eliminarán clientes, documentos y configuración fiscal. Escribe tu email (<strong>{{ $user->email }}</strong>) o <strong>ELIMINAR</strong> para confirmar.</p>
        <form method="POST" action="{{ route('settings.destroy') }}" id="delete-account-form" class="form">
            @csrf
            <div class="form-group">
                <label for="confirmation">Confirmación</label>
                <input type="text" id="confirmation" name="confirmation" value="{{ old('confirmation') }}" autocomplete="off" required>
                @error('confirmation')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="delete-account-cancel">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    @if ($errors->has('confirmation'))
        $('#delete-account-modal').show();
    @endif

    $('#delete-account-open').on('click', function() {
        $('#delete-account-modal').show();
        $('#confirmation').trigger('focus');
    });

    $('#delete-account-cancel, #delete-account-modal').on('click', function(e) {
        if (e.target === this) {
            $('#delete-account-modal').hide();
        }
    });

    $('#export-data-form').on('submit', function() {
        $('#export-data-btn').prop('disabled', true);
        $('#export-status').prop('hidden', false);
    });
});
</script>
@endpush
