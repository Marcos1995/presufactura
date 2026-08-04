<?php

namespace Tests\Unit;

use App\Jobs\SubmitBillingRecordJob;
use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Models\UserSifConfig;
use App\Services\Verifactu\BillingRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillingRecordServiceTest extends TestCase
{
    use RefreshDatabase;

    private BillingRecordService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
        $this->service = app(BillingRecordService::class);
    }

    public function test_create_alta_record_stores_xml_and_hash(): void
    {
        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $document = $this->makeInvoice($user);

        $record = $this->service->createAltaRecord($document);

        $this->assertNotNull($record);
        $this->assertSame(BillingRecord::TYPE_ALTA, $record->record_type);
        $this->assertNull($record->hash_previous);
        $this->assertMatchesRegularExpression('/^[A-F0-9]{64}$/', $record->hash_current);
        $this->assertNotNull($record->xml_path);
        Storage::disk('local')->assertExists($record->xml_path);
        Queue::assertPushed(SubmitBillingRecordJob::class);
    }

    public function test_second_record_chains_to_previous_hash(): void
    {
        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
        UserSifConfig::create([
            'user_id' => $user->id,
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'enabled' => true,
        ]);

        $first = $this->service->createAltaRecord($this->makeInvoice($user, 'F2026-001'));
        $second = $this->service->createAltaRecord($this->makeInvoice($user, 'F2026-002'));

        $this->assertSame($first->hash_current, $second->hash_previous);
        $this->assertNotSame($first->hash_current, $second->hash_current);
    }

    public function test_skips_when_verifactu_disabled(): void
    {
        $user = User::factory()->onboarded()->create();
        $record = $this->service->createAltaRecord($this->makeInvoice($user));

        $this->assertNull($record);
        $this->assertDatabaseCount('billing_records', 0);
    }

    public function test_skips_non_invoice_documents(): void
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

        $quote = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_QUOTE,
            'number' => 'P2026-001',
            'status' => Document::STATUS_DRAFT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'quotetoken',
        ]);

        $this->assertNull($this->service->createAltaRecord($quote));
    }

    private function makeInvoice(User $user, string $number = 'F2026-001'): Document
    {
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
            'number' => $number,
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'token-'.$number,
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

        return $invoice->fresh(['user', 'client', 'lineItems']);
    }
}
