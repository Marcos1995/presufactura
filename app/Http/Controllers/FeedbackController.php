<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->feedback()->exists()) {
            return back();
        }

        if ($request->boolean('dismiss')) {
            $user->feedback()->create(['dismissed' => true]);

            return back();
        }

        $data = $request->validate([
            'expected' => ['nullable', 'string', 'max:2000'],
            'difficult' => ['nullable', 'string', 'max:2000'],
            'used_before' => ['nullable', 'string', 'max:2000'],
            'missing_weekly' => ['nullable', 'string', 'max:2000'],
            'would_recommend' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        $user->feedback()->create([
            'dismissed' => false,
            'expected' => $data['expected'] ?? null,
            'difficult' => $data['difficult'] ?? null,
            'used_before' => $data['used_before'] ?? null,
            'missing_weekly' => $data['missing_weekly'] ?? null,
            'would_recommend' => $data['would_recommend'] ?? null,
        ]);

        return back()->with('status', 'Gracias por tu opinión. Nos ayuda a mejorar PresuFactura.');
    }
}
