<?php

namespace App\Mail;

use App\Models\AccountCapability;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when Admin rejects the Supplier capability. The reason comes from
 * account_capabilities.rejection_reason.
 */
class SupplierRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountCapability $capability) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your Supplier Application',
        );
    }

    public function content(): Content
    {
        $account = $this->capability->account;

        return new Content(
            view: 'emails.supplier-rejected',
            with: [
                'account'     => $account,
                'profile'     => $account?->supplierProfile,
                'companyName' => $account?->supplierProfile?->display_name
                    ?? $account?->display_name
                    ?? 'Supplier',
                'reason'      => $this->capability->rejection_reason,
            ],
        );
    }
}
