<?php

namespace App\Mail;

use App\Models\BugReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BugReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BugReport $report) {}

    public function envelope(): Envelope
    {
        $this->report->loadMissing('user');

        return new Envelope(
            replyTo: [new Address($this->report->user->email, $this->report->user->name)],
            subject: 'Aviso de fallo — '.$this->report->user->email,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.bug-report');
    }
}
