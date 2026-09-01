<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_quote_can_be_accepted(): void
    {
        Mail::fake();

        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $quote = $this->createDocument($user, $client, [
            'type' => Document::TYPE_QUOTE,
            'number' => 'PRE-0001',
            'status' => Document::STATUS_SENT,
            'valid_until' => now()->addDays(7)->toDateString(),
            'due_date' => null,
            'public_token' => 'token-aceptar-test',
        ]);

        $response = $this->post(route('quotes.public.accept', ['token' => $quote->public_token]));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $quote->refresh();
        $this->assertSame(Document::STATUS_ACCEPTED, $quote->status);
        $this->assertNotNull($quote->accepted_at);
    }

    public function test_public_invoice_shows_proforma_without_verifactu(): void
    {
        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $invoice = $this->createDocument($user, $client, [
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
            'public_token' => 'token-proforma-public',
        ]);

        $this->get(route('quotes.public', ['token' => $invoice->public_token]))
            ->assertOk()
            ->assertSee('Documento proforma')
            ->assertDontSee('Factura verificable — Veri*Factu')
            ->assertSee('VERI*FACTU')
            ->assertSee('data:image/png;base64,', false);
    }

    public function test_public_invoice_shows_fiscal_badge_when_sif_alta_exists(): void
    {
        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $invoice = $this->createDocument($user, $client, [
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
            'public_token' => 'token-fiscal-public',
        ]);

        $invoice->billingRecords()->create([
            'user_id' => $user->id,
            'record_type' => \App\Models\BillingRecord::TYPE_ALTA,
            'hash_current' => str_repeat('a', 64),
            'aeat_status' => \App\Models\BillingRecord::STATUS_PENDING,
        ]);

        $this->get(route('quotes.public', ['token' => $invoice->public_token]))
            ->assertOk()
            ->assertSee('Factura verificable')
            ->assertDontSee('Documento proforma');
    }
}
