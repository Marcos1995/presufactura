<?php

namespace Tests\Unit;

use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Models\UserSifConfig;
use App\Services\Verifactu\AeatSoapClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AeatSoapClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_build_soap_envelope_wraps_registro_with_cabecera(): void
    {
        $user = User::factory()->onboarded()->create([
            'tax_id' => '89890001K',
            'business_name' => 'Empresa Test SL',
        ]);

        $record = $this->makeBillingRecord($user, '<RegistroAlta><IDVersion>1.0</IDVersion></RegistroAlta>');
        $client = new AeatSoapClient;

        $envelope = $client->buildSoapEnvelope($record, '<RegistroAlta><IDVersion>1.0</IDVersion></RegistroAlta>');

        $this->assertStringContainsString('RegFactuSistemaFacturacion', $envelope);
        $this->assertStringContainsString('ObligadoEmision', $envelope);
        $this->assertStringContainsString('89890001K', $envelope);
        $this->assertStringContainsString('Empresa Test SL', $envelope);
        $this->assertStringContainsString('RegistroFactura', $envelope);
        $this->assertStringContainsString('RegistroAlta', $envelope);
    }

    public function test_submit_returns_permanent_error_without_certificate(): void
    {
        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $record = $this->makeBillingRecord($user, '<RegistroAlta/>');
        $client = new AeatSoapClient;

        $result = $client->submit($record, 'secret');

        if (extension_loaded('soap')) {
            $this->assertFalse($result['success']);
            $this->assertSame('Certificado no configurado', $result['message']);
            $this->assertTrue($result['permanent']);
        } else {
            $this->assertFalse($result['success']);
            $this->assertSame('ext-soap no disponible en este servidor', $result['message']);
            $this->assertTrue($result['permanent']);
        }
    }

    public function test_submit_sends_envelope_via_soap_client(): void
    {
        if (! extension_loaded('soap')) {
            $this->markTestSkipped('ext-soap no disponible');
        }

        $user = User::factory()->onboarded()->create([
            'tax_id' => '89890001K',
            'business_name' => 'Empresa Test SL',
        ]);

        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
            'cert_path' => 'sif/certs/user_'.$user->id.'.p12.enc',
            'cert_expires_at' => now()->addYear(),
        ]);

        Storage::disk('local')->put('sif/certs/user_'.$user->id.'.p12.enc', encrypt($this->makeTestP12('secret')));

        $record = $this->makeBillingRecord($user, '<RegistroAlta><IDVersion>1.0</IDVersion></RegistroAlta>');

        $soapMock = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__doRequest', '__getLocation'])
            ->getMock();

        $soapMock->method('__getLocation')->willReturn('https://prewww2.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion');
        $soapMock->method('__doRequest')->willReturn(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<RespuestaRegFactuSistemaFacturacion>
    <EstadoEnvio>Correcto</EstadoEnvio>
    <CSV>ABC123</CSV>
</RespuestaRegFactuSistemaFacturacion>
XML);

        $client = new AeatSoapClient(function () use ($soapMock) {
            return $soapMock;
        });

        $result = $client->submit($record, 'secret');

        $this->assertTrue($result['success']);
        $this->assertSame('ABC123', $result['csv']);
    }

    private function makeBillingRecord(User $user, string $xml): BillingRecord
    {
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
        ]);

        $document = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-001',
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'token-1',
        ]);

        $path = 'sif/'.$user->id.'/F2026-001_alta.xml';
        Storage::disk('local')->put($path, $xml);

        return BillingRecord::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'xml_path' => $path,
            'hash_current' => str_repeat('A', 64),
            'aeat_status' => BillingRecord::STATUS_PENDING,
        ]);
    }

    private function makeTestP12(string $password): string
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $csr = openssl_csr_new(['commonName' => 'Test VeriFactu'], $key);
        $cert = openssl_csr_sign($csr, null, $key, 365);
        $p12 = '';
        openssl_pkcs12_export($cert, $p12, $key, $password);

        return $p12;
    }
}
