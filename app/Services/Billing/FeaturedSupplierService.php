<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\AccountBenefitOverride;
use App\Models\Subscription;
use App\Models\SubscriptionBenefit;
use App\Models\SubscriptionPlanBenefit;
use App\Models\SupplierProfile;
use App\Services\Account\PublicSupplierQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Which suppliers are "featured": the `featured_supplier` benefit resolves to
 * Enabled for their account (BenefitResolver — override → subscription
 * snapshot → plan → default) and they are publicly listed.
 *
 * Resolving every supplier on each page view would be slow, so:
 *   1. shortlist accounts that could have it — an effective override for the
 *      benefit, or an active subscription whose plan or snapshot includes it
 *      (everyone, if the benefit's default is Enabled);
 *   2. confirm each one with BenefitResolver (exact rules — an override set to
 *      Disabled removes a plan's featuring);
 *   3. keep only publicly listed suppliers.
 *
 * The resulting account ids are cached for CACHE_TTL seconds; flush() is
 * called whenever overrides, plan benefits or benefits change.
 */
class FeaturedSupplierService
{
    public const BENEFIT_CODE = 'featured_supplier';

    public const CACHE_KEY = 'featured_supplier_account_ids';

    public const CACHE_TTL = 600; // 10 minutes

    public function __construct(private readonly BenefitResolver $resolver) {}

    /** @return list<int> account ids of every featured, publicly listed supplier */
    public function featuredAccountIds(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->computeFeaturedAccountIds());
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function isFeatured(int $accountId): bool
    {
        return in_array($accountId, $this->featuredAccountIds(), true);
    }

    /**
     * Homepage "Featured Suppliers": featured suppliers by highest rating (up
     * to $max), topped up with the highest-rated other public suppliers so at
     * least $min cards show. Each item gets `is_featured_supplier`.
     */
    public function forHomepage(int $max = 20, int $min = 4): Collection
    {
        $ids = $this->featuredAccountIds();

        $featured = empty($ids) ? collect() : PublicSupplierQuery::base()
            ->with(SupplierProfile::publicCardRelations())
            ->whereIn('supplier_profiles.account_id', $ids)
            ->orderByDesc('rating')->orderBy('supplier_profiles.id')
            ->limit($max)
            ->get()
            ->each(function ($s) {
                $s->is_featured_supplier = true;
            });

        if ($featured->count() < $min) {
            $featured = $featured->concat(
                PublicSupplierQuery::base()
                    ->with(SupplierProfile::publicCardRelations())
                    ->whereNotIn('supplier_profiles.id', $featured->pluck('id'))
                    ->orderByDesc('rating')->orderBy('supplier_profiles.id')
                    ->limit($min - $featured->count())
                    ->get()
                    // A closure, not an arrow fn: returning false would stop each().
                    ->each(function ($s) {
                        $s->is_featured_supplier = false;
                    })
            );
        }

        return $featured->values();
    }

    /** @return list<int> */
    private function computeFeaturedAccountIds(): array
    {
        $benefit = SubscriptionBenefit::active()->where('code', self::BENEFIT_CODE)->first();
        if (! $benefit) {
            return [];
        }

        $publicAccountIds = fn () => PublicSupplierQuery::base();

        // 1. Shortlist.
        if ($benefit->fallbackValue()) {
            $candidateIds = $publicAccountIds()->pluck('supplier_profiles.account_id');
        } else {
            $viaOverride = AccountBenefitOverride::where('subscription_benefit_id', $benefit->id)
                ->effective()
                ->pluck('account_id');

            $plansWithBenefit = SubscriptionPlanBenefit::where('subscription_benefit_id', $benefit->id)->pluck('subscription_plan_id');

            $viaSubscription = Subscription::query()
                ->whereIn('status', ['active', 'trialing'])
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->where(fn ($q) => $q->whereIn('plan_id', $plansWithBenefit)
                    ->orWhereNotNull('features_snapshot->benefits->'.self::BENEFIT_CODE))
                ->pluck('supplier_account_id');

            $candidateIds = $publicAccountIds()
                ->whereIn('supplier_profiles.account_id', $viaOverride->merge($viaSubscription)->unique())
                ->pluck('supplier_profiles.account_id');
        }

        if ($candidateIds->isEmpty()) {
            return [];
        }

        // 2. Confirm with the resolver's exact rules.
        return Account::whereIn('id', $candidateIds->unique())
            ->with('capabilities.capabilityType')
            ->get()
            ->filter(fn (Account $account) => $this->resolver->enabled($account, self::BENEFIT_CODE))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
