<?php

namespace App\Mail;

use App\Models\ServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceProvider $provider,
        public string $plainPassword,
        public bool $isReset = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isReset
            ? 'Your AR Provider App Password Has Been Reset'
            : 'Welcome to AR Water Tank Cleaners — Your Provider Login';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.provider-credentials');
    }
}
