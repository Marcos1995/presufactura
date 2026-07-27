<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicQuoteController extends Controller
{
    public function show(string $token): View
    {
        $quote = Document::where('public_token', $token)
            ->where('type', Document::TYPE_QUOTE)
            ->with(['client', 'lineItems', 'user'])
            ->firstOrFail();

        if ($quote->status === Document::STATUS_SENT
            && $quote->valid_until
            && $quote->valid_until->copy()->startOfDay()->lt(now()->startOfDay())) {
            $quote->update(['status' => Document::STATUS_EXPIRED]);
            $quote->refresh();
        }

        return view('public.quote', compact('quote'));
    }

    public function accept(string $token): RedirectResponse
    {
        $quote = Document::where('public_token', $token)
            ->where('type', Document::TYPE_QUOTE)
            ->firstOrFail();

        if ($quote->valid_until && $quote->valid_until->copy()->startOfDay()->lt(now()->startOfDay())) {
            $quote->update(['status' => Document::STATUS_EXPIRED]);

            return back()->with('error', 'Este presupuesto ha caducado.');
        }

        if ($quote->status !== Document::STATUS_SENT) {
            return back()->with('error', 'Este presupuesto no puede aceptarse.');
        }

        $quote->update([
            'status' => Document::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        $quote->events()->create(['event_type' => DocumentEvent::ACCEPTED]);

        return back()->with('status', 'Presupuesto aceptado. Gracias.');
    }
}
