<?php

namespace App\Mail;

use App\Models\AttendanceException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttendanceExceptionCreated extends Mailable
{
    use Queueable, SerializesModels;

    public AttendanceException $exception;

    /**
     * Create a new message instance.
     */
    public function __construct(AttendanceException $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Attendance Request: ' . ucfirst(str_replace('_', ' ', $this->exception->type)))
                    ->view('emails.attendance_exception_created')
                    ->with(['exception' => $this->exception]);
    }
}
