<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventCreatedConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Event creation success!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event_created' // Correct view path
        );
    }


    public function build()
    {
        return $this->subject('Your Event Has Been Created!')
                    ->view('emails.event_created');
    }

    public function attachments(): array
    {
        return [];
    }
}
