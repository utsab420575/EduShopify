<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\AccountBenefitOverride;
use App\Models\SubscriptionBenefit;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Central benefit resolution — the one place that answers "what does this
 * supplier account get?". For every active benefit, highest priority first:
 *
 *   1. Active admin override   (account_benefit_overrides, in effect now —
 *                               replaces the value, false and 0 included)
 *   2. Subscription snapshot   (subscriptions.features_snapshot['benefits'],
 *                               captured at subscribe / renewal / plan change)
 *   3. Plan benefit            (subscription_plan_benefits of the current plan)
 *   4. Default benefit value   (subscription_benefits.default_value)
 *
 * 2 and 3 only apply while the account has an active subscription; an
 * override applies even without one. An account that isn't eligible
 * (account not active, or no active supplier capability) gets only the
 * defaults — overrides included. Expired overrides are skipped by the query,
 * so the subscription value comes back on its own.
 *
 * Results are memoised per account for the request; call forget() after
 * changing an account's overrides or subscription.
 *
 * Usage: app(BenefitResolver::class)->value($account, 'max_active_listings')
 */
class BenefitResolver
{
    /** @var array<int, Collection<string, ResolvedBenefit>> */
    private array $resolved = [];

    private ?Collection $benefits = null;

    /** @return Collection<string, ResolvedBenefit> keyed by benefit code */
    public function forAccount(Account $account): Collection
    {
        return $this->resolved[$account->id] ??= $this->resolve($account);
    }

    /** Effective value of one benefit ($fallback when the code is unknown or inactive). */
    public function value(Account $account, string $code, mixed $fallback = null): mixed
    {
        return $this->forAccount($account)->get($code)?->value ?? $fallback;
    }

    /** Effective value as a yes/no (for boolean benefits). */
    public function enabled(Account $account, string $code): bool
    {
        return (bool) $this->value($account, $code, false);
    }

    public function isEligible(Account $account): bool
    {
        return $account->status === 'active' && $account->hasActiveCapability('supplier');
    }

    /**
     * code => value for a plan's active benefits — what a new subscription
     * snapshots into features_snapshot['benefits'].
     *
     * @return array<string, bool|int|float|string|null>
     */
    public function planBenefitMap(SubscriptionPlan $plan): array
    {
        return $plan->planBenefits()
            ->with('benefit')
            ->get()
            ->filter(fn ($row) => $row->benefit?->is_active)
            ->mapWithKeys(fn ($row) => [$row->benefit->code => $row->benefit->normalize($row->value)])
            ->all();
    }

    public function forget(?Account $account = null): void
    {
        if ($account) {
            unset($this->resolved[$account->id]);
        } else {
            $this->resolved = [];
        }
    }

    /** @return Collection<string, ResolvedBenefit> */
    private function resolve(Account $account): Collection
    {
        $benefits = $this->benefits ??= SubscriptionBenefit::active()->ordered()->get();

        if (! $this->isEligible($account)) {
            return $benefits->mapWithKeys(fn (SubscriptionBenefit $b) => [
                $b->code => new ResolvedBenefit($b, $b->fallbackValue(), ResolvedBenefit::SOURCE_INELIGIBLE),
            ]);
        }

        $subscription = $account->activeSubscription()->with('plan.planBenefits')->first();
        $subscription = $subscription?->isActive() ? $subscription : null;

        $snapshot = $subscription?->features_snapshot['benefits'] ?? null;
        $snapshot = is_array($snapshot) ? $snapshot : null;

        $planValues = $subscription?->plan?->planBenefits->keyBy('subscription_benefit_id') ?? collect();

        // Latest effective override per benefit.
        $overrides = AccountBenefitOverride::where('account_id', $account->id)
            ->effective()
            ->orderByDesc('id')
            ->get()
            ->unique('subscription_benefit_id')
            ->keyBy('subscription_benefit_id');

        return $benefits->mapWithKeys(function (SubscriptionBenefit $benefit) use ($subscription, $snapshot, $planValues, $overrides) {
            $hasSnapshot = $subscription !== null && $snapshot !== null && array_key_exists($benefit->code, $snapshot);
            $snapshotValue = $hasSnapshot ? $benefit->normalize($snapshot[$benefit->code]) : null;

            $planRow = $subscription !== null ? $planValues->get($benefit->id) : null;
            $planValue = $planRow ? $benefit->normalize($planRow->value) : null;

            $override = $overrides->get($benefit->id);

            [$value, $source] = match (true) {
                $override !== null => [$benefit->normalize($override->value), ResolvedBenefit::SOURCE_OVERRIDE],
                $hasSnapshot => [$snapshotValue, ResolvedBenefit::SOURCE_SNAPSHOT],
                $planRow !== null => [$planValue, ResolvedBenefit::SOURCE_PLAN],
                default => [$benefit->fallbackValue(), ResolvedBenefit::SOURCE_DEFAULT],
            };

            return [$benefit->code => new ResolvedBenefit(
                $benefit, $value, $source, $override,
                $hasSnapshot, $snapshotValue,
                $planRow !== null, $planValue,
            )];
        });
    }
}
