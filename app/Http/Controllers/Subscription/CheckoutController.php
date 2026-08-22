<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Mail\Subscription\SubscriptionActivatedMail;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

/**
 * Subscriptions belong to the supplier ACCOUNT, and the acting user is recorded
 * separately in selected_by_user_id (spec section 15.2).
 */
class CheckoutController extends Controller
{
    public function checkout(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $user    = $request->user();
        $account = $request->attributes->get('account') ?? $user->account;

        if (! $account) {
            return redirect()->route('supplier.pricing')
                ->with('error', 'No account context could be resolved for your user.');
        }

        /* ── Guard: Supplier capability must be approved first (spec rule 28) ── */
        if (! $account->hasActiveCapability('supplier')) {
            return redirect()->route('supplier.pending')
                ->with('error', 'Your Supplier application must be approved before you can subscribe.');
        }

        /* ── Guard: plan must be active ── */
        if (! $plan->is_active) {
            return redirect()->route('supplier.pricing')
                ->with('error', 'This plan is no longer available.');
        }

        /* ── Guard: only one current subscription per supplier account ── */
        if ($account->hasActiveSubscription()) {
            return redirect()->route('supplier.pricing')
                ->with('error', 'You already have an active subscription.');
        }

        /* ── Free plan: no Stripe round trip ── */
        if ($plan->isFree()) {
            if (! $account->isEligibleForFreePlan()) {
                return redirect()->route('supplier.pricing')
                    ->with('error', 'You are not eligible for the free plan.');
            }

            $subscription = DB::transaction(function () use ($account, $plan, $user) {
                $subscription = Subscription::create([
                    'supplier_account_id' => $account->id,
                    'plan_id'             => $plan->id,
                    'selected_by_user_id' => $user->id,
                    'provider'            => 'free',
                    'status'              => 'pending',
                ]);

                $subscription->activate();

                SubscriptionPayment::create([
                    'subscription_id'     => $subscription->id,
                    'supplier_account_id' => $account->id,
                    'provider'            => 'free',
                    'amount'              => 0,
                    'currency_code'       => $plan->effectiveCurrencyCode(),
                    'status'              => 'paid',
                    'paid_at'             => now(),
                ]);

                return $subscription;
            });

            $this->notifyActivated($subscription, $user->email);

            return redirect()->route('supplier.subscribe.success')
                ->with('subscription_id', $subscription->id);
        }

        /* ── Paid plan: Stripe Checkout ── */
        if (! $plan->stripe_price_id) {
            return redirect()->route('supplier.pricing')
                ->with('error', 'This plan is not yet available for purchase.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $subscription = Subscription::create([
            'supplier_account_id' => $account->id,
            'plan_id'             => $plan->id,
            'selected_by_user_id' => $user->id,
            'provider'            => 'stripe',
            'status'              => 'pending',
        ]);

        $session = StripeSession::create([
            'mode'                => 'subscription',
            'line_items'          => [[
                'price'    => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url'         => route('supplier.subscribe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'          => route('supplier.subscribe.cancel'),
            'client_reference_id' => (string) $subscription->id,
            'customer_email'      => $user->email,
            'metadata'            => [
                'subscription_id'     => $subscription->id,
                'supplier_account_id' => $account->id,
                'selected_by_user_id' => $user->id,
                'plan_id'             => $plan->id,
                'billing_type'        => $plan->billing_type,
            ],
        ]);

        // Stored for webhook reconciliation.
        $subscription->update(['provider_session_id' => $session->id]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $account = $request->attributes->get('account') ?? $request->user()?->account;

        $subscription = $account
            ? Subscription::forSupplierAccount($account->id)->with('plan')->latest()->first()
            : null;

        return view('supplier.checkout.success', compact('subscription'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        // A cancelled checkout fires no webhook, so the pending row simply stays
        // pending and never grants access.
        return redirect()->route('supplier.pricing')
            ->with('info', 'Payment was cancelled. You can try again any time.');
    }

    private function notifyActivated(Subscription $subscription, string $email): void
    {
        try {
            Mail::to($email)->send(new SubscriptionActivatedMail($subscription));
        } catch (\Exception $e) {
            Log::warning('SubscriptionActivatedMail failed: ' . $e->getMessage());
        }
    }
}
