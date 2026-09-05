<?php

namespace App\Http\Controllers;

use App\Mail\AccountDeletedMail;
use App\Models\AnalyticsEvent;
use App\Models\UserSifConfig;
use App\Services\AnalyticsService;
use App\Support\VerifactuProductionCheck;
use App\Support\VerifactuSchema;
use App\Services\DataExportService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $company = $user->currentCompany();
        $sif = VerifactuSchema::hasSifConfigTable() ? $company->sifConfig : null;

        return view('settings.index', [
            'user' => $user,
            'company' => $company,
            'companies' => $user->companies,
            'sif' => $sif,
            'verifactuAvailable' => VerifactuSchema::hasSifConfigTable(),
            'sandboxCheck' => $user->isDemoAdmin() ? VerifactuProductionCheck::run() : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $company = $user->currentCompany();

        $rules = [
            'business_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'province' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:30'],
            'iban' => ['nullable', 'string', 'max:34'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'vat_regime' => ['nullable', 'in:general,recargo,exento'],
            'default_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_recargo_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'quote_prefix' => ['required', 'string', 'max:20'],
            'default_due_days' => ['required', 'integer', 'min:1', 'max:365'],
            'invoice_footer' => ['nullable', 'string', 'max:2000'],
        ];

        if ($user->isPro()) {
            $rules['reminder_day_1'] = ['required', 'integer', 'min:1', 'max:90'];
            $rules['reminder_day_2'] = ['required', 'integer', 'min:1', 'max:90'];
            $rules['reminder_day_3'] = ['required', 'integer', 'min:1', 'max:90'];
            $rules['owner_reminder_day'] = ['required', 'integer', 'min:1', 'max:90'];
        }

        $data = $request->validate($rules, [
            'business_name.required' => 'El nombre comercial es obligatorio.',
            'tax_id.required' => 'El NIF/CIF es obligatorio.',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        $company->update([
            'legal_name' => $data['business_name'],
            'tax_id' => $data['tax_id'],
            'address' => $data['address'] ?? $company->address,
            'city' => $data['city'] ?? $company->city,
            'postal_code' => $data['postal_code'] ?? $company->postal_code,
            'province' => $data['province'] ?? $company->province,
            'country' => $data['country'] ?? $company->country ?? 'ES',
            'phone' => $data['phone'] ?? $company->phone,
            'iban' => $data['iban'] ?? $company->iban,
            'logo_path' => $data['logo_path'] ?? $company->logo_path,
            'vat_regime' => $data['vat_regime'] ?? $company->vat_regime,
            'default_vat_rate' => $data['default_vat_rate'],
            'default_irpf_rate' => $data['default_irpf_rate'] ?? $company->default_irpf_rate,
            'default_recargo_rate' => $data['default_recargo_rate'] ?? $company->default_recargo_rate,
            'invoice_prefix' => $data['invoice_prefix'],
            'quote_prefix' => $data['quote_prefix'],
            'default_due_days' => $data['default_due_days'],
            'invoice_footer' => $data['invoice_footer'] ?? $company->invoice_footer,
        ]);
        $company->syncLegacyUserFields();

        $user->update(array_intersect_key($data, array_flip([
            'reminder_day_1', 'reminder_day_2', 'reminder_day_3', 'owner_reminder_day',
        ])));

        return redirect()->route('settings.index')->with('status', 'Configuración guardada.');
    }

    public function updateVerifactu(Request $request): RedirectResponse
    {
        if (! VerifactuSchema::hasSifConfigTable()) {
            return back()->with('error', 'Veri*Factu no está disponible. Ejecuta php artisan migrate.');
        }

        $user = auth()->user();
        $company = $user->currentCompany();

        $data = $request->validate([
            'verifactu_enabled' => ['nullable', 'boolean'],
            'verifactu_mode' => ['required', 'in:verifactu,no_verifactu'],
            'cert_file' => ['nullable', 'file', 'max:5120', 'extensions:p12,pfx'],
            'cert_password' => ['nullable', 'string', 'max:255'],
        ], [
            'cert_password.required_with' => 'Indica la contraseña del certificado.',
        ]);

        $company->ensureSifConfig();
        $config = $company->sifConfig ?? new UserSifConfig(['user_id' => $user->id, 'company_id' => $company->id]);
        $wasEnabled = (bool) $config->enabled;
        $config->user_id = $user->id;
        $config->company_id = $company->id;
        $config->enabled = $request->boolean('verifactu_enabled');
        $config->mode = $data['verifactu_mode'];

        if ($request->hasFile('cert_file') && ! filled($data['cert_password'] ?? null)) {
            return back()->withErrors(['cert_password' => 'Indica la contraseña del certificado.']);
        }

        if ($request->hasFile('cert_file') && filled($data['cert_password'])) {
            $p12Content = file_get_contents($request->file('cert_file')->getRealPath());
            $certs = [];

            if (! openssl_pkcs12_read($p12Content, $certs, $data['cert_password'])) {
                return back()->withErrors(['cert_password' => 'Contraseña incorrecta o certificado inválido.']);
            }

            $certInfo = openssl_x509_parse($certs['cert']);
            $expiresAt = isset($certInfo['validTo_time_t'])
                ? \Carbon\Carbon::createFromTimestamp($certInfo['validTo_time_t'])
                : null;

            if ($expiresAt?->isPast()) {
                return back()->withErrors(['cert_file' => 'El certificado está caducado. Sube uno vigente.']);
            }

            if ($config->cert_path && Storage::disk('local')->exists($config->cert_path)) {
                Storage::disk('local')->delete($config->cert_path);
            }

            $path = 'sif/certs/company_'.$company->id.'.p12.enc';
            Storage::disk('local')->put($path, encrypt($p12Content));
            $config->cert_path = $path;
            $config->cert_expires_at = $expiresAt;
            $config->is_dev_cert = false;
        }

        if (filled($data['cert_password'] ?? null)) {
            $config->storeCertPassword($data['cert_password']);
        } elseif ($config->enabled && $config->hasValidCertificate() && ! filled($config->certPassword())) {
            return back()->withErrors([
                'cert_password' => 'Indica la contraseña del certificado para poder enviar a la AEAT.',
            ]);
        }

        $config->save();

        if ($config->enabled && ! $wasEnabled) {
            app(AnalyticsService::class)->record(AnalyticsEvent::VERIFACTU_ENABLED);
        }

        return redirect()->route('settings.index')->with('status', 'Configuración Veri*Factu guardada.');
    }

    public function export(Request $request, DataExportService $exporter): RedirectResponse|BinaryFileResponse
    {
        $user = $request->user();
        $cacheKey = 'data-export:user:'.$user->id;

        if (Cache::has($cacheKey)) {
            return redirect()->route('settings.index')->with('error', 'Solo puedes exportar tus datos una vez cada 24 horas.');
        }

        try {
            $zipPath = $exporter->createZip($user);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('settings.index')->with('error', 'No se pudo preparar la exportación. Inténtalo más tarde.');
        }

        Cache::put($cacheKey, true, now()->addDay());

        Log::info('RGPD data export', [
            'user_id' => $user->id,
        ]);

        return response()->download($zipPath, $exporter->filename(), [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function destroy(Request $request, StripeService $stripe): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'confirmation' => ['required', 'string'],
        ], [
            'confirmation.required' => 'Debes confirmar escribiendo tu email o ELIMINAR.',
        ]);

        $confirmation = trim($request->confirmation);
        if ($confirmation !== $user->email && strtoupper($confirmation) !== 'ELIMINAR') {
            return back()->withErrors([
                'confirmation' => 'Escribe tu email o ELIMINAR para confirmar.',
            ]);
        }

        $email = $user->email;
        $name = $user->name;

        if ($user->stripe_subscription_id) {
            try {
                $stripe->cancelSubscription($user);
            } catch (\Throwable) {
                // la baja RGPD no debe bloquearse por un fallo de Stripe
            }
        }

        if ($user->hasFiscalRecords()) {
            DB::transaction(function () use ($user): void {
                $user->forceFill([
                    'name' => 'Cuenta eliminada',
                    'email' => 'deleted-'.$user->id.'@invalid.local',
                    'password' => str()->password(32),
                    'google_id' => null,
                    'remember_token' => null,
                    'onboarding_completed_at' => $user->onboarding_completed_at,
                ])->save();
                $user->companies()->update(['is_active' => false, 'email' => null, 'phone' => null]);
            });
        } else {
            if ($user->logo_path) {
                Storage::disk('public')->delete($user->logo_path);
            }

            DB::transaction(function () use ($user): void {
                $user->documents()->whereDoesntHave('billingRecords')->delete();
                $user->clients()->delete();
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
                $user->delete();
            });
        }

        try {
            Mail::to($email)->send(new AccountDeletedMail($name));
        } catch (\Throwable) {
            // no bloquear la baja si el email falla
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('status', 'Cuenta eliminada.');
    }
}
