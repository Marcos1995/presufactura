<?php

namespace App\Support;

use App\Models\BillingRecord;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class VerifactuProductionCheck
{
    /**
     * Comprueba el sandbox de pruebas en este servidor. Nunca llama a AEAT.
     *
     * @return array{ok: bool, checks: list<array{label: string, pass: bool, hint: ?string}>}
     */
    public static function run(): array
    {
        $checks = [];

        $add = function (string $label, bool $pass, ?string $hint = null) use (&$checks): void {
            $checks[] = ['label' => $label, 'pass' => $pass, 'hint' => $hint];
        };

        $add(
            'Tablas Veri*Factu',
            VerifactuSchema::hasSifConfigTable() && VerifactuSchema::hasBillingRecordsTable(),
            'php artisan migrate --force'
        );
        $add(
            'Columna is_dev_cert',
            VerifactuSchema::hasDevCertColumn(),
            'php artisan migrate --force'
        );

        $email = config('demo.admin_email');
        $user = filled($email)
            ? User::query()->whereRaw('LOWER(email) = ?', [$email])->first()
            : null;

        $add(
            'Usuario demo'.(filled($email) ? ' ('.$email.')' : ''),
            (bool) $user,
            'Crea la cuenta, verifica el email y completa el onboarding'
        );

        if ($user && VerifactuSchema::hasSifConfigTable()) {
            $user->load('sifConfig');
            $add(
                'Sandbox activo (certificado de desarrollo)',
                $user->usesVerifactuSandbox() && $user->canEmitFiscalInvoices(),
                'php artisan presufactura:prepare-test-user'
            );

            $fiscal = Document::query()
                ->where('user_id', $user->id)
                ->where('number', 'DEMO-F-FIS')
                ->first();

            $add(
                'Factura DEMO-F-FIS fiscal',
                (bool) $fiscal?->isFiscal(),
                'php artisan presufactura:prepare-test-user'
            );

            $record = $fiscal?->billingRecord;
            $sandboxAccepted = $record
                && $record->aeat_status === BillingRecord::STATUS_ACCEPTED
                && ($record->aeat_response['sandbox'] ?? false) === true;

            $add(
                'Registro SIF Aceptada (pruebas)',
                $sandboxAccepted,
                'php artisan presufactura:prepare-test-user'
            );

            if ($record?->xml_path) {
                $add(
                    'XML SIF en disco',
                    Storage::disk('local')->exists($record->xml_path),
                    'Revisa permisos de storage/app'
                );
            }

            if ($fiscal && $record) {
                $qrOk = false;
                $pdfOk = false;
                try {
                    $payload = app(\App\Services\Verifactu\QrService::class)->payloadForDocument($fiscal->fresh(['user.sifConfig', 'billingRecord']));
                    $qrOk = is_array($payload) && str_starts_with((string) $payload['dataUri'], 'data:image/png;base64,');
                    $pdf = app(\App\Services\PdfGeneratorService::class)->generateInvoicePdf($fiscal);
                    $pdfOk = str_starts_with($pdf, '%PDF') && (str_contains($pdf, '/Image') || str_contains($pdf, '/XObject'));
                } catch (\Throwable) {
                    $qrOk = false;
                    $pdfOk = false;
                }
                $add('QR PNG de DEMO-F-FIS', $qrOk, 'php artisan presufactura:prepare-test-user');
                $add('PDF con imagen QR', $pdfOk, 'Comprueba ext-gd en PHP');
            }
        }

        $ok = true;
        foreach ($checks as $check) {
            if (! $check['pass']) {
                $ok = false;
                break;
            }
        }

        return ['ok' => $ok, 'checks' => $checks];
    }
}
