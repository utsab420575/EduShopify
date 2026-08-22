<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BrandModerationService
{
    public function approve(Brand $brand, User $admin): Brand
    {
        if ($brand->approval_status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending brand can be approved.']);
        }

        $brand->update([
            'approval_status'     => 'approved',
            'rejection_reason'    => null,
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at'         => now(),
        ]);

        return $brand->fresh();
    }

    public function reject(Brand $brand, User $admin, string $reason): Brand
    {
        if ($brand->approval_status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending brand can be rejected.']);
        }

        $brand->update([
            'approval_status'     => 'rejected',
            'rejection_reason'    => $reason,
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at'         => now(),
        ]);

        return $brand->fresh();
    }
}
