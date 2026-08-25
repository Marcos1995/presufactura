<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentEvent;
use App\Services\EmailService;
use App\Support\VerifactuSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicQuoteController extends Controller
{
    public function __construct(
        private EmailService $emailService,
    ) {}

    public function show(string $token): View
    {
        $with = ['client', 'lineItems', 'user'];
        if (VerifactuSchema::hasBillingRecordsTable()) {
            $with[] = 'billingRecord';
        }

        $document = Document::where('public_token', $token)
            ->with($with)
            ->firstOrFail();

        if ($document->isQuote()) {
            return $this->showQuote($document);
        }

        if ($document->isInvoice()) {
            return $this->showInvoice($document);
        }

        abort(404);
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

    public function claimPaid(string $token): RedirectResponse
    {
        $invoice = Document::where('public_token', $token)
            ->where('type', Document::TYPE_INVOICE)
            ->firstOrFail();

        if (! $invoice->canClaimPaid()) {
            return back()->with('error', 'No puedes indicar el pago en el estado actual de esta factura.');
        }

        $invoice->update(['status' => Document::STATUS_PAYMENT_PENDING]);

        $invoice->events()->create([
            'event_type' => DocumentEvent::CLIENT_CLAIMED_PAID,
        ]);

        try {
            $this->emailService->sendClientClaimedPaid($invoice);
        } catch (\Throwable) {
            // notification failure should not block client confirmation
        }

        return back()->with('status', 'Gracias. Hemos notificado al emisor que has realizado el pago.');
    }

    private function showQuote(Document $quote): View
    {
        if ($quote->status === Document::STATUS_SENT
            && $quote->valid_until
            && $quote->valid_until->copy()->startOfDay()->lt(now()->startOfDay())) {
            $quote->update(['status' => Document::STATUS_EXPIRED]);
            $quote->refresh();
        }

        return view('public.quote', compact('quote'));
    }

    private function showInvoice(Document $invoice): View
    {
        abort_unless(
            in_array($invoice->status, [
                Document::STATUS_SENT,
                Document::STATUS_EXPIRED,
                Document::STATUS_PAYMENT_PENDING,
                Document::STATUS_PAID,
            ], true),
            404
        );

        if ($invoice->status === Document::STATUS_SENT
            && $invoice->due_date
            && $invoice->due_date->copy()->startOfDay()->lt(now()->startOfDay())) {
            $invoice->update(['status' => Document::STATUS_EXPIRED]);
            $invoice->refresh();
        }

        return view('public.invoice', compact('invoice'));
    }
}
