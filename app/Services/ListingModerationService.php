<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\SupplierCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin-side listing moderation — the other half of the supplier's
 * submitForApproval() in ListingService. A listing only counts as "published"
 * (Listing::isPublished()) once approved here, with published_at set.
 */
class ListingModerationService
{
    public function approve(Listing $listing, User $admin): Listing
    {
        return DB::transaction(function () use ($listing, $admin) {
            // Re-check under a row lock — two Admins approving the same
            // pending listing must not both apply (admin_dashboard_workflow.md
            // Phase 18: "listing approve/reject stale state" / "duplicate
            // approval blocked").
            $listing = Listing::whereKey($listing->id)->lockForUpdate()->firstOrFail();

            if ($listing->approval_status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only a pending listing can be approved.']);
            }

            $listing->update([
                'approval_status'     => 'approved',
                'rejection_reason'    => null,
                'approved_by_user_id' => $admin->id,
                'approved_at'         => now(),
                'published_at'        => $listing->published_at ?? now(),
            ]);

            activity('moderation')->causedBy($admin)->performedOn($listing)->log('Listing approved');

            // supplier_categories represents "what a supplier is capable of
            // supplying," used by open_matching RFQ eligibility — an
            // approved listing confirms the supplier serves that category,
            // but the row must stay even if the listing is later removed.
            if ($listing->main_category_id) {
                SupplierCategory::firstOrCreate(
                    [
                        'supplier_account_id' => $listing->supplier_account_id,
                        'category_id'         => $listing->main_category_id,
                    ],
                    ['is_active' => true]
                );
            }

            return $listing->fresh();
        });
    }

    public function undoApprove(Listing $listing, User $admin): Listing
    {
        return DB::transaction(function () use ($listing, $admin) {
            $listing = Listing::whereKey($listing->id)->lockForUpdate()->firstOrFail();

            if ($listing->approval_status !== 'approved') {
                throw ValidationException::withMessages(['status' => 'Only an approved listing can have its approval reverted.']);
            }

            $listing->update([
                'approval_status'     => 'pending',
                'rejection_reason'    => null,
                'approved_by_user_id' => null,
                'approved_at'         => null,
                'published_at'        => null,
            ]);

            activity('moderation')->causedBy($admin)->performedOn($listing)->log('Listing approval reverted back to pending');

            return $listing->fresh();
        });
    }

    public function reject(Listing $listing, User $admin, string $reason): Listing
    {
        return DB::transaction(function () use ($listing, $admin, $reason) {
            $listing = Listing::whereKey($listing->id)->lockForUpdate()->firstOrFail();

            if ($listing->approval_status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only a pending listing can be rejected.']);
            }

            $listing->update([
                'approval_status'  => 'rejected',
                'rejection_reason' => $reason,
            ]);

            activity('moderation')->causedBy($admin)->performedOn($listing)->withProperties(['reason' => $reason])->log('Listing rejected');

            return $listing->fresh();
        });
    }

    /**
     * Admin may suspend a live listing at any time (spec §17.1) — distinct
     * from the supplier's own deactivate, which only they can reverse.
     */
    public function suspend(Listing $listing, string $reason): Listing
    {
        $listing->update([
            'is_active'        => false,
            'rejection_reason' => $reason,
        ]);

        return $listing->fresh();
    }
}
