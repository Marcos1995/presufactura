<?php

namespace App\Jobs;

use App\Models\BillingRecord;
use App\Services\Verifactu\AeatSoapClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SubmitBillingRecordJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $billingRecordId,
    ) {}

    public function handle(AeatSoapClient $client): void
    {
        $record = BillingRecord::find($this->billingRecordId);
        if (! $record || $record->aeat_status !== BillingRecord::STATUS_PENDING) {
            return;
        }

        $record->loadMissing(['user.sifConfig']);
        $sifConfig = $record->user->sifConfig;

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

        $result = $client->submit($record, $password);

        if ($result['success']) {
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

        throw new RuntimeException($result['message'] ?? 'Error de envío AEAT');
    }

    public function failed(?\Throwable $exception): void
    {
        $record = BillingRecord::find($this->billingRecordId);
        if (! $record || $record->aeat_status !== BillingRecord::STATUS_PENDING) {
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
}
