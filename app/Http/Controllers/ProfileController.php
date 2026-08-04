<?php

namespace App\Http\Controllers;

use App\Mail\AccountDeletedMail;
use App\Models\UserSifConfig;
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
        $user->load('sifConfig');

        return view('settings.index', ['user' => $user]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'iban' => ['nullable', 'string', 'max:34'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'default_vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'quote_prefix' => ['required', 'string', 'max:20'],
            'default_due_days' => ['required', 'integer', 'min:1', 'max:365'],
            'reminder_day_1' => ['required', 'integer', 'min:1', 'max:90'],
            'reminder_day_2' => ['required', 'integer', 'min:1', 'max:90'],
            'reminder_day_3' => ['required', 'integer', 'min:1', 'max:90'],
            'owner_reminder_day' => ['required', 'integer', 'min:1', 'max:90'],
        ], [
            'business_name.required' => 'El nombre comercial es obligatorio.',
            'tax_id.required' => 'El NIF/CIF es obligatorio.',
        ]);

        if ($request->hasFile('logo')) {
            if ($user->logo_path) {
                Storage::disk('public')->delete($user->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        $user->update($data);

        return redirect()->route('settings.index')->with('status', 'Configuración guardada.');
    }

    public function updateVerifactu(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'verifactu_enabled' => ['nullable', 'boolean'],
            'verifactu_mode' => ['required', 'in:verifactu,no_verifactu'],
            'cert_file' => ['nullable', 'file', 'max:5120'],
            'cert_password' => ['nullable', 'string', 'max:255'],
        ]);

        $config = $user->sifConfig ?? new UserSifConfig(['user_id' => $user->id]);
        $config->user_id = $user->id;
        $config->enabled = $request->boolean('verifactu_enabled');
        $config->mode = $data['verifactu_mode'];

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

            if ($config->cert_path && Storage::disk('local')->exists($config->cert_path)) {
                Storage::disk('local')->delete($config->cert_path);
            }

            $path = 'sif/certs/user_'.$user->id.'.p12.enc';
            Storage::disk('local')->put($path, encrypt($p12Content));
            $config->cert_path = $path;
            $config->cert_expires_at = $expiresAt;

            Cache::put("verifactu:cert_password:{$user->id}", $data['cert_password'], now()->addHours(24));
        }

        $config->save();

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
            'email' => $user->email,
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

        if ($user->isPro()) {
            try {
                $stripe->cancelSubscription($user);
            } catch (\Throwable) {
                // la baja RGPD no debe bloquearse por un fallo de Stripe
            }
        }

        if ($user->logo_path) {
            Storage::disk('public')->delete($user->logo_path);
        }

        DB::transaction(function () use ($user): void {
            $user->documents()->delete();
            $user->clients()->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete();
        });

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
