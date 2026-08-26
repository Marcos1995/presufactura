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
        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('id="sidebar-toggle"', false)
            ->assertSee('class="menu-fold"', false)
            ->assertSee('appearance:none', false)
            ->assertSee('css/app.css?v=', false)
            ->assertSee('js/app.js?v=', false)
            ->assertDontSee('class="sidebar-toggle"', false);

        $css = file_get_contents(public_path('css/app.css'));
        $this->assertStringContainsString('position: sticky', $css);
        $this->assertStringContainsString('height: 100dvh', $css);
        $this->assertStringContainsString('overflow-wrap: anywhere', $css);
        $this->assertStringContainsString('.alert-warning', $css);
        $this->actingAs($user)->get('/clientes')->assertOk();
        $this->actingAs($user)->get('/facturas/nueva')
            ->assertOk()
            ->assertSee('Crea un cliente primero');
        $this->actingAs($user)->get('/presupuestos/nuevo')
            ->assertOk()
            ->assertSee('Crea un cliente primero');
        $this->actingAs($user)->get('/stripe/cancel')
            ->assertOk()
            ->assertDontSee('Ver planes')
            ->assertSee('Ir al dashboard');
    }

    public function test_quote_show_offers_pdf_and_quote_status_gender(): void
    {
        $user = $this->verifiedUser();
        $client = $this->createClient($user);
        $quote = $this->createDocument($user, $client, [
            'type' => \App\Models\Document::TYPE_QUOTE,
            'number' => 'PRE-TEST-001',
            'status' => \App\Models\Document::STATUS_SENT,
            'due_date' => null,
            'valid_until' => now()->addDays(15)->toDateString(),
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('quotes.show', $quote))
            ->assertOk()
            ->assertSee(route('quotes.pdf', $quote), false)
            ->assertSee('Enviado')
            ->assertDontSee('Enviada');
    }

    public function test_sent_invoice_show_page_works_without_paid_at(): void
    {
        $user = $this->verifiedUser();
        $client = $this->createClient($user);
        $invoice = $this->createDocument($user, $client, [
            'status' => \App\Models\Document::STATUS_SENT,
            'sent_at' => now(),
            'paid_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertDontSee('Pagada:');
    }
}
