<?php

namespace App\Http\Controllers;

use App\Services\AdminOverviewService;
use Illuminate\View\View;

class FunnelController extends Controller
{
    public function index(AdminOverviewService $overview): View
    {
        abort_unless(auth()->user()?->isDemoAdmin(), 403);

        return view('funnel.index', $overview->build());
    }
}
