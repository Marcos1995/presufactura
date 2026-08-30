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

    public function test_verifactu_check_fails_without_demo_user(): void
    {
        $this->artisan('presufactura:verifactu-check')
            ->expectsOutputToContain('Veri*Factu pruebas: FALLIDO')
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
        \Illuminate\Support\Facades\Storage::fake('local');

        $this->artisan('presufactura:prepare-test-user')
            ->expectsOutputToContain('Usuario de pruebas listo')
            ->assertSuccessful();

        $this->assertTrue($admin->fresh()->usesVerifactuSandbox());
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
            'aeat_status' => BillingRecord::STATUS_ACCEPTED,
        ]);
        $this->assertNotNull($admin->fresh()->sifConfig->cert_path);

        $fiscal = Document::query()->where('user_id', $admin->id)->where('number', 'DEMO-F-FIS')->first();
        $this->assertTrue($fiscal->fresh()->isFiscal());

        $this->actingAs($admin)->get(route('invoices.show', $fiscal))
            ->assertOk()
            ->assertSee('Aceptada (pruebas)')
            ->assertSee('VERI*FACTU')
            ->assertSee('data:image/png;base64,', false)
            ->assertSee('Comprobar en la AEAT');
        $this->get(route('quotes.public', ['token' => $fiscal->public_token]))
            ->assertOk()
            ->assertSee('Factura verificable')
            ->assertSee('VERI*FACTU')
            ->assertSee('data:image/png;base64,', false);

        $pdf = $this->actingAs($admin)->get(route('invoices.pdf', $fiscal));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertMatchesRegularExpression('/\/(Subtype\s*\/Image|XObject)/', $pdf->getContent());

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Catálogo de ejemplo');
        $this->actingAs($other)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Catálogo de ejemplo');

        $this->artisan('presufactura:verifactu-check')
            ->expectsOutputToContain('Veri*Factu pruebas: OK')
            ->assertSuccessful();

        $this->actingAs($admin)->get('/configuracion')
            ->assertOk()
            ->assertSee('Prueba Veri*Factu en este servidor: OK');
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

    public function test_dev_cert_command_installs_self_signed_p12_for_demo_admin(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $user = User::factory()->onboarded()->create([
            'email' => 'marcospc1995@gmail.com',
            'tax_id' => '89890001K',
        ]);

        $this->artisan('presufactura:verifactu-dev-cert')
            ->expectsOutputToContain('Certificado de desarrollo instalado')
            ->assertSuccessful();

        $config = $user->fresh()->sifConfig;
        $this->assertTrue($config->is_dev_cert);
        $this->assertTrue($config->hasValidCertificate());
        $this->assertTrue($user->fresh()->canEmitFiscalInvoices());
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($config->cert_path);

        $this->actingAs($user)->get('/configuracion')
            ->assertOk()
            ->assertSee('Certificado de desarrollo');
    }

    public function test_dev_cert_command_adds_missing_is_dev_cert_column(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        User::factory()->onboarded()->create([
            'email' => 'marcospc1995@gmail.com',
            'tax_id' => '89890001K',
        ]);

        \Illuminate\Support\Facades\Schema::table('user_sif_config', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('is_dev_cert');
        });
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('user_sif_config', 'is_dev_cert'));

        $this->artisan('presufactura:verifactu-dev-cert')
            ->assertSuccessful();

        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('user_sif_config', 'is_dev_cert'));
        $this->assertTrue(User::where('email', 'marcospc1995@gmail.com')->first()->sifConfig->is_dev_cert);
    }
}
