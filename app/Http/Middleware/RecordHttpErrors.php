<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordHttpErrors
{
    public function __construct(
        private AnalyticsService $analytics,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->analytics->recordHttpStatus($request, $response->getStatusCode());

        return $response;
    }
}
