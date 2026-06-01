<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $contenu,
        public string $sujet = 'Notification Patrimoine'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->sujet,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'reports.notification', // On va créer cette vue simple
            with: ['contenu' => $this->contenu],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
