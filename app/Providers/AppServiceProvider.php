<?php

namespace App\Providers;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (config('verifactu.software.name') && Cache::add('verifactu:startup_logged', true, now()->addDay())) {
                \App\Models\SifEvent::create([
                    'event_type' => \App\Models\SifEvent::TYPE_STARTUP,
                    'payload' => [
                        'version' => config('verifactu.software.version'),
                        'env' => config('verifactu.env'),
                    ],
                ]);
            }
        } catch (\Throwable) {
            // BD/caché no disponible (composer install, migraciones pendientes)
        }

        if (! filled(config('services.sentry.dsn'))) {
            return;
        }

        // Opcional: composer require sentry/sentry-laravel (ver composer.json → suggest)
        if (! class_exists(\Sentry\SentrySdk::class)) {
            return;
        }

        $this->app->afterResolving(Handler::class, function (Handler $handler): void {
            $handler->reportable(function (\Throwable $e): bool {
                \Sentry\captureException($e);

                return false;
            });
        });
    }
}
