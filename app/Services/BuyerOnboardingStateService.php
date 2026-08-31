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
        $user->load('accountMember.account.buyerCapability', 'accountMember.account.supplierCapability', 'accountMember.account.buyerProfile');

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
            'draft'             => $this->draftRoute($user, $account),
            'pending',
            'revision_required',
            'rejected',
            'active'            => route('buyer.dashboard'),
            default             => route('home'),
        };
    }

    /**
     * Review and submission now happen inside the last step of the profile
     * wizard itself (mount() resumes at the right step), so a draft buyer
     * normally lands on the same one route regardless of progress.
     *
     * Exception: a dual buyer+supplier registration always finishes Supplier
     * onboarding first (the longer, document + plan driven wizard) — once
     * Supplier submits, its shared fields get copied into the still-draft
     * BuyerProfile (see CapabilityApplicationService::submit()), so Buyer's
     * wizard opens pre-filled instead of asking for the same details twice.
     */
    private function draftRoute(User $user, \App\Models\Account $account): string
    {
        $supplierCap = $account->supplierCapability;

        if ($supplierCap && $supplierCap->status === 'draft') {
            return app(SupplierOnboardingStateService::class)->resolveRoute($user);
        }

        return route('buyer.onboarding.profile');
    }
}
