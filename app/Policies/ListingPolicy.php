<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    private function checkAccess(User $user): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return false;
        }

        return $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists();
    }

    public function view(User $user): bool
    {
        return $this->checkAccess($user) && $user->hasPermissionTo('listing.view');
    }

    public function create(User $user): bool
    {
        // Pending suppliers CAN prepare draft listings (Section 53)
        return $this->checkAccess($user) && $user->hasPermissionTo('listing.create');
    }

    public function update(User $user, Listing $listing): bool
    {
        if (! $this->checkAccess($user) || ! $user->hasPermissionTo('listing.update')) {
            return false;
        }

        return $listing->supplier_account_id === $user->accountMember?->account_id;
    }

    public function publish(User $user, Listing $listing): bool
    {
        if (! $this->checkAccess($user) || ! $user->hasPermissionTo('listing.publish')) {
            return false;
        }

        $account = $user->account;

        // Publishing REQUIRES active supplier capability (Section 86)
        if (! $account?->hasActiveCapability('supplier')) {
            return false;
        }

        // Publishing REQUIRES an active/trialing subscription (Section 86)
        $hasActiveSub = $account->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->exists();

        if (! $hasActiveSub) {
            return false;
        }

        return $listing->supplier_account_id === $user->accountMember?->account_id;
    }

    public function deactivate(User $user, Listing $listing): bool
    {
        if (! $this->checkAccess($user) || ! $user->hasPermissionTo('listing.deactivate')) {
            return false;
        }

        return $listing->supplier_account_id === $user->accountMember?->account_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        if (! $this->checkAccess($user) || ! $user->hasPermissionTo('listing.delete')) {
            return false;
        }

        return $listing->supplier_account_id === $user->accountMember?->account_id;
    }
}
