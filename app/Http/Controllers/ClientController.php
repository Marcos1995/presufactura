<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('clients.index');
    }
}
