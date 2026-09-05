<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'doc.limit' => \App\Http\Middleware\CheckDocumentLimit::class,
            'onboarding' => \App\Http\Middleware\CheckOnboarding::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->append(\App\Http\Middleware\ForceHttps::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ShareCurrentCompany::class);
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if ($response->getStatusCode() >= 400) {
                app(\App\Services\AnalyticsService::class)->recordHttpStatus(request(), $response->getStatusCode());
            }

            return $response;
        });
    })->create();
