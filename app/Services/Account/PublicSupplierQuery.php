<?php

namespace App\Services\Account;

use App\Models\SupplierProfile;
use Illuminate\Database\Eloquent\Builder;

/**
 * Centralized public Supplier eligibility (frontend_workflow.md Part 10).
 *
 * A Supplier storefront/card is publicly visible only when the owning account
 * is active and its Supplier capability is active. Pending, rejected,
 * suspended and deleted accounts must never resolve through this query.
 */
class PublicSupplierQuery
{
    public static function base(): Builder
    {
        return SupplierProfile::query()
            ->whereNotNull('profile_completed_at')
            ->whereNotNull('slug')
            ->whereHas('account', function (Builder $account) {
                $account->where('status', 'active')
                    ->where('is_system_account', false)
                    ->whereHas('supplierCapability', fn (Builder $cap) => $cap->where('status', 'active'));
            });
    }

    public static function bySlug(string $slug): Builder
    {
        return static::base()->where('slug', $slug);
    }
}
