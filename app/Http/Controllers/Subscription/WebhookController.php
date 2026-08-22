<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Mail\Subscription\SubscriptionActivatedMail;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * V4.3 stores provider identifiers in provider_session_id /
 * provider_subscription_id / provider_customer_id, not stripe_* columns.
 */
class WebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook.secret');

        /* ── Verify signature ── */
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);

            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        /* ── Route events ── */
        match ($event->type) {
            'checkout.session.completed'    => $this->handleCheckoutCompleted($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'invoice.payment_failed'        => $this->handlePaymentFailed($event),
            default                         => null,
        };

        return response('OK', 200);
    }

    /* ── Event handlers ── */

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;

        $subscription = Subscription::where('provider_session_id', $session->id)
            ->orWhere('id', $session->client_reference_id)
            ->first();

        if (! $subscription) {
            Log::error('Stripe webhook: no subscription found for session', ['session_id' => $session->id]);

            return;
        }

        // Idempotency guard — Stripe retries.
        if (in_array($subscription->status, ['active', 'trialing'], true)) {
            return;
        }

        $subscription->activate(
            $session->subscription ?? null,
            $session->customer ?? null
        );

        $this->recordPayment($subscription, $session);

        try {
            $subscription->load('plan', 'selectedBy');

            if ($email = $subscription->selectedBy?->email) {
                Mail::to($email)->send(new SubscriptionActivatedMail($subscription));
            }
        } catch (\Exception $e) {
            Log::warning('SubscriptionActivatedMail failed: ' . $e->getMessage());
        }

        Log::info('Stripe webhook: subscription activated', [
            'subscription_id'     => $subscription->id,
            'supplier_account_id' => $subscription->supplier_account_id,
        ]);
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $stripeSubscription = $event->data->object;

        $subscription = Subscription::where('provider_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->cancel('Cancelled at the payment provider.');
            Log::info('Stripe webhook: subscription cancelled', ['subscription_id' => $subscription->id]);
        }
    }

    private function handlePaymentFailed(Event $event): void
    {
        $invoice = $event->data->object;

        if (! $invoice->subscription) {
            return;
        }

        $subscription = Subscription::where('provider_subscription_id', $invoice->subscription)->first();

        if (! $subscription) {
            return;
        }

        $subscription->markExpired();

        SubscriptionPayment::create([
            'subscription_id'     => $subscription->id,
            'supplier_account_id' => $subscription->supplier_account_id,
            'provider'            => 'stripe',
            'provider_invoice_id' => $invoice->id ?? null,
            'amount'              => isset($invoice->amount_due) ? $invoice->amount_due / 100 : 0,
            'currency_code'       => strtoupper($invoice->currency ?? 'USD'),
            'status'              => 'failed',
            'failure_reason'      => 'Invoice payment failed at the payment provider.',
        ]);

        Log::info('Stripe webhook: subscription expired due to payment failure', [
            'subscription_id' => $subscription->id,
        ]);
    }

    private function recordPayment(Subscription $subscription, object $session): void
    {
        $amount = isset($session->amount_total) ? $session->amount_total / 100 : (float) $subscription->price_snapshot;

        SubscriptionPayment::create([
            'subscription_id'     => $subscription->id,
            'supplier_account_id' => $subscription->supplier_account_id,
            'provider'            => 'stripe',
            'provider_payment_id' => $session->payment_intent ?? null,
            'provider_invoice_id' => $session->invoice ?? null,
            'amount'              => $amount,
            'currency_code'       => strtoupper($session->currency ?? 'USD'),
            'status'              => 'paid',
            'paid_at'             => now(),
        ]);
    }
}
