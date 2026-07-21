<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\ServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderJobAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceProvider $provider,
        public Booking $booking,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Job Assigned — '.$this->booking->booking_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.provider-job-assigned');
    }
}
