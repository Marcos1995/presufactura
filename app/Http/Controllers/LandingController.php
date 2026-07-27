<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        if (Auth::check()) {
            return view('landing.index', ['loggedIn' => true]);
        }

        return view('landing.index', ['loggedIn' => false]);
    }
}
