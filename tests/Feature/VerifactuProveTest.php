<?php

namespace Tests\Feature;

use App\Jobs\SubmitBillingRecordJob;
use App\Models\BillingRecord;
use App\Models\Document;
use App\Models\User;
use App\Services\Verifactu\BillingRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VerifactuProveTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_gets_verifactu_enabled_and_full_sif_pipeline_with_certificate(): void
    {
        Notification::fake();
        Mail::fake();
        Queue::fake();

        $this->post('/registro', [
            'name' => 'Autonomo Demo',
            'email' => 'verifactu-demo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $user = User::where('email', 'verifactu-demo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasVerifactuEnabled());
        $this->assertFalse($user->canEmitFiscalInvoices());

        $user->forceFill([
            'email_verified_at' => now(),
            'business_name' => 'Demo SL',
            'tax_id' => '89890001K',
            'iban' => 'ES9121000418450200051332',
            'onboarding_completed_at' => now(),
        ])->save();

        $client = $this->createClient($user);
        $draft = $this->createDocument($user, $client, [
            'number' => 'F-PROVE-1',
            'status' => Document::STATUS_DRAFT,
        ]);
        $draft->lineItems()->create([
            'description' => 'Servicio demo',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 21,
            'line_subtotal' => 100,
            'line_vat' => 21,
            'line_total' => 121,
            'sort_order' => 0,
        ]);

        $this->actingAs($user->fresh())->post(route('invoices.send', $draft))->assertRedirect();
        $this->assertSame(Document::STATUS_SENT, $draft->fresh()->status);
        $this->assertSame(Document::STATUS_SENT, $draft->fresh()->status);
        $this->assertDatabaseMissing('billing_records', ['document_id' => $draft->id]);
        $this->assertFalse($draft->fresh()->isFiscal());

        $this->activateVerifactuCertificate($user);
        $user->sifConfig->storeCertPassword('secret');

        $fiscalDraft = $this->createDocument($user, $client, [
            'number' => 'F-PROVE-2',
            'status' => Document::STATUS_DRAFT,
        ]);
        $fiscalDraft->lineItems()->create([
            'description' => 'Servicio fiscal',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_rate' => 21,
            'line_subtotal' => 100,
            'line_vat' => 21,
            'line_total' => 121,
            'sort_order' => 0,
        ]);

        $user->refresh();
        $this->assertTrue($user->canEmitFiscalInvoices());

        $send = $this->actingAs($user)->post(route('invoices.send', $fiscalDraft));
        $send->assertRedirect();
        $this->assertNull(session('error'), (string) session('error'));
        $this->assertSame(Document::STATUS_SENT, $fiscalDraft->fresh()->status);

        $record = BillingRecord::where('document_id', $fiscalDraft->id)->first();
        $this->assertNotNull($record);
        $this->assertSame(BillingRecord::TYPE_ALTA, $record->record_type);
        $this->assertMatchesRegularExpression('/^[A-F0-9]{64}$/', $record->hash_current);
        Queue::assertPushed(SubmitBillingRecordJob::class);

        $chained = $this->createDocument($user, $client, [
            'number' => 'F-PROVE-3',
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
        ]);
        $chained->lineItems()->create([
            'description' => 'Servicio encadenado',
            'quantity' => 1,
            'unit_price' => 50,
            'vat_rate' => 21,
            'line_subtotal' => 50,
            'line_vat' => 10.5,
            'line_total' => 60.5,
            'sort_order' => 0,
        ]);

        $second = app(BillingRecordService::class)->createAltaRecord($chained->fresh(['user.sifConfig', 'client', 'lineItems']));
        $this->assertNotNull($second);
        $this->assertSame($record->hash_current, $second->hash_previous);

        $this->get(route('quotes.public', ['token' => $fiscalDraft->fresh()->public_token]))
            ->assertOk()
            ->assertSee('Factura verificable');
    }

    public function test_artisan_prove_command_passes_and_rolls_back(): void
    {
        $this->artisan('presufactura:verifactu-prove')
            ->expectsOutputToContain('Veri*Factu: demostración OK.')
            ->expectsOutputToContain('Sandbox acepta sin enviar a AEAT')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('billing_records', 0);
    }
}
