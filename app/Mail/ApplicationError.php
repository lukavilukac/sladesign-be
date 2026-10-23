<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ApplicationError extends Mailable
{
    public function __construct(
        public string $errorTitle,
        public string $level,
        public string $occurredAt,
        public string $viewerUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '['.config('app.env').'] '.strtoupper($this->level).': '.$this->errorTitle);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.application-error');
    }
}
