<?php

namespace App\Console\Commands;

use App\Mail\FiscalReminderMail;
use App\Models\Company;
use App\Services\Fiscal\FiscalEstimateService;
use App\Services\Fiscal\FiscalObligationEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ProcessFiscalRemindersCommand extends Command
{
    protected $signature = 'presufactura:process-fiscal-reminders';

    protected $description = 'Email T-10 días con estimación orientativa del calendario fiscal';

    public function handle(FiscalObligationEngine $engine, FiscalEstimateService $estimates): int
    {
        $target = now()->addDays(10)->toDateString();
        $sent = 0;

        Company::query()
            ->whereNotNull('fiscal_profile_completed_at')
            ->where('fiscal_reminders_opt_in', true)
            ->with('user')
            ->orderBy('id')
            ->chunkById(50, function ($companies) use ($engine, $estimates, $target, &$sent) {
                foreach ($companies as $company) {
                    if (! $company->user) {
                        continue;
                    }
                    foreach ($engine->forCompany($company) as $row) {
                        if ($row['due'] !== $target) {
                            continue;
                        }
                        $cacheKey = "fiscal_reminder:{$company->id}:{$row['model']}:{$row['due']}";
                        if (! Cache::add($cacheKey, 1, now()->addDays(20))) {
                            continue;
                        }
                        $estimate = in_array($row['model'], ['303', '130'], true)
                            ? $estimates->forPeriod($company, $row['period'], $row['due'])
                            : null;
                        Mail::to($company->user->email)->send(
                            new FiscalReminderMail($company->user, $company, $row, $estimate)
                        );
                        $sent++;
                    }
                }
            });

        $this->info("Recordatorios fiscales enviados: {$sent}");

        return self::SUCCESS;
    }
}
