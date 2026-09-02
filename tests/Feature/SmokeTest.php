<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_returns_200(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Tres pasos. Cobras.')
            ->assertSee('Facturas verificables ante Hacienda')
            ->assertSee('id="verifactu"', false)
            ->assertSee('pricing-card', false)
            ->assertSee('Crear cuenta y activarlo')
            ->assertSee('Empezar gratis')
            ->assertSee('Así funciona')
            ->assertSee('data-mock-tab="verifactu"', false)
            ->assertSee('Factura verificable en la sede electrónica de la AEAT')
            ->assertDontSee('landing-marquee', false)
            ->assertDontSee('landing-verifactu', false)
            ->assertDontSee('verifactu-panel', false);

        $css = file_get_contents(public_path('css/app.css'));
        $this->assertIsString($css);
        $this->assertStringContainsString('.pricing-card', $css);
        $this->assertStringContainsString('.hero-demo', $css);
        $this->assertStringNotContainsString('.landing-verifactu', $css);
        $this->assertStringNotContainsString('.verifactu-panel', $css);
    }

    public function test_pricing_page_returns_200(): void
    {
        $this->get('/precios')->assertOk();
    }

    public function test_help_page_returns_200(): void
    {
        $this->get('/ayuda')->assertOk();
    }

    public function test_privacy_does_not_advertise_stripe_payments(): void
    {
        $this->get('/privacidad')
            ->assertOk()
            ->assertDontSee('Stripe (pagos)')
            ->assertSee('Veri*Factu')
            ->assertSee('Continuar con Google');
    }
}
