<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelPagesTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
            'business_name' => 'Test SL',
            'tax_id' => '12345678A',
            'iban' => 'ES1234567890123456789012',
        ]);
    }

    public function test_panel_pages_return_200(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)->get('/facturas')->assertOk();
        $this->actingAs($user)->get('/presupuestos')->assertOk();
        $this->actingAs($user)->get('/configuracion')->assertOk();
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/clientes')->assertOk();
    }
}
