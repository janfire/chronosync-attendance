<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminInvoiceProofUploaded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tenant;
    public $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, Invoice $invoice)
    {
        $this->tenant  = $tenant;
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🧾 New payment proof uploaded')
                    ->markdown('emails.admin.proof_uploaded')
                    ->with([
                        'tenant'  => $this->tenant,
                        'invoice' => $this->invoice,
                    ]);
    }
}
