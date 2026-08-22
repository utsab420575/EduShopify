<?php

namespace App\Services;

use App\Models\User;

/**
 * Resolves the correct onboarding/dashboard route for a buyer
 * based on their capability and profile state.
 * Used after login and after email verification.
 */
class BuyerOnboardingStateService
{
    public function resolve(User $user): string
    {
        // Ensure account context is loaded
        $user->load('accountMember.account.buyerCapability', 'accountMember.account.buyerProfile');

        $account = $user->accountMember?->account;

        if (! $account) {
            return route('home');
        }

        $cap = $account->buyerCapability;

        // No buyer capability — shouldn't reach buyer onboarding
        if (! $cap) {
            return route('home');
        }

        return match ($cap->status) {
            'draft'             => $this->draftRoute($account),
            'pending',
            'revision_required',
            'rejected',
            'active'            => route('buyer.dashboard'),
            default             => route('home'),
        };
    }

    private function draftRoute(\App\Models\Account $account): string
    {
        $profile = $account->buyerProfile;

        if (! $profile || ! $profile->isComplete()) {
            return route('buyer.onboarding.profile');
        }

        return route('buyer.onboarding.review');
    }
}
