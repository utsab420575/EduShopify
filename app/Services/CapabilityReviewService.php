<?php

namespace App\Services;

use App\Models\AccountCapability;
use App\Models\CapabilityApplicationHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CapabilityReviewService
{
    /**
     * Approve a pending application.
     * Allowed only when status = pending.
     */
    public function approve(AccountCapability $cap, User $admin, ?string $comment = null): void
    {
        // Delegate Supplier-specific approval rules to domain validator (Point 1)
        if ($cap->capabilityType?->code === 'supplier') {
            app(\App\Domain\Supplier\Validators\SupplierApprovalValidator::class)->validate($cap->account);
        }

        $cap = DB::transaction(function () use ($cap, $admin, $comment) {
            // Re-check status under a row lock inside the transaction — two
            // Admins approving/rejecting the same capability concurrently must
            // not both apply (admin_dashboard_workflow.md Part 18: "two Admins
            // approve same capability" / "capability approval with stale state").
            $cap = AccountCapability::whereKey($cap->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($cap, 'approve');

            $cap->update([
                'status'              => 'active',
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
                'activated_at'        => now(),
                'rejection_reason'    => null,
                'revision_reason'     => null,
            ]);

            // Update the existing attempt row (no new history row for approval)
            CapabilityApplicationHistory::where('account_capability_id', $cap->id)
                ->where('attempt_no', $cap->application_attempts)
                ->update([
                    'status'              => 'approved',
                    'reviewed_by_user_id' => $admin->id,
                    'review_comment'      => $comment,
                    'reviewed_at'         => now(),
                ]);

            return $cap;
        });

        // After commit: notify capability owner
        $this->notifyOwner($cap, 'approved', $comment);
    }

    /**
     * Request revisions on a pending application.
     * Reason is required.
     */
    public function requestRevision(AccountCapability $cap, User $admin, string $reason): void
    {
        $cap = DB::transaction(function () use ($cap, $admin, $reason) {
            $cap = AccountCapability::whereKey($cap->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($cap, 'request_revision');

            $cap->update([
                'status'              => 'revision_required',
                'revision_reason'     => $reason,
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
            ]);

            CapabilityApplicationHistory::where('account_capability_id', $cap->id)
                ->where('attempt_no', $cap->application_attempts)
                ->update([
                    'status'              => 'revision_required',
                    'reviewed_by_user_id' => $admin->id,
                    'review_comment'      => $reason,
                    'reviewed_at'         => now(),
                ]);

            return $cap;
        });

        $this->notifyOwner($cap, 'revision_requested', $reason);
    }

    /**
     * Reject a pending application.
     * Reason is required.
     */
    public function reject(AccountCapability $cap, User $admin, string $reason): void
    {
        $cap = DB::transaction(function () use ($cap, $admin, $reason) {
            $cap = AccountCapability::whereKey($cap->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($cap, 'reject');

            $cap->update([
                'status'              => 'rejected',
                'rejection_reason'    => $reason,
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
            ]);

            CapabilityApplicationHistory::where('account_capability_id', $cap->id)
                ->where('attempt_no', $cap->application_attempts)
                ->update([
                    'status'              => 'rejected',
                    'reviewed_by_user_id' => $admin->id,
                    'review_comment'      => $reason,
                    'reviewed_at'         => now(),
                ]);

            return $cap;
        });

        $this->notifyOwner($cap, 'rejected', $reason);
    }

    /**
     * Revert an active (approved) capability application back to pending.
     */
    public function undoApprove(AccountCapability $cap, User $admin, ?string $reason = null): void
    {
        DB::transaction(function () use ($cap, $admin, $reason) {
            $cap = AccountCapability::whereKey($cap->id)->lockForUpdate()->firstOrFail();

            if ($cap->status !== 'active') {
                throw new \RuntimeException(
                    "Cannot undo approval: capability [{$cap->capabilityType?->code}] is not active (current: {$cap->status})."
                );
            }

            $cap->update([
                'status'              => 'pending',
                'activated_at'        => null,
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
            ]);

            CapabilityApplicationHistory::where('account_capability_id', $cap->id)
                ->where('attempt_no', $cap->application_attempts)
                ->update([
                    'status'              => 'submitted',
                    'reviewed_by_user_id' => $admin->id,
                    'review_comment'      => $reason ?: 'Approval undone by administrator.',
                    'reviewed_at'         => now(),
                ]);
        });
    }

    /**
     * Guard: all review actions require status = pending.
     */
    private function assertPending(AccountCapability $cap, string $action): void
    {
        if ($cap->status !== 'pending') {
            throw new \RuntimeException(
                "Cannot [{$action}]: capability [{$cap->capabilityType?->code}] is not pending (current: {$cap->status})."
            );
        }
    }

    /**
     * Send notification to the account owner after commit.
     * Failures are logged but never surface to the admin.
     */
    private function notifyOwner(AccountCapability $cap, string $event, ?string $message): void
    {
        try {
            $account = $cap->account()->with('primaryOwner')->first();
            $owner   = $account?->primaryOwner;

            if (! $owner) return;

            match ($event) {
                'approved'           => $owner->notify(new \App\Notifications\BuyerApplicationApprovedNotification($cap)),
                'revision_requested' => $owner->notify(new \App\Notifications\BuyerRevisionRequestedNotification($cap, $message)),
                'rejected'           => $owner->notify(new \App\Notifications\BuyerApplicationRejectedNotification($cap, $message)),
                default              => null,
            };
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("CapabilityReviewService notify failed [{$event}]: " . $e->getMessage());
        }
    }
}
