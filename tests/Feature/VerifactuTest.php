<?php

namespace Tests\Feature;

use App\Jobs\SubmitBillingRecordJob;
use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Models\UserSifConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VerifactuTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_invoice_creates_billing_record_when_verifactu_enabled(): void
    {
        Queue::fake();
        Mail::fake();

        $user = User::factory()->onboarded()->create();
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'tax_id' => 'B87654321',
        ]);

        $invoice = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-001',
            'status' => Document::STATUS_DRAFT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'testtoken123',
        ]);

        $invoice->lineItems()->create([
            'description' => 'Servicio',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 21,
            'line_subtotal' => 100,
            'line_vat' => 21,
            'line_total' => 121,
            'sort_order' => 0,
        ]);

        $this->actingAs($user);

        $this->post(route('invoices.send', $invoice));

        $this->assertDatabaseHas('billing_records', [
            'document_id' => $invoice->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'aeat_status' => BillingRecord::STATUS_PENDING,
        ]);

        Queue::assertPushed(SubmitBillingRecordJob::class);
    }

    public function test_sent_invoice_is_not_editable(): void
    {
        $user = User::factory()->onboarded()->create();
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
        ]);

        $invoice = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-002',
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'token456',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('invoices.update', $invoice), [
            'client_id' => $client->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'lines' => [
                ['description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21],
            ],
        ]);

        $response->assertForbidden();
    }
}
