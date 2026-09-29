<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Webinar;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $recipient,
        public ?Webinar $webinar,
        public string $mailSubject,
        public string $mailMessage,
        public ?string $attachmentPath = null,
        public ?string $attachmentName = null,
        public ?string $attachmentMime = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.communication');
    }

    public function attachments(): array
    {
        if (! $this->attachmentPath) {
            return [];
        }

        return [Attachment::fromStorageDisk('public', $this->attachmentPath)
            ->as($this->attachmentName ?: basename($this->attachmentPath))
            ->withMime($this->attachmentMime ?: 'application/octet-stream')];
    }
}
