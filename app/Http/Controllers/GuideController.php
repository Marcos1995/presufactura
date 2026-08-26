<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GuideController extends Controller
{
    public function index(): View
    {
        return view('guides.index', [
            'guides' => config('guides'),
        ]);
    }

    public function show(string $slug): View
    {
        $guide = config('guides.'.$slug);
        abort_unless(is_array($guide), 404);

        return view('guides.show', [
            'slug' => $slug,
            'guide' => $guide,
            'guides' => config('guides'),
        ]);
    }
}
