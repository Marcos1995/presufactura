<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = [
            ['loc' => route('landing'), 'priority' => '1.0'],
            ['loc' => route('pricing'), 'priority' => '0.9'],
            ['loc' => route('help'), 'priority' => '0.8'],
            ['loc' => route('register'), 'priority' => '0.8'],
            ['loc' => route('legal.terminos'), 'priority' => '0.3'],
            ['loc' => route('legal.privacidad'), 'priority' => '0.3'],
            ['loc' => route('legal.cookies'), 'priority' => '0.3'],
        ];

        return response()
            ->view('sitemap', compact('pages'))
            ->header('Content-Type', 'application/xml');
    }
}
