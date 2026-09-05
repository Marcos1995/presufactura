<?php

namespace Tests\Unit;

use App\Jobs\SubmitBillingRecordJob;
use App\Models\BillingRecord;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Services\Verifactu\VerifactuTransportFactory;
use App\Services\Verifactu\VerifactuTransportInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SubmitBillingRecordJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_marks_record_accepted_on_success(): void
    {
        [$record, $user] = $this->makePendingRecord();

        Cache::put("verifactu:cert_password:{$user->id}", 'secret', now()->addHour());

        $factory = $this->transportFactory($this->transport([
            'success' => true,
            'message' => 'Enviado correctamente',
            'csv' => 'CSV123',
        ]));

        $job = new SubmitBillingRecordJob($record->id);
        $job->handle($factory);

        $record->refresh();
        $this->assertSame(BillingRecord::STATUS_ACCEPTED, $record->aeat_status);
        $this->assertSame('CSV123', $record->aeat_response['csv']);
        $this->assertNotNull($record->sent_at);
    }

    public function test_marks_record_rejected_on_permanent_failure(): void
    {
        [$record, $user] = $this->makePendingRecord();

        Cache::put("verifactu:cert_password:{$user->id}", 'secret', now()->addHour());

        $factory = $this->transportFactory($this->transport([
            'success' => false,
            'message' => 'Certificado no configurado',
            'permanent' => true,
        ]));

        $job = new SubmitBillingRecordJob($record->id);
        $job->handle($factory);

        $record->refresh();
        $this->assertSame(BillingRecord::STATUS_REJECTED, $record->aeat_status);
    }

    public function test_throws_on_transient_failure_for_retry(): void
    {
        [$record, $user] = $this->makePendingRecord();

        Cache::put("verifactu:cert_password:{$user->id}", 'secret', now()->addHour());

        $factory = $this->transportFactory($this->transport([
            'success' => false,
            'message' => 'Timeout AEAT',
        ]));

        $job = new SubmitBillingRecordJob($record->id);

        $this->expectException(RuntimeException::class);
        $job->handle($factory);
    }

    public function test_failed_marks_record_rejected_after_retries(): void
    {
        [$record] = $this->makePendingRecord();

        $job = new SubmitBillingRecordJob($record->id);
        $job->failed(new RuntimeException('Timeout AEAT'));

        $record->refresh();
        $this->assertSame(BillingRecord::STATUS_REJECTED, $record->aeat_status);
        $this->assertSame('Timeout AEAT', $record->aeat_response['message']);
    }

    public function test_uses_stored_password_when_cache_empty(): void
    {
        [$record, $user] = $this->makePendingRecord();
        $user->sifConfig->storeCertPassword('secret');

        $transport = $this->createMock(VerifactuTransportInterface::class);
        $transport->expects($this->once())
            ->method('submit')
            ->with($this->anything(), 'secret')
            ->willReturn([
                'success' => true,
                'message' => 'Enviado',
                'csv' => 'CSV',
            ]);

        $job = new SubmitBillingRecordJob($record->id);
        $job->handle($this->transportFactory($transport));

        $record->refresh();
        $this->assertSame(BillingRecord::STATUS_ACCEPTED, $record->aeat_status);
    }

    /** @return array{0: BillingRecord, 1: User} */
    private function makePendingRecord(): array
    {
        $user = User::factory()->onboarded()->create(['tax_id' => '89890001K']);
        $this->activateVerifactuCertificate($user);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente',
            'email' => 'c@test.com',
        ]);

        $document = Document::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => Document::TYPE_INVOICE,
            'number' => 'F2026-001',
            'status' => Document::STATUS_SENT,
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 100,
            'vat_amount' => 21,
            'total' => 121,
            'public_token' => 'token-1',
        ]);

        $path = 'sif/'.$user->id.'/F2026-001_alta.xml';
        Storage::disk('local')->put($path, '<RegistroAlta/>');

        $record = BillingRecord::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'record_type' => BillingRecord::TYPE_ALTA,
            'xml_path' => $path,
            'hash_current' => str_repeat('A', 64),
            'aeat_status' => BillingRecord::STATUS_PENDING,
        ]);

        return [$record, $user];
    }

    /** @param  array<string, mixed>  $result */
    private function transport(array $result): VerifactuTransportInterface
    {
        $transport = $this->createMock(VerifactuTransportInterface::class);
        $transport->method('submit')->willReturn($result);

        return $transport;
    }

    private function transportFactory(VerifactuTransportInterface $transport): VerifactuTransportFactory
    {
        $factory = $this->createMock(VerifactuTransportFactory::class);
        $factory->method('for')->willReturn($transport);

        return $factory;
    }
}
