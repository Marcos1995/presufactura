<?php

namespace App\Console\Commands;

use App\Jobs\SubmitBillingRecordJob;
use App\Models\BillingRecord;
use Illuminate\Console\Command;

class VerifactuRetryFailedCommand extends Command
{
    protected $signature = 'presufactura:verifactu-retry-failed {--user= : ID de usuario}';

    protected $description = 'Reintenta envíos AEAT rechazados o pendientes';

    public function handle(): int
    {
        $query = BillingRecord::query()
            ->where('aeat_status', BillingRecord::STATUS_PENDING);

        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        $records = $query->get();
        $count = 0;

        foreach ($records as $record) {
            $record->update(['aeat_status' => BillingRecord::STATUS_PENDING]);
            SubmitBillingRecordJob::dispatch($record->id);
            $count++;
            $this->line("Reencolado registro #{$record->id} (doc {$record->document_id})");
        }

        $this->info("{$count} registro(s) reencolado(s).");

        return self::SUCCESS;
    }
}
