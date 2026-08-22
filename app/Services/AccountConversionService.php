<?php

namespace App\Services;

use App\Models\AccountConversionRequest;
use App\Models\AccountTypeChange;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin review of individual → organization conversion requests (spec §10.1).
 * Approval is a one-way door — rollback to individual is never allowed.
 */
class AccountConversionService
{
    public function approve(AccountConversionRequest $request, User $admin): AccountConversionRequest
    {
        DB::transaction(function () use ($request, $admin) {
            // Re-check under a row lock inside the transaction — two Admins
            // approving the same conversion request must not both apply
            // (admin_dashboard_workflow.md Phase 18: "two Admins approve same
            // conversion").
            $request = AccountConversionRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only a pending request can be approved.']);
            }

            $account = $request->account;
            $oldSnapshot = ['account_type' => $account->account_type, 'display_name' => $account->display_name];

            $account->update([
                'account_type'                 => 'organization',
                'display_name'                 => $request->proposed_display_name,
                'converted_from_individual_at' => now(),
            ]);

            AccountTypeChange::create([
                'account_id'            => $account->id,
                'conversion_request_id' => $request->id,
                'old_type'              => 'individual',
                'new_type'              => 'organization',
                'old_display_name'      => $oldSnapshot['display_name'],
                'new_display_name'      => $request->proposed_display_name,
                'old_snapshot'          => $oldSnapshot,
                'new_snapshot'          => $request->proposed_organization_data,
                'changed_by_user_id'    => $request->submitted_by_user_id,
                'approved_by_user_id'   => $admin->id,
                'changed_at'            => now(),
            ]);

            // Active Supplier capability must be re-approved after conversion.
            $supplierCapability = $account->supplierCapability;
            if ($supplierCapability && $supplierCapability->status === 'active') {
                $supplierCapability->update(['status' => 'pending']);
            }

            $request->update([
                'status'              => 'approved',
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
            ]);
        });

        return $request->fresh();
    }

    public function reject(AccountConversionRequest $request, User $admin, string $comment): AccountConversionRequest
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending request can be rejected.']);
        }

        $request->update([
            'status'              => 'rejected',
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at'         => now(),
            'review_comment'      => $comment,
        ]);

        return $request->fresh();
    }
}
