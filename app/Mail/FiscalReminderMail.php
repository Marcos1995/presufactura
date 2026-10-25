<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FiscalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{model: string, name: string, period: string, due: string, url: string}  $obligation
     * @param  array<string, mixed>|null  $estimate
     */
    public function __construct(
        public User $user,
        public Company $company,
        public array $obligation,
        public ?array $estimate = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'En 10 días: '.$this->obligation['name'].' (orientativo)',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.fiscal-reminder');
    }
}
