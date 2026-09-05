<?php

namespace App\Jobs;

use App\Models\BillingRecord;
use App\Models\BillingSubmissionAttempt;
use App\Services\Verifactu\VerifactuTransportFactory;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SubmitBillingRecordJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $billingRecordId,
    ) {}

    public function uniqueId(): string
    {
        return 'verifactu-submit-'.$this->billingRecordId;
    }

    public function handle(VerifactuTransportFactory $factory): void
    {
        $record = BillingRecord::find($this->billingRecordId);
        if (! $record || in_array($record->aeat_status, [BillingRecord::STATUS_ACCEPTED, BillingRecord::STATUS_REJECTED], true)) {
            return;
        }

        $record->loadMissing(['user.sifConfig', 'company.sifConfig']);
        $sifConfig = $record->company?->sifConfig ?? $record->user->sifConfig;

        if (! $sifConfig?->enabled || ! $sifConfig->hasValidCertificate()) {
            Log::info('Veri*Factu: envío omitido (sin certificado válido)', [
                'billing_record_id' => $record->id,
            ]);

            return;
        }

        $password = $sifConfig->certPassword();
        if (! $password) {
            Log::info('Veri*Factu: envío omitido (contraseña certificado no disponible)', [
                'billing_record_id' => $record->id,
            ]);

            return;
        }

        $result = $factory->for($record)->submit($record, $password);
        $this->storeAttempt($record, $result);

        if ($result['success'] ?? false) {
            $record->update([
                'aeat_status' => BillingRecord::STATUS_ACCEPTED,
                'aeat_response' => $result,
                'sent_at' => now(),
            ]);

            return;
        }

        if ($result['permanent'] ?? false) {
            $this->markRejected($record, $result);

            return;
        }

        $record->increment('retry_count');
        $record->update([
            'aeat_status' => BillingRecord::STATUS_ERROR,
            'aeat_response' => $result,
        ]);

        throw new RuntimeException($result['message'] ?? 'Error de envío AEAT');
    }

    public function failed(?\Throwable $exception): void
    {
        $record = BillingRecord::find($this->billingRecordId);
        if (! $record || $record->aeat_status === BillingRecord::STATUS_ACCEPTED) {
            return;
        }

        $this->markRejected($record, [
            'success' => false,
            'message' => $exception?->getMessage() ?? 'Error de envío AEAT tras reintentos',
        ]);

        Log::warning('Veri*Factu: envío rechazado tras reintentos', [
            'billing_record_id' => $record->id,
            'message' => $exception?->getMessage(),
        ]);
    }

    /** @param array<string, mixed> $result */
    private function markRejected(BillingRecord $record, array $result): void
    {
        $record->update([
            'aeat_status' => BillingRecord::STATUS_REJECTED,
            'aeat_response' => $result,
            'sent_at' => now(),
        ]);

        if (! ($result['success'] ?? false)) {
            Log::warning('Veri*Factu: envío rechazado', [
                'billing_record_id' => $record->id,
                'message' => $result['message'] ?? 'unknown',
            ]);
        }
    }

    /** @param array<string, mixed> $result */
    private function storeAttempt(BillingRecord $record, array $result): void
    {
        if (! class_exists(BillingSubmissionAttempt::class)) {
            return;
        }

        try {
            BillingSubmissionAttempt::create([
                'billing_record_id' => $record->id,
                'status' => ($result['success'] ?? false) ? 'accepted' : 'error',
                'permanent' => (bool) ($result['permanent'] ?? false),
                'idempotency_key' => $record->idempotency_key,
                'response' => [
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? null,
                    'csv' => $result['csv'] ?? null,
                    'sandbox' => $result['sandbox'] ?? false,
                ],
                'error_message' => ($result['success'] ?? false) ? null : ($result['message'] ?? null),
            ]);
        } catch (\Throwable) {
            // tabla de intentos opcional si la migración no se ha ejecutado
        }
    }
}
