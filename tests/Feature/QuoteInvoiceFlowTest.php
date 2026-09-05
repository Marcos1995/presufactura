<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteInvoiceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_crud_quote_convert_vat_numbering_pdf_and_email(): void
    {
        $user = User::factory()->onboarded()->create();
        $this->actingAs($user);

        $this->post('/clientes', [
            'name' => 'Acme SL',
            'email' => 'acme@example.com',
        ])->assertRedirect(route('clients.index'));

        $client = $user->clients()->first();
        $this->assertNotNull($client);

        $this->put(route('clients.update', $client), [
            'name' => 'Acme SLU',
            'email' => 'acme@example.com',
        ])->assertRedirect(route('clients.index'));
        $this->assertSame('Acme SLU', $client->fresh()->name);

        $this->post('/presupuestos', $this->quotePayload($client->id))->assertRedirect();
        $quote = $user->documents()->where('type', Document::TYPE_QUOTE)->first();
        $this->assertNotNull($quote);
        $this->assertEquals(100, (float) $quote->subtotal);
        $this->assertEquals(21, (float) $quote->vat_amount);
        $this->assertEquals(121, (float) $quote->total);
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::FIRST_QUOTE_CREATED]);

        $this->get(route('quotes.pdf', $quote))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::PDF_GENERATED]);

        $this->post(route('quotes.convert', $quote))->assertRedirect();
        $invoice = $user->documents()->where('type', Document::TYPE_INVOICE)->first();
        $this->assertNotNull($invoice);
        $this->assertSame($quote->id, $invoice->converted_from_id);
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::QUOTE_TO_INVOICE]);
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::FIRST_INVOICE_CREATED]);

        $year = now()->year;
        $this->post('/facturas', $this->invoicePayload($client->id))->assertRedirect();
        $invoices = $user->documents()->where('type', Document::TYPE_INVOICE)->orderBy('id')->get();
        $this->assertTrue($invoices->every(fn (Document $doc) => str_starts_with($doc->number, 'BOR-F-')));

        $this->post(route('invoices.send', $invoice))->assertRedirect();
        $this->assertSame(Document::STATUS_SENT, $invoice->fresh()->status);
        $this->assertSame("FAC-{$year}-001", $invoice->fresh()->number);
        $this->assertDatabaseHas('analytics_events', ['name' => AnalyticsEvent::INVOICE_EMAIL_SENT]);

        $second = $invoices->last();
        $this->post(route('invoices.send', $second))->assertRedirect();
        $this->assertSame("FAC-{$year}-002", $second->fresh()->number);

        $this->get(route('quotes.public', ['token' => $quote->fresh()->public_token]))->assertOk();

        $this->delete(route('clients.destroy', $client))->assertRedirect();
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_quick_start_creates_sample_quote(): void
    {
        $user = User::factory()->onboarded()->create();
        $this->actingAs($user)
            ->post(route('quickstart.quote'))
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'type' => Document::TYPE_QUOTE,
            'status' => Document::STATUS_DRAFT,
        ]);
    }

    public function test_rectificativa_and_cancel_require_accepted_sif(): void
    {
        $user = User::factory()->onboarded()->create();
        $client = $this->createClient($user);
        $invoice = $this->createDocument($user, $client, [
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $this->actingAs($user);
        $this->post(route('invoices.rectificativa', $invoice))->assertForbidden();
        $this->post(route('invoices.cancel', $invoice))->assertForbidden();
    }
}
