<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(AnalyticsService $analytics): View
    {
        $analytics->record(AnalyticsEvent::LANDING_VIEW);

        $landingFaqs = collect(config('faq'))->where('landing', true)->values()->all();

        return view('landing.index', [
            'loggedIn' => Auth::check(),
            'landingFaqs' => $landingFaqs,
        ]);
    }
}
