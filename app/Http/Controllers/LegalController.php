<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function terminos(): View
    {
        return view('legal.terminos');
    }

    public function privacidad(): View
    {
        return view('legal.privacidad');
    }

    public function cookies(): View
    {
        return view('legal.cookies');
    }
}
