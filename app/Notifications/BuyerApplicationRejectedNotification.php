<?php

namespace App\Notifications;

use App\Models\AccountCapability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BuyerApplicationRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Update on Your Buyer Application — Edushopify')
            ->greeting("Hello {$notifiable->name},")
            ->line('Thank you for your interest in becoming a Buyer on Edushopify.')
            ->line('After reviewing your application, we are unable to approve it at this time.');

        if ($this->reason) {
            $mail->line("**Reason:**\n{$this->reason}");
        }

        return $mail
            ->line('If you believe this decision was made in error, please contact our support team.')
            ->action('Contact Support', url('/'))
            ->salutation('The Edushopify Team');
    }
}
