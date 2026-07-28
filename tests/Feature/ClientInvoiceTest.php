<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_client_and_draft_invoice(): void
    {
        $user = User::factory()->onboarded()->create();
        $this->actingAs($user);

        $clientResponse = $this->post('/clientes', [
            'name' => 'Acme SL',
            'email' => 'acme@example.com',
        ]);

        $clientResponse->assertRedirect(route('clients.index'));
        $client = $user->clients()->first();
        $this->assertNotNull($client);

        $invoiceResponse = $this->post('/facturas', $this->invoicePayload($client->id));

        $invoiceResponse->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'status' => Document::STATUS_DRAFT,
        ]);
    }
}
