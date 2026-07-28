<?php

namespace App\Providers;

use Illuminate\Foundation\Exceptions\Handler;
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
