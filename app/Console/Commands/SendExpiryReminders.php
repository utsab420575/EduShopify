<?php

namespace App\Console\Commands;

use App\Mail\Subscription\SubscriptionExpiringMail;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders {--days=7 : Days of notice}';

    protected $description = 'Email suppliers whose subscription expires soon, and expire the ones that already lapsed';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        /*
         * A subscription belongs to an account. The reminder goes to the user
         * who selected the plan, falling back to the account's primary owner.
         */
        $subscriptions = Subscription::expiringSoon($days)
            ->with(['plan', 'selectedBy', 'supplierAccount.primaryOwner'])
            ->get();

        $this->info("Found {$subscriptions->count()} subscription(s) expiring within {$days} day(s).");

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $recipient = $subscription->selectedBy?->email
                ?? $subscription->supplierAccount?->primaryOwner?->email;

            if (! $recipient) {
                $this->warn("  ! No recipient for subscription #{$subscription->id}");
                Log::warning('Expiry reminder skipped: no recipient', ['subscription_id' => $subscription->id]);

                continue;
            }

            try {
                Mail::to($recipient)->send(new SubscriptionExpiringMail($subscription));
                $this->line("  ✓ Sent to {$recipient}");
                $sent++;
            } catch (\Exception $e) {
                Log::warning("Failed to send expiry reminder to {$recipient}: " . $e->getMessage());
                $this->warn("  ✗ Failed: {$recipient}");
            }
        }

        $this->info("Sent {$sent} reminder(s).");

        // Lapse anything past its expiry. Trialing counts as current until it lapses.
        $expired = Subscription::whereIn('status', ['active', 'trialing'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $subscription) {
            $subscription->markExpired();
        }

        $this->info("Marked {$expired->count()} expired subscription(s).");

        return self::SUCCESS;
    }
}
