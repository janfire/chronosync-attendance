<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkspaceWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $companyName;
    public string $adminName;
    public string $workspaceUrl;
    public string $adminEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $companyName, string $adminName, string $workspaceUrl, string $adminEmail)
    {
        $this->companyName = $companyName;
        $this->adminName = $adminName;
        $this->workspaceUrl = $workspaceUrl;
        $this->adminEmail = $adminEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ChronoSync! Your Workspace is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.workspace.welcome',
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
