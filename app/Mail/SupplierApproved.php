<?php

namespace App\Mail;

use App\Models\AccountCapability;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when Admin approves the Supplier capability. Approval lives on
 * account_capabilities, so that is what this mailable carries.
 */
class SupplierApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountCapability $capability) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Supplier Application has been Approved!',
        );
    }

    public function content(): Content
    {
        $account = $this->capability->account;

        return new Content(
            view: 'emails.supplier-approved',
            with: [
                'account'     => $account,
                'profile'     => $account?->supplierProfile,
                'companyName' => $account?->supplierProfile?->display_name
                    ?? $account?->display_name
                    ?? 'Supplier',
            ],
        );
    }
}
