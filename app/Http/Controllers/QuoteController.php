<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(): View
    {
        return view('quotes.index');
    }
}
