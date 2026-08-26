<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_client_quote_invoice_or_pdf(): void
    {
        $owner = User::factory()->onboarded()->create();
        $stranger = User::factory()->onboarded()->create();
        $client = $this->createClient($owner);
        $invoice = $this->createDocument($owner, $client);
        $quote = $this->createDocument($owner, $client, [
            'type' => Document::TYPE_QUOTE,
            'number' => 'PRE-0001',
            'due_date' => null,
            'valid_until' => now()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($stranger);

        $this->get(route('clients.edit', $client))->assertForbidden();
        $this->put(route('clients.update', $client), [
            'name' => 'Hack',
            'email' => 'hack@example.com',
        ])->assertForbidden();
        $this->delete(route('clients.destroy', $client))->assertForbidden();

        $this->get(route('invoices.show', $invoice))->assertForbidden();
        $this->get(route('invoices.pdf', $invoice))->assertForbidden();
        $this->get(route('quotes.show', $quote))->assertForbidden();
        $this->get(route('quotes.pdf', $quote))->assertForbidden();
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
