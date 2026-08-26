<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\View\View;

class FunnelController extends Controller
{
    public function index(AnalyticsService $analytics): View
    {
        abort_unless(auth()->user()?->isDemoAdmin(), 403);

        $from = now()->subDays(30);

        return view('funnel.index', [
            'funnel' => $analytics->funnelCounts($from),
            'traffic' => $analytics->trafficSplit($from),
            'from' => $from,
        ]);
    }
}
