<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $userName) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cuenta en PresuFactura ha sido eliminada',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account-deleted');
    }
}
