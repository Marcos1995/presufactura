<?php

namespace Tests\Integration;

use App\Models\BillingRecord;
use App\Models\Document;
use App\Models\User;
use App\Services\Verifactu\AeatSoapClient;
use App\Services\Verifactu\BillingRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('aeatinternal')]
class AeatPreprodTest extends TestCase
{
    use RefreshDatabase;

    public function test_preprod_alta_and_anulacion_are_accepted_by_aeat(): void
    {
        $path = (string) env('VERIFACTU_TEST_P12_PATH', '');
        $password = (string) env('VERIFACTU_TEST_P12_PASSWORD', '');
        $nif = strtoupper(preg_replace('/\s+/', '', (string) (env('VERIFACTU_TEST_NIF') ?: config('verifactu.software.nif'))));

        if (! filter_var(env('VERIFACTU_PREPROD_TEST'), FILTER_VALIDATE_BOOLEAN) || $path === '' || ! is_file($path)) {
            $this->markTestSkipped('Requiere VERIFACTU_PREPROD_TEST=true y un .p12 de pruebas AEAT en VERIFACTU_TEST_P12_PATH.');
        }

        if ($password === '' || $nif === '') {
            $this->markTestSkipped('Requiere VERIFACTU_TEST_P12_PASSWORD y VERIFACTU_TEST_NIF (NIF del certificado).');
        }

        if (! extension_loaded('soap') || ! extension_loaded('openssl')) {
            $this->markTestSkipped('ext-soap y ext-openssl requeridos.');
        }

        config(['verifactu.env' => 'preprod']);

        $p12 = file_get_contents($path);
        $this->assertNotFalse($p12);
        $certs = [];
        if (! openssl_pkcs12_read($p12, $certs, $password)) {
            $this->fail('No se pudo leer el .p12. Revisa VERIFACTU_TEST_P12_PASSWORD.');
        }

        $user = User::factory()->onboarded()->create([
            'email' => 'aeat-preprod-test@example.com',
            'name' => 'Prueba AEAT',
            'business_name' => (string) env('VERIFACTU_TEST_NAME', 'Prueba Verifactu SL'),
            'tax_id' => $nif,
        ]);

        $this->activateVerifactuCertificate($user);
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

        $client = $this->createClient($user, [
            'name' => 'Cliente Preprod AEAT',
            'tax_id' => '99999950H',
        ]);

        $invoice = $this->createDocument($user, $client, [
            'number' => 'PRE-'.now()->format('YmdHis'),
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
            'issue_date' => now()->toDateString(),
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
        $invoice->update(['subtotal' => 10, 'vat_amount' => 2.10, 'total' => 12.10]);

        $billing = app(BillingRecordService::class);
        $soap = app(AeatSoapClient::class);

        try {
            $alta = $billing->createAltaRecord($invoice->fresh(['user.sifConfig', 'client', 'lineItems']));
        } catch (\RuntimeException $e) {
            $this->fail('AEAT alta: '.$e->getMessage());
        }

        $this->assertNotNull($alta);
        $alta->refresh();

        if ($alta->aeat_status === BillingRecord::STATUS_PENDING) {
            $result = $soap->submit($alta, $password);
            $this->assertTrue($result['success'] ?? false, 'AEAT alta: '.($result['message'] ?? 'sin respuesta'));
            $alta->update([
                'aeat_status' => BillingRecord::STATUS_ACCEPTED,
                'aeat_response' => $result,
                'sent_at' => now(),
            ]);
        }

        $this->assertSame(
            BillingRecord::STATUS_ACCEPTED,
            $alta->fresh()->aeat_status,
            json_encode($alta->fresh()->aeat_response)
        );

        try {
            $anulacion = $billing->createAnulacionRecord($invoice->fresh(['user.sifConfig', 'billingRecord']));
        } catch (\RuntimeException $e) {
            $this->fail('AEAT anulación: '.$e->getMessage());
        }

        $this->assertNotNull($anulacion);
        $anulacion->refresh();

        if ($anulacion->aeat_status === BillingRecord::STATUS_PENDING) {
            $result = $soap->submit($anulacion, $password);
            $this->assertTrue($result['success'] ?? false, 'AEAT anulación: '.($result['message'] ?? 'sin respuesta'));
            $anulacion->update([
                'aeat_status' => BillingRecord::STATUS_ACCEPTED,
                'aeat_response' => $result,
                'sent_at' => now(),
            ]);
        }

        $this->assertSame(
            BillingRecord::STATUS_ACCEPTED,
            $anulacion->fresh()->aeat_status,
            json_encode($anulacion->fresh()->aeat_response)
        );
    }
}
