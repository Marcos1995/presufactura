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
            ->assertSee('verifactu-inner', false)
            ->assertSee('verifactu-points', false)
            ->assertSee('Empezar gratis')
            ->assertDontSee('landing-marquee', false);

        $css = file_get_contents(public_path('css/app.css'));
        $this->assertIsString($css);
        $this->assertStringContainsString('.verifactu-inner', $css);
        $this->assertStringContainsString('clamp(1.25rem, 5vw, 2rem)', $css);
    }

    public function test_pricing_page_returns_200(): void
    {
        $this->get('/precios')->assertOk();
    }

    public function test_help_page_returns_200(): void
    {
        $this->get('/ayuda')->assertOk();
    }
}
