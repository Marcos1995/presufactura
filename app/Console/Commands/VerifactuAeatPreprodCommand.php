<?php

namespace App\Console\Commands;

use App\Models\BillingRecord;
use App\Models\Document;
use App\Models\User;
use App\Services\Verifactu\AeatSoapClient;
use App\Services\Verifactu\BillingRecordService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerifactuAeatPreprodCommand extends Command
{
    protected $signature = 'presufactura:verifactu-aeat-preprod {--keep : No enviar anulación tras el alta}';

    protected $description = 'Envía una factura de prueba a AEAT preprod (alta y anulación) con un .p12 real';

    public function handle(BillingRecordService $billing, AeatSoapClient $soap): int
    {
        if (! extension_loaded('soap') || ! extension_loaded('openssl')) {
            $this->error('Faltan ext-soap o ext-openssl.');

            return self::FAILURE;
        }

        config(['verifactu.env' => 'preprod', 'queue.default' => 'sync']);

        $prepared = $this->prepareIssuer();
        if ($prepared === null) {
            return self::FAILURE;
        }

        [$user, $password] = $prepared;

        $client = $user->clients()->first() ?? $user->clients()->create([
            'name' => 'Cliente Preprod AEAT',
            'email' => 'preprod-aeat@example.com',
            'tax_id' => '99999950H',
        ]);

        $invoice = $user->documents()->create([
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'PRE-'.now()->format('YmdHis'),
            'status' => Document::STATUS_SENT,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 10,
            'vat_amount' => 2.10,
            'total' => 12.10,
            'public_token' => Str::random(32),
            'sent_at' => now(),
        ]);
        $invoice->lineItems()->create([
            'description' => 'Prueba preprod AEAT',
            'quantity' => 1,
            'unit_price' => 10,
            'vat_rate' => 21,
            'line_subtotal' => 10,
            'line_vat' => 2.10,
            'line_total' => 12.10,
            'sort_order' => 0,
        ]);

        $this->info('Alta '.$invoice->number.' → AEAT preprod…');

        try {
            $alta = $billing->createAltaRecord($invoice->fresh(['user.sifConfig', 'client', 'lineItems']));
        } catch (\RuntimeException $e) {
            $this->error('Alta AEAT: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $alta) {
            $this->error('No se creó el registro SIF. ¿Certificado válido y Veri*Factu activo?');

            return self::FAILURE;
        }

        if (! $this->ensureAccepted($alta, $soap, $password, 'Alta')) {
            return self::FAILURE;
        }

        $this->info('✓ Alta aceptada. CSV: '.($alta->fresh()->aeat_response['csv'] ?? '—'));

        if ($this->option('keep')) {
            $this->warn('Sin anulación (--keep). Factura de prueba: '.$invoice->number);

            return self::SUCCESS;
        }

        $this->info('Anulación…');

        try {
            $anulacion = $billing->createAnulacionRecord($invoice->fresh(['user.sifConfig', 'billingRecord']));
        } catch (\RuntimeException $e) {
            $this->error('Anulación AEAT: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $anulacion || ! $this->ensureAccepted($anulacion, $soap, $password, 'Anulación')) {
            return self::FAILURE;
        }

        $invoice->update(['status' => Document::STATUS_CANCELLED]);
        $this->info('✓ Anulación aceptada. Prueba AEAT preprod: OK.');

        return self::SUCCESS;
    }

    /** @return array{0: User, 1: string}|null */
    private function prepareIssuer(): ?array
    {
        $path = (string) env('VERIFACTU_TEST_P12_PATH', '');
        $password = (string) env('VERIFACTU_TEST_P12_PASSWORD', '');
        $nif = strtoupper(preg_replace('/\s+/', '', (string) (env('VERIFACTU_TEST_NIF') ?: config('verifactu.software.nif'))));

        if ($path !== '' && is_file($path)) {
            if ($password === '' || $nif === '') {
                $this->error('Con VERIFACTU_TEST_P12_PATH hace falta VERIFACTU_TEST_P12_PASSWORD y VERIFACTU_TEST_NIF.');

                return null;
            }

            $p12 = file_get_contents($path);
            $certs = [];
            if ($p12 === false || ! openssl_pkcs12_read($p12, $certs, $password)) {
                $this->error('No se pudo leer el .p12. Revisa la contraseña.');

                return null;
            }

            $user = User::query()->where('email', 'aeat-preprod-test@example.com')->first();
            if (! $user) {
                $user = new User;
                $user->forceFill([
                    'email' => 'aeat-preprod-test@example.com',
                    'name' => 'Prueba AEAT',
                    'business_name' => (string) env('VERIFACTU_TEST_NAME', 'Prueba Verifactu SL'),
                    'tax_id' => $nif,
                    'password' => \Illuminate\Support\Facades\Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                    'onboarding_completed_at' => now(),
                    'iban' => 'ES9121000418450200051332',
                    'invoice_prefix' => 'FAC',
                    'quote_prefix' => 'PRE',
                    'default_vat_rate' => 21,
                ])->save();
            } else {
                $user->forceFill(['tax_id' => $nif])->save();
            }
            $user->ensureDefaultVerifactu();
            $encPath = 'sif/certs/user_'.$user->id.'.p12.enc';
            Storage::disk('local')->put($encPath, encrypt($p12));
            $user->sifConfig->update([
                'cert_path' => $encPath,
                'cert_expires_at' => now()->addYear(),
                'enabled' => true,
                'is_dev_cert' => false,
            ]);
            $user->sifConfig->storeCertPassword($password);
            $user->unsetRelation('sifConfig');

            return [$user->fresh(['sifConfig']), $password];
        }

        $email = config('demo.admin_email');
        $admin = filled($email) ? User::query()->where('email', $email)->first() : null;
        if (! $admin?->canEmitFiscalInvoices() || $admin->sifConfig?->is_dev_cert) {
            $this->error('Sin .p12 de pruebas. Sube tu certificado FNMT en Configuración (no el de desarrollo) o define VERIFACTU_TEST_P12_PATH.');

            return null;
        }

        $password = $admin->sifConfig->certPassword();
        if (! $password) {
            $this->error('Guarda de nuevo el certificado en Configuración para persistir la contraseña.');

            return null;
        }

        return [$admin, $password];
    }

    private function ensureAccepted(BillingRecord $record, AeatSoapClient $soap, string $password, string $label): bool
    {
        $record->refresh();

        if ($record->aeat_status === BillingRecord::STATUS_PENDING) {
            $result = $soap->submit($record, $password);
            if (! ($result['success'] ?? false)) {
                $this->error($label.' AEAT: '.($result['message'] ?? 'sin respuesta'));

                return false;
            }
            $record->update([
                'aeat_status' => BillingRecord::STATUS_ACCEPTED,
                'aeat_response' => $result,
                'sent_at' => now(),
            ]);
        }

        if ($record->fresh()->aeat_status !== BillingRecord::STATUS_ACCEPTED) {
            $this->error($label.' no aceptada: '.json_encode($record->fresh()->aeat_response));

            return false;
        }

        return true;
    }
}
