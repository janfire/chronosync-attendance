<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $deviceInfo;
    public $ipAddress;

    /**
     * Create a new message instance.
     */
    public function __construct(string $token, string $deviceInfo, string $ipAddress)
    {
        $this->token = $token;
        $this->deviceInfo = $deviceInfo;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Approve Your Login',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.login-approval',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
