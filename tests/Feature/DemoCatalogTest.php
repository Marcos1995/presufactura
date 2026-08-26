<?php

namespace Tests\Feature;

use App\Models\BillingRecord;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_command_requires_configured_email(): void
    {
        config(['demo.admin_email' => '']);

        $this->artisan('presufactura:seed-demo')
            ->expectsOutputToContain('Define DEMO_ADMIN_EMAIL')
            ->assertFailed();
    }

    public function test_seed_command_creates_catalog_only_for_demo_admin(): void
    {
        $admin = User::factory()->onboarded()->create([
            'email' => 'admin-demo@example.com',
            'tax_id' => '89890001K',
        ]);
        $other = User::factory()->onboarded()->create([
            'email' => 'otro@example.com',
        ]);
        config(['demo.admin_email' => 'admin-demo@example.com']);

        $this->artisan('presufactura:seed-demo')
            ->expectsOutputToContain('Catálogo de ejemplo listo')
            ->assertSuccessful();

        $this->assertTrue($admin->fresh()->isDemoAdmin());
        $this->assertFalse($other->fresh()->isDemoAdmin());
        $this->assertSame(1, $admin->clients()->count());
        $this->assertSame(0, $other->clients()->count());
        $this->assertDatabaseHas('documents', [
            'user_id' => $admin->id,
            'number' => 'DEMO-P-001',
            'type' => Document::TYPE_QUOTE,
        ]);
        $this->assertDatabaseHas('documents', [
            'user_id' => $admin->id,
            'number' => 'DEMO-F-FIS',
            'type' => Document::TYPE_INVOICE,
        ]);
        $this->assertDatabaseHas('billing_records', [
            'user_id' => $admin->id,
            'record_type' => BillingRecord::TYPE_ALTA,
        ]);
        $this->assertNull($admin->fresh()->sifConfig->cert_path);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Catálogo de ejemplo');
        $this->actingAs($other)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Catálogo de ejemplo');
    }

    public function test_marcos_gmail_is_the_demo_admin(): void
    {
        $user = User::factory()->onboarded()->create([
            'email' => 'marcospc1995@gmail.com',
        ]);

        $this->assertTrue($user->isDemoAdmin());
        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Catálogo de ejemplo');
    }
}
