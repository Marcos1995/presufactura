<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('pdf', fn (Request $request) => Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip()));
        RateLimiter::for('mail-send', fn (Request $request) => Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip()));
        RateLimiter::for('public-doc', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('analytics', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
        RateLimiter::for('export', fn (Request $request) => Limit::perMinute(3)->by(optional($request->user())->id ?: $request->ip()));

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
