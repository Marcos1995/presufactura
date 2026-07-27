<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $landingFaqs = collect(config('faq'))->where('landing', true)->values()->all();

        return view('landing.index', [
            'loggedIn' => Auth::check(),
            'landingFaqs' => $landingFaqs,
        ]);
    }
}
