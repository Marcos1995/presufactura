<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class FunnelReportCommand extends Command
{
    protected $signature = 'presufactura:funnel {--days=30}';

    protected $description = 'Muestra el embudo de conversión (sin datos personales)';

    public function handle(AnalyticsService $analytics): int
    {
        $from = now()->subDays((int) $this->option('days'));

        $this->info('Embudo desde '.$from->toDateString());
        $this->table(['Paso', 'Eventos'], collect($analytics->funnelCounts($from))->map(fn ($n, $k) => [$k, $n])->values()->all());
        $this->info('Tráfico');
        $this->table(['Origen', 'Eventos'], collect($analytics->trafficSplit($from))->map(fn ($n, $k) => [$k, $n])->values()->all());

        return self::SUCCESS;
    }
}
