<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailService
{
    public function __construct(
        private PdfGeneratorService $pdfGenerator,
    ) {}

    public function sendInvoice(Document $document): void
    {
        $document->load(['user', 'company', 'client', 'lineItems']);
        $pdf = $this->pdfGenerator->generateInvoicePdf($document);
        $filename = 'factura-'.$document->number.'.pdf';

        Mail::send('emails.invoice-sent', ['document' => $document], function ($message) use ($document, $pdf, $filename) {
            $message->from(config('mail.from.address'), config('mail.from.name'))
                ->to($document->client->email, $document->client->name)
                ->subject('Factura '.$document->number.' — '.$document->issuerName())
                ->attachData($pdf, $filename, ['mime' => 'application/pdf']);
        });
    }

    public function sendQuote(Document $document): void
    {
        $document->load(['user', 'company', 'client', 'lineItems']);
        $pdf = $this->pdfGenerator->generateQuotePdf($document);
        $filename = 'presupuesto-'.$document->number.'.pdf';

        Mail::send('emails.quote-sent', ['document' => $document], function ($message) use ($document, $pdf, $filename) {
            $message->from(config('mail.from.address'), config('mail.from.name'))
                ->to($document->client->email, $document->client->name)
                ->subject('Presupuesto '.$document->number.' — '.$document->issuerName())
                ->attachData($pdf, $filename, ['mime' => 'application/pdf']);
        });
    }

    public function sendClientClaimedPaid(Document $document): void
    {
        $document->load(['user', 'company', 'client']);

        $panelUrl = url('/facturas/'.$document->id);

        Mail::send('emails.client-claimed-paid', [
            'document' => $document,
            'panelUrl' => $panelUrl,
        ], function ($message) use ($document) {
            $message->to($document->user->email, $document->user->name)
                ->subject('Tu cliente indica que ha pagado — Factura '.$document->number);
        });
    }

    public function sendClientReminder(Document $document, int $daysOverdue): void
    {
        $document->load(['user', 'company', 'client']);

        Mail::send('emails.client-reminder', [
            'document' => $document,
            'daysOverdue' => $daysOverdue,
        ], function ($message) use ($document) {
            $message->to($document->client->email, $document->client->name)
                ->subject('Recordatorio de pago — Factura '.$document->number);
        });
    }

    public function sendOwnerReminder(Document $document, int $daysOverdue): void
    {
        $document->load(['user', 'company', 'client']);

        $confirmUrl = URL::signedRoute('documents.confirm-paid', [
            'token' => $document->public_token,
        ]);

        $panelUrl = url('/facturas/'.$document->id);

        Mail::send('emails.owner-reminder', [
            'document' => $document,
            'daysOverdue' => $daysOverdue,
            'confirmUrl' => $confirmUrl,
            'panelUrl' => $panelUrl,
        ], function ($message) use ($document) {
            $message->to($document->user->email, $document->user->name)
                ->subject('¿Cobraste la factura '.$document->number.'?');
        });
    }

    public function recordReminderSent(Document $document, string $type, string $recipientEmail): void
    {
        $document->reminders()->create([
            'type' => $type,
            'recipient_email' => $recipientEmail,
            'sent_at' => now(),
        ]);

        $document->events()->create([
            'event_type' => DocumentEvent::REMINDER_SENT,
            'meta' => ['type' => $type],
        ]);
    }

    public function sendPaymentFailed(\App\Models\User $user): void
    {
        $subscriptionUrl = route('subscription.index');

        Mail::send('emails.payment-failed', [
            'user' => $user,
            'subscriptionUrl' => $subscriptionUrl,
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('Problema con el pago de tu suscripción Pro — PresuFactura');
        });
    }
}
