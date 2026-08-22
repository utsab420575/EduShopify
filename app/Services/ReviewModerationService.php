<?php

namespace App\Services;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin moderation of buyer-submitted reviews. Publishing here is also the
 * only place supplier_profiles.rating / reviews_count get recalculated —
 * nothing else in the app writes those two columns.
 */
class ReviewModerationService
{
    public function publish(Review $review, User $admin): Review
    {
        if (! in_array($review->status, ['pending', 'flagged'], true)) {
            throw ValidationException::withMessages(['status' => 'Only a pending or flagged review can be published.']);
        }

        DB::transaction(function () use ($review, $admin) {
            $review->update([
                'status'              => 'published',
                'published_at'        => $review->published_at ?? now(),
                'moderated_by_user_id' => $admin->id,
                'moderation_reason'   => null,
            ]);

            $this->recalculateSupplierRating($review->supplier_account_id);
        });

        activity('moderation')->causedBy($admin)->performedOn($review)->log('Review published');

        return $review->fresh();
    }

    public function hide(Review $review, User $admin, string $reason): Review
    {
        DB::transaction(function () use ($review, $admin, $reason) {
            $wasPublished = $review->status === 'published';

            $review->update([
                'status'              => 'hidden',
                'moderated_by_user_id' => $admin->id,
                'moderation_reason'   => $reason,
            ]);

            if ($wasPublished) {
                $this->recalculateSupplierRating($review->supplier_account_id);
            }
        });

        activity('moderation')->causedBy($admin)->performedOn($review)->withProperties(['reason' => $reason])->log('Review hidden');

        return $review->fresh();
    }

    public function reject(Review $review, User $admin, string $reason): Review
    {
        if ($review->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending review can be rejected.']);
        }

        $review->update([
            'status'              => 'rejected',
            'moderated_by_user_id' => $admin->id,
            'moderation_reason'   => $reason,
        ]);

        return $review->fresh();
    }

    private function recalculateSupplierRating(int $supplierAccountId): void
    {
        $stats = Review::where('supplier_account_id', $supplierAccountId)
            ->where('status', 'published')
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();

        \App\Models\SupplierProfile::where('account_id', $supplierAccountId)->update([
            'rating'        => round((float) ($stats->avg_rating ?? 0), 2),
            'reviews_count' => (int) ($stats->cnt ?? 0),
        ]);
    }
}
