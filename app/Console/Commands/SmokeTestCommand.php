<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

class SmokeTestCommand extends Command
{
    protected $signature = 'presufactura:smoke-test';

    protected $description = 'Verifica DB, mail, storage y schedule para producción';

    public function handle(): int
    {
        $failed = false;

        $failed = ! $this->checkDatabase() || $failed;
        $failed = ! $this->checkMailConfig() || $failed;
        $failed = ! $this->checkStripeConfig() || $failed;
        $failed = ! $this->checkStorageWritable() || $failed;
        $failed = ! $this->checkScheduleRegistered() || $failed;

        if ($failed) {
            $this->error('Smoke test FALLIDO.');

            return self::FAILURE;
        }

        $this->info('Smoke test OK.');

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

    private function checkMailConfig(): bool
    {
        $mailer = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $from = config('mail.from.address');
        $username = config('mail.mailers.smtp.username');

        if (! $mailer || ! $host || ! $from) {
            $this->error('✗ Mail: faltan MAIL_MAILER, MAIL_HOST o MAIL_FROM_ADDRESS');

            return false;
        }

        if ($mailer === 'smtp' && empty($username)) {
            $this->error('✗ Mail: MAIL_USERNAME vacío');

            return false;
        }

        $password = config('mail.mailers.smtp.password');
        if ($mailer === 'smtp' && (empty($password) || str_contains((string) $password, 'tu_password'))) {
            $this->warn('⚠ Mail: MAIL_PASSWORD parece placeholder — revisa .env');
        }

        $this->line('✓ Mail config ('.$mailer.' @ '.$host.')');

        return true;
    }

    private function checkStripeConfig(): bool
    {
        $secret = config('services.stripe.secret');
        $key = config('services.stripe.key');
        $priceId = config('services.stripe.price_id');
        $ok = true;

        if (empty($key)) {
            $this->error('✗ Stripe: STRIPE_KEY vacío en .env');
            $ok = false;
        }
        if (empty($secret)) {
            $this->error('✗ Stripe: STRIPE_SECRET vacío en .env');
            $ok = false;
        }
        if (empty($priceId)) {
            $this->error('✗ Stripe: STRIPE_PRICE_ID vacío en .env');
            $ok = false;
        }

        if (! $ok) {
            $this->line('  → Edita laravel/.env y ejecuta: php artisan config:cache');

            return false;
        }

        if (! str_starts_with($priceId, 'price_')) {
            $this->error('✗ Stripe: STRIPE_PRICE_ID debe empezar por price_');

            return false;
        }

        if (app()->environment('production') && str_starts_with($secret, 'sk_test_')) {
            $this->error('✗ Stripe: sk_test_ en producción — usa claves live');

            return false;
        }

        if (app()->environment('production') && str_starts_with($key, 'pk_test_')) {
            $this->warn('⚠ Stripe: pk_test_ en producción');
        }

        $this->line('✓ Stripe config (price configurado)');

        return true;
    }

    private function checkStorageWritable(): bool
    {
        $paths = [
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('app/public'),
            base_path('bootstrap/cache'),
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

    private function checkScheduleRegistered(): bool
    {
        $events = Schedule::events();
        $found = false;

        foreach ($events as $event) {
            if (str_contains($event->command ?? '', 'presufactura:process-reminders')) {
                $found = true;
                break;
            }
        }

        if (! $found) {
            $this->error('✗ Schedule: presufactura:process-reminders no registrado en routes/console.php');

            return false;
        }

        $this->line('✓ Schedule: presufactura:process-reminders');

        return true;
    }
}
