<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;



class UserDeletionAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Data of the user being deleted.
     * We store this as an array to prevent serialization issues if the user is deleted before the email is sent.
     */
    public array $userData;

    /**
     * Name of the admin performing the action.
     */
    public string $adminName;

    /**
     * Create a new message instance.
     */
    public function __construct(array $userData, string $adminName)
    {
        $this->userData = $userData;
        $this->adminName = $adminName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'System Alert: User Deletion Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.users.deleted',
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
