<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Deliberately NOT named $message: Laravel injects the
     * Illuminate\Mail\Message instance into every mail view under that name,
     * which silently shadows a Mailable property called the same thing. The
     * view then reads properties off the transport object instead of the model.
     */
    public function __construct(
        public ContactMessage $contact,
        public ?string $subjectLine = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine ?: 'New GoCare contact message');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
