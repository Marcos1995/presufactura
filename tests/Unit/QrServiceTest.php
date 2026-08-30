<?php

namespace Tests\Unit;

use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Models\UserSifConfig;
use App\Services\PdfGeneratorService;
use App\Services\Verifactu\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class QrServiceTest extends TestCase
{
    use RefreshDatabase;

    private QrService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(QrService::class);
    }

    public function test_build_url_uses_env_mode_and_invoice_params(): void
    {
        config(['verifactu.env' => 'preprod']);

        $record = $this->makeBillingRecord(UserSifConfig::MODE_VERIFACTU);

        $url = $this->service->buildUrl($record);

        $this->assertStringStartsWith(
            config('verifactu.qr_urls.preprod.verifactu'),
            $url
        );
        $this->assertStringContainsString('nif=89890001K', $url);
        $this->assertStringContainsString('numserie=F2026-001', $url);
        $this->assertStringContainsString('importe=121.00', $url);
    }

    public function test_build_url_respects_no_verifactu_mode(): void
    {
        config(['verifactu.env' => 'preprod']);

        $record = $this->makeBillingRecord(UserSifConfig::MODE_NO_VERIFACTU);

        $url = $this->service->buildUrl($record);

        $this->assertStringStartsWith(
            config('verifactu.qr_urls.preprod.no_verifactu'),
            $url
        );
        $this->assertStringContainsString('ValidarQRNoVerifactu', $url);
    }

    public function test_generate_data_uri_returns_png_base64(): void
    {
        $record = $this->makeBillingRecord();

        $dataUri = $this->service->generateDataUri($record);

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
        $this->assertNotEmpty(base64_decode(substr($dataUri, 22), true));
    }

    public function test_should_show_qr_only_for_alta_and_sent_statuses(): void
    {
        $record = $this->makeBillingRecord();
        $record->document->update(['status' => Document::STATUS_DRAFT]);

        $this->assertFalse($this->service->shouldShowQr($record));

        $record->document->update(['status' => Document::STATUS_SENT]);
        $record->refresh();

        $this->assertTrue($this->service->shouldShowQr($record));
    }

    public function test_official_cotejo_probe_accepts_aeat_ok_payload(): void
    {
        Http::fake([
            '*' => Http::response([
                'status' => 'OK',
                'mensaje' => 'Encontrada',
                'respuesta' => ['resultado' => '00', 'nif' => '89890001K'],
            ], 200),
        ]);

        $probe = $this->service->probeOfficialCotejo();

        $this->assertTrue($probe['ok']);
        $this->assertSame('Encontrada', $probe['mensaje']);
    }

    public function test_invoice_pdf_includes_qr_when_billing_record_exists(): void
    {
        $record = $this->makeBillingRecord();
        $document = $record->document->fresh(['user', 'client', 'lineItems', 'billingRecord']);

        $pdf = app(PdfGeneratorService::class)->generateInvoicePdf($document);

        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertMatchesRegularExpression('/\/(Subtype\s*\/Image|XObject)/', $pdf);
    }

    public function test_invoice_view_shows_fiscal_badge_and_qr_legend(): void
    {
        $record = $this->makeBillingRecord();
        $document = $record->document->fresh(['user', 'client', 'lineItems']);
        $qrDataUri = $this->service->generateDataUri($record);

        $html = View::make('pdf.invoice', [
            'document' => $document,
            'logoDataUri' => null,
            'qrDataUri' => $qrDataUri,
            'isFiscal' => true,
        ])->render();

        $this->assertStringContainsString('Factura verificable en la sede electrónica de la AEAT', $html);
        $this->assertStringContainsString('VERI*FACTU', $html);
        $this->assertStringContainsString('badge-fiscal', $html);
        $this->assertStringContainsString($qrDataUri, $html);
        $this->assertStringContainsString('32mm', $html);
        $this->assertStringNotContainsString('#2563eb', $html);
        $this->assertStringNotContainsString('#1e40af', $html);
        $this->assertStringNotContainsString('background: #111111', $html);
    }

    public function test_invoice_view_keeps_proforma_disclaimer_without_billing_record(): void
    {
        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
        ]);

        $document = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-099',
            'status' => Document::STATUS_DRAFT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'draft-token',
        ]);

        $html = View::make('pdf.invoice', [
            'document' => $document->fresh(['user', 'client', 'lineItems']),
            'logoDataUri' => null,
            'qrDataUri' => null,
            'isFiscal' => false,
        ])->render();

        $this->assertStringContainsString('Documento proforma', $html);
        $this->assertStringContainsString('No válido como factura fiscal', $html);
        $this->assertStringNotContainsString('Factura verificable en la sede electrónica de la AEAT', $html);
    }

    private function makeBillingRecord(string $mode = UserSifConfig::MODE_VERIFACTU): BillingRecord
    {
        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
        $user->sifConfig->update([
            'mode' => $mode,
            'enabled' => true,
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Test',
            'email' => 'cliente@test.com',
            'tax_id' => 'B87654321',
        ]);

        $document = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-001',
            'status' => Document::STATUS_SENT,
            'issue_date' => now()->setDate(2026, 3, 15),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'token-f11',
        ]);

        return BillingRecord::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'hash_current' => hash('sha256', 'test'),
            'aeat_status' => BillingRecord::STATUS_PENDING,
        ])->fresh(['document.user.sifConfig']);
    }
}
