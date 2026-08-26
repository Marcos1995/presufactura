<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsEventController extends Controller
{
    public function store(Request $request, AnalyticsService $analytics): JsonResponse
    {
        $name = (string) $request->input('name');

        if (! in_array($name, AnalyticsEvent::CLIENT_ALLOWED, true)) {
            return response()->json(['ok' => false], 422);
        }

        $analytics->record($name, $request);

        return response()->json(['ok' => true]);
    }
}
