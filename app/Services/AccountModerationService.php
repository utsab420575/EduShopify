<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AccountModerationService
{
    public function approve(Account $account, User $admin): Account
    {
        if ($account->status !== 'pending_approval') {
            throw ValidationException::withMessages(['status' => 'Only an account pending approval can be approved.']);
        }

        $account->update([
            'status'              => 'active',
            'approved_at'         => now(),
            'approved_by_user_id' => $admin->id,
        ]);

        return $account->fresh();
    }

    public function suspend(Account $account, User $admin, string $reason): Account
    {
        if (in_array($account->status, ['suspended', 'deleted'], true)) {
            throw ValidationException::withMessages(['status' => 'This account is already suspended or deleted.']);
        }

        $account->update([
            'status'              => 'suspended',
            'suspended_at'        => now(),
            'suspended_by_user_id' => $admin->id,
            'suspension_reason'   => $reason,
        ]);

        activity('moderation')->causedBy($admin)->performedOn($account)->withProperties(['reason' => $reason])->log('Account suspended');

        return $account->fresh();
    }

    public function reactivate(Account $account): Account
    {
        if ($account->status !== 'suspended') {
            throw ValidationException::withMessages(['status' => 'Only a suspended account can be reactivated.']);
        }

        $account->update([
            'status'              => 'active',
            'suspended_at'        => null,
            'suspended_by_user_id' => null,
            'suspension_reason'   => null,
        ]);

        return $account->fresh();
    }
}
