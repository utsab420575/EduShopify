<?php

namespace App\Services\Catalog;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;

/**
 * Centralized public listing eligibility (frontend_workflow.md Part 9).
 *
 * A listing is publicly visible only when it is itself approved, active and
 * published, AND its owning Supplier account/capability remain publicly
 * eligible. Every public frontend query must go through here rather than
 * re-deriving these conditions ad hoc.
 */
class PublicListingQuery
{
    public static function base(): Builder
    {
        return Listing::query()
            ->published()
            ->whereHas('supplierAccount', function (Builder $account) {
                $account->where('status', 'active')
                    ->where('is_system_account', false)
                    ->whereHas('supplierCapability', fn (Builder $cap) => $cap->where('status', 'active'));
            });
    }

    public static function products(): Builder
    {
        return static::base()->products();
    }

    public static function services(): Builder
    {
        return static::base()->services();
    }

    public static function forSupplierAccount(int $supplierAccountId): Builder
    {
        return static::base()->where('supplier_account_id', $supplierAccountId);
    }

    public static function forCategory(int $categoryId): Builder
    {
        return static::base()->where(function (Builder $q) use ($categoryId) {
            $q->where('main_category_id', $categoryId)
                ->orWhereHas('categories', fn (Builder $c) => $c->where('categories.id', $categoryId));
        });
    }
}
