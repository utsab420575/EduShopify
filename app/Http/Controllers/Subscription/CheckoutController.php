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

        /* ── Guard: Supplier capability must exist (spec rule 28, relaxed) ──
         * Originally required the capability to already be `active`. The
         * Supplier Application wizard now collects plan selection as its
         * final step, before Admin review, so a `draft` capability must also
         * be allowed here — this is a deliberate override, not an oversight.
         * Post-approval callers are unaffected (their capability is already
         * `active`, which still satisfies this check). */
        if (! $account->hasCapability('supplier')) {
            return redirect()->route('supplier.pending')
                ->with('error', 'A Supplier application is required before you can subscribe.');
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

    public function success(Request $request, \App\Services\CapabilityApplicationService $capabilityService)
    {
        $account = $request->attributes->get('account') ?? $request->user()?->account;

        $subscription = $account
            ? Subscription::forSupplierAccount($account->id)->with('plan')->latest()->first()
            : null;

        // A supplier who paid as the final step of the application wizard
        // (capability still draft at this point) is now submitted for
        // Admin review. Already-approved accounts resubscribing here have a
        // non-draft capability, so this is a no-op for them.
        $justSubmitted = $account && $account->capabilityStatus('supplier') === 'draft';

        if ($justSubmitted) {
            $capabilityService->submit($account, 'supplier', $request->user());
        }

        // A dual buyer+supplier registration finishes Supplier onboarding
        // first — its shared fields were just copied into the still-draft
        // BuyerProfile (CapabilityApplicationService::submit()), so send the
        // user straight into the (now pre-filled) Buyer wizard instead of
        // this "payment successful" page.
        if ($justSubmitted && $account->buyerCapability?->status === 'draft') {
            return redirect()->route('buyer.onboarding.profile')
                ->with('success', "Supplier application submitted! Let's finish setting up your buyer account too.");
        }

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
