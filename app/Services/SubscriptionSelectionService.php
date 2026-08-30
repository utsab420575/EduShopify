<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionSelectionService
{
    /**
     * Select a plan for a Supplier account.
     *
     * Deliberately relaxed from requiring an *active* (approved) capability
     * to requiring only that a supplier capability record exists: the
     * Supplier Application wizard now collects plan selection as its final
     * step, before Admin review, so the capability is still `draft` at this
     * point. Post-approval callers (e.g. the standalone plan-selection page
     * for an already-approved account) are unaffected — their capability is
     * already `active`, which still satisfies this check.
     */
    public function select(Account $account, SubscriptionPlan $plan, User $user): Subscription
    {
        if (! $account->hasCapability('supplier')) {
            throw ValidationException::withMessages([
                'capability' => 'Plan selection requires a Supplier capability.',
            ]);
        }

        if (! $plan->is_active) {
            throw ValidationException::withMessages([
                'plan' => 'Selected subscription plan is not active.',
            ]);
        }

        if ($plan->isFree()) {
            return $this->activateFree($account, $plan, $user);
        }

        return $this->createPendingStripeSubscription($account, $plan, $user);
    }

    /**
     * Activate a Free plan immediately.
     */
    public function activateFree(Account $account, SubscriptionPlan $plan, User $user): Subscription
    {
        return DB::transaction(function () use ($account, $plan, $user) {
            // Cancel/expire existing subscriptions if any
            Subscription::where('supplier_account_id', $account->id)
                ->whereIn('status', ['active', 'trialing', 'pending'])
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            $snapshot = $this->buildFeaturesSnapshot($plan);

            $sub = Subscription::create([
                'supplier_account_id' => $account->id,
                'plan_id'             => $plan->id,
                'selected_by_user_id' => $user->id,
                'provider'            => 'free',
                'plan_name_snapshot'  => $plan->name,
                'price_snapshot'      => 0.00,
                'features_snapshot'   => $snapshot,
                'status'              => 'active',
                'starts_at'           => now(),
                'current_period_start'=> now(),
                'current_period_end'  => now()->addYears(10), // free long term
                'auto_renew'          => true,
            ]);

            return $sub;
        });
    }

    /**
     * Create pending Stripe subscription record before checkout redirect.
     */
    private function createPendingStripeSubscription(Account $account, SubscriptionPlan $plan, User $user): Subscription
    {
        return DB::transaction(function () use ($account, $plan, $user) {
            // Mark older pending subscriptions as cancelled
            Subscription::where('supplier_account_id', $account->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            $snapshot = $this->buildFeaturesSnapshot($plan);

            return Subscription::create([
                'supplier_account_id' => $account->id,
                'plan_id'             => $plan->id,
                'selected_by_user_id' => $user->id,
                'provider'            => 'stripe',
                'plan_name_snapshot'  => $plan->name,
                'price_snapshot'      => $plan->price,
                'features_snapshot'   => $snapshot,
                'status'              => 'pending',
                'auto_renew'          => true,
            ]);
        });
    }

    /**
     * Build entitlement snapshot array.
     */
    private function buildFeaturesSnapshot(SubscriptionPlan $plan): array
    {
        return [
            'type'                   => $plan->type,
            'max_active_listings'    => $plan->max_active_listings,
            'max_products'           => $plan->max_products,
            'max_services'           => $plan->max_services,
            'max_team_members'       => $plan->max_team_members,
            'max_monthly_quotations' => $plan->max_monthly_quotations,
            'rfq_delay_minutes'      => $plan->rfq_delay_minutes,
            'has_rfq_notifications'  => $plan->has_rfq_notifications,
            'has_analytics'          => $plan->has_analytics,
            'has_verified_badge'     => $plan->has_verified_badge,
            'has_homepage_placement' => $plan->has_homepage_placement,
            'has_team_members'       => $plan->has_team_members,
            'features'               => $plan->features ?? [],
        ];
    }
}
