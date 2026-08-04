<?php

namespace Tests\Feature;

use App\Jobs\SubmitBillingRecordJob;
use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\SifEvent;
use App\Models\User;
use App\Models\UserSifConfig;
use App\Services\Verifactu\QrService;
use App\Services\Verifactu\XmlBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
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

    public function test_cancelling_aeat_accepted_invoice_creates_anulacion_record(): void
    {
        Queue::fake();

        $user = User::factory()->onboarded()->create();
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
        ]);

        $invoice = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-003',
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'token789',
            'sent_at' => now(),
        ]);

        BillingRecord::create([
            'document_id' => $invoice->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'xml_path' => 'sif/1/test.xml',
            'hash_current' => hash('sha256', 'alta'),
            'hash_previous' => null,
            'aeat_status' => BillingRecord::STATUS_ACCEPTED,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('invoices.cancel', $invoice));

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'id' => $invoice->id,
            'status' => Document::STATUS_CANCELLED,
        ]);
        $this->assertDatabaseHas('billing_records', [
            'document_id' => $invoice->id,
            'record_type' => BillingRecord::TYPE_ANULACION,
            'aeat_status' => BillingRecord::STATUS_PENDING,
        ]);
        Queue::assertPushed(SubmitBillingRecordJob::class);
    }

    public function test_cancelled_invoice_cannot_be_edited(): void
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
            'number' => 'F2026-004',
            'status' => Document::STATUS_CANCELLED,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'tokencancel',
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

    public function test_invoice_without_aeat_acceptance_cannot_be_cancelled(): void
    {
        $user = User::factory()->onboarded()->create();
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
        ]);

        $invoice = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-005',
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'tokenpending',
            'sent_at' => now(),
        ]);

        BillingRecord::create([
            'document_id' => $invoice->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'xml_path' => 'sif/1/test.xml',
            'hash_current' => hash('sha256', 'pending'),
            'hash_previous' => null,
            'aeat_status' => BillingRecord::STATUS_PENDING,
        ]);

        $this->actingAs($user);

        $this->post(route('invoices.cancel', $invoice))->assertForbidden();
    }

    public function test_full_invoice_emission_flow_creates_record_job_and_qr_pdf(): void
    {
        Queue::fake();
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
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
            'number' => 'FAC-2026-001',
            'status' => Document::STATUS_DRAFT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'fullflowtoken',
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
        $this->post(route('invoices.send', $invoice))->assertRedirect();

        $invoice->refresh();
        $record = BillingRecord::where('document_id', $invoice->id)->first();
        $this->assertNotNull($record);
        $this->assertSame(BillingRecord::TYPE_ALTA, $record->record_type);
        Queue::assertPushed(SubmitBillingRecordJob::class);

        $pdfResponse = $this->get(route('invoices.pdf', $invoice));
        $pdfResponse->assertOk();
        $this->assertStringStartsWith('%PDF', $pdfResponse->getContent());

        $document = $invoice->fresh(['user', 'client', 'lineItems', 'billingRecord']);
        $qrDataUri = app(QrService::class)->generateDataUri($record);
        $html = View::make('pdf.invoice', [
            'document' => $document,
            'logoDataUri' => null,
            'qrDataUri' => $qrDataUri,
            'isFiscal' => true,
        ])->render();

        $this->assertStringContainsString('Factura verificable en sede.agenciatributaria.gob.es', $html);
    }

    public function test_paid_invoice_cannot_be_edited_or_deleted(): void
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
            'number' => 'F2026-006',
            'status' => Document::STATUS_PAID,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'tokenpaid',
            'paid_at' => now(),
        ]);

        $this->actingAs($user);

        $payload = [
            'client_id' => $client->id,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'lines' => [
                ['description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'vat_rate' => 21],
            ],
        ];

        $this->put(route('invoices.update', $invoice), $payload)->assertForbidden();
        $this->delete(route('invoices.destroy', $invoice))->assertForbidden();
    }

    public function test_create_rectificativa_uses_series_r_and_links_original(): void
    {
        $user = User::factory()->onboarded()->create();
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
        ]);

        $original = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'FAC-2026-010',
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'originaltoken',
            'sent_at' => now(),
        ]);

        $original->lineItems()->create([
            'description' => 'Servicio',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 21,
            'line_subtotal' => 100,
            'line_vat' => 21,
            'line_total' => 121,
            'sort_order' => 0,
        ]);

        BillingRecord::create([
            'document_id' => $original->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'xml_path' => 'sif/1/original.xml',
            'hash_current' => hash('sha256', 'original'),
            'aeat_status' => BillingRecord::STATUS_ACCEPTED,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('invoices.rectificativa', $original));
        $response->assertRedirect();

        $rectificativa = Document::where('rectifies_document_id', $original->id)->first();
        $this->assertNotNull($rectificativa);
        $this->assertStringStartsWith('R-', $rectificativa->number);
        $this->assertSame(Document::STATUS_DRAFT, $rectificativa->status);
    }

    public function test_sending_rectificativa_creates_r1_billing_record(): void
    {
        Queue::fake();
        Mail::fake();
        Storage::fake('local');

        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
            'tax_id' => 'B87654321',
        ]);

        $original = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'FAC-2026-011',
            'status' => Document::STATUS_SENT,
            'issue_date' => now()->setDate(2026, 1, 15),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'orig2',
            'sent_at' => now(),
        ]);

        $rectificativa = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'R-2026-001',
            'status' => Document::STATUS_DRAFT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 50,
            'vat_amount' => 10.5,
            'total' => 60.5,
            'public_token' => 'recttoken',
            'rectifies_document_id' => $original->id,
        ]);

        $rectificativa->lineItems()->create([
            'description' => 'Ajuste',
            'quantity' => 1,
            'unit_price' => 50,
            'vat_rate' => 21,
            'line_subtotal' => 50,
            'line_vat' => 10.5,
            'line_total' => 60.5,
            'sort_order' => 0,
        ]);

        $this->actingAs($user);
        $this->post(route('invoices.send', $rectificativa));

        $record = BillingRecord::where('document_id', $rectificativa->id)->first();
        $this->assertNotNull($record);

        $xml = app(XmlBuilderService::class)->buildAltaXml(
            $rectificativa->fresh(['user', 'client', 'lineItems', 'rectifiesDocument']),
            $record->hash_current,
            now()->format('c')
        );

        $this->assertStringContainsString('<TipoFactura>R1</TipoFactura>', $xml);
        $this->assertStringContainsString('<FacturasRectificadas>', $xml);
        $this->assertStringContainsString('FAC-2026-011', $xml);
        Queue::assertPushed(SubmitBillingRecordJob::class);
    }

    public function test_verifactu_export_command_creates_zip_and_sif_event(): void
    {
        Storage::fake('local');

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
            'number' => 'FAC-2026-012',
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'exporttoken',
        ]);

        Storage::disk('local')->put('sif/1/test_alta.xml', '<xml/>');

        BillingRecord::create([
            'document_id' => $invoice->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'xml_path' => 'sif/1/test_alta.xml',
            'hash_current' => hash('sha256', 'export'),
            'aeat_status' => BillingRecord::STATUS_ACCEPTED,
        ]);

        $this->artisan('presufactura:verifactu-export', ['user' => $user->id])
            ->assertSuccessful();

        $this->assertDatabaseHas('sif_events', [
            'user_id' => $user->id,
            'event_type' => SifEvent::TYPE_EXPORT,
        ]);
    }
}
