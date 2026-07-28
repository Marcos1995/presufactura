<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;

class HealthCheckCommand extends Command
{
    protected $signature = 'presufactura:health-check';

    protected $description = 'Comprueba DB, storage y cola (exit 1 si falla — usable desde cron/uptime)';

    public function handle(): int
    {
        $failed = false;

        $failed = ! $this->checkDatabase() || $failed;
        $failed = ! $this->checkStorage() || $failed;
        $failed = ! $this->checkQueue() || $failed;

        if ($failed) {
            $this->error('Health check FALLIDO.');

            return self::FAILURE;
        }

        $this->info('Health check OK.');

        return self::SUCCESS;
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $this->line('✓ Base de datos');

            return true;
        } catch (\Throwable $e) {
            $this->error('✗ Base de datos: '.$e->getMessage());

            return false;
        }
    }

    private function checkStorage(): bool
    {
        $paths = [
            storage_path('logs'),
            storage_path('app/public'),
        ];

        foreach ($paths as $path) {
            if (! File::isDirectory($path)) {
                $this->error('✗ Storage: no existe '.$path);

                return false;
            }

            if (! is_writable($path)) {
                $this->error('✗ Storage: sin permiso de escritura '.$path);

                return false;
            }
        }

        $this->line('✓ Storage writable');

        return true;
    }

    private function checkQueue(): bool
    {
        $driver = config('queue.default');

        if ($driver === 'sync') {
            $this->line('✓ Cola (sync)');

            return true;
        }

        try {
            Queue::connection()->size();
            $this->line('✓ Cola ('.$driver.')');

            return true;
        } catch (\Throwable $e) {
            $this->error('✗ Cola: '.$e->getMessage());

            return false;
        }
    }
}
