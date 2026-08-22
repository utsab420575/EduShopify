<?php

namespace App\Notifications;

use App\Models\AccountCapability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BuyerApplicationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AccountCapability $capability) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Your Buyer Application Has Been Approved — Edushopify')
            ->greeting("Hello {$notifiable->name},")
            ->line('Great news! Your Buyer application on **Edushopify** has been **approved**.')
            ->line('You now have full access to the Buyer marketplace — issue RFQs, compare quotes, and award suppliers.')
            ->action('Go to Buyer Dashboard', url('/buyer'))
            ->line('Welcome aboard, and thank you for joining Edushopify!');
    }
}
