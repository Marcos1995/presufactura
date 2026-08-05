<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PanelPagesWithoutVerifactuTablesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('billing_records');
        Schema::dropIfExists('user_sif_config');
        Schema::dropIfExists('sif_events');
    }

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

    public function test_invoices_index_without_billing_records_table(): void
    {
        $user = $this->verifiedUser();
        $client = $this->createClient($user);
        $this->createDocument($user, $client);

        $this->actingAs($user)->get('/facturas')->assertOk();
    }

    public function test_settings_without_sif_config_table(): void
    {
        $user = $this->verifiedUser();

        $response = $this->actingAs($user)->get('/configuracion');
        $response->assertOk();
    }
}
