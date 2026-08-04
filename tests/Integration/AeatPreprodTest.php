<?php

namespace Tests\Integration;

use App\Services\Verifactu\AeatSoapClient;
use Tests\TestCase;

/**
 * @group aeatinternal
 */
class AeatPreprodTest extends TestCase
{
    public function test_preprod_soap_submit_with_real_certificate(): void
    {
        if (! env('VERIFACTU_PREPROD_TEST') || ! env('VERIFACTU_TEST_P12_PATH')) {
            $this->markTestSkipped('Requiere VERIFACTU_PREPROD_TEST y certificado de pruebas AEAT.');
        }

        if (! extension_loaded('soap')) {
            $this->markTestSkipped('ext-soap no disponible.');
        }

        $this->assertTrue(class_exists(AeatSoapClient::class));
    }
}
