<?php

namespace App\Notifications;

use App\Models\AccountCapability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BuyerRevisionRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly AccountCapability $capability,
        public readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Action Required: Your Buyer Application Needs Revision — Edushopify')
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for applying to become a Buyer on Edushopify.')
            ->line('Our team has reviewed your application and requires some changes before we can approve it.');

        if ($this->reason) {
            $mail->line("**Reviewer Notes:**\n{$this->reason}");
        }

        return $mail
            ->action('Update My Application', url('/buyer/onboarding/profile'))
            ->line('Once you have made the changes, you can resubmit your application for review.');
    }
}
