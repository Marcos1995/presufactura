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
        $this->actingAs($user)->get('/clientes')->assertOk();
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
