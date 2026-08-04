<?php

namespace App\Jobs;

use App\Models\BillingRecord;
use App\Services\Verifactu\AeatSoapClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

        $password = Cache::get("verifactu:cert_password:{$record->user_id}");
        if (! $password) {
            Log::info('Veri*Factu: envío omitido (contraseña certificado no en caché)', [
                'billing_record_id' => $record->id,
            ]);

            return;
        }

        $result = $client->submit($record, $password);

        $record->update([
            'aeat_status' => $result['success'] ? BillingRecord::STATUS_ACCEPTED : BillingRecord::STATUS_REJECTED,
            'aeat_response' => $result,
            'sent_at' => now(),
        ]);

        if (! $result['success']) {
            Log::warning('Veri*Factu: envío rechazado', [
                'billing_record_id' => $record->id,
                'message' => $result['message'] ?? 'unknown',
            ]);
        }
    }
}
