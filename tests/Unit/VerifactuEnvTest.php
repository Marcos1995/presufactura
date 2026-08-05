<?php

namespace Tests\Unit;

use App\Support\VerifactuEnv;
use Tests\TestCase;

class VerifactuEnvTest extends TestCase
{
    public function test_preprod_is_default(): void
    {
        config(['verifactu.env' => 'preprod']);

        $this->assertTrue(VerifactuEnv::isPreprod());
        $this->assertFalse(VerifactuEnv::isProd());
        $this->assertSame('Entorno de pruebas AEAT', VerifactuEnv::label());
        $this->assertSame('badge-env-preprod', VerifactuEnv::badgeClass());
    }

    public function test_prod_env(): void
    {
        config(['verifactu.env' => 'prod']);

        $this->assertTrue(VerifactuEnv::isProd());
        $this->assertFalse(VerifactuEnv::isPreprod());
        $this->assertSame('Entorno real AEAT', VerifactuEnv::label());
        $this->assertSame('badge-env-prod', VerifactuEnv::badgeClass());
    }
}
