<?php

namespace App\Services;

use App\Models\DocumentType;
use App\Models\SupplierDocument;
use App\Models\User;

/**
 * Evaluates the strict 10-step onboarding state resolution order for a Supplier
 * as specified in Point 3.
 */
class SupplierOnboardingStateService
{
    /**
     * Resolve application/onboarding state key.
     */
    public function resolveState(User $user): string
    {
        // 1. Account / User inactive check
        if (! $user->isActive()) {
            return 'user_inactive';
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            return 'account_inactive';
        }

        // 2. Supplier capability exists?
        $cap = $account->supplierCapability;

        if (! $cap) {
            return 'no_capability';
        }

        // 3. Supplier profile incomplete?
        $profile = $account->supplierProfile;
        if (! $profile || ! $profile->isComplete() || ! $account->supplierTypes()->exists()) {
            return 'profile_incomplete';
        }

        // 4. Required current documents incomplete?
        $requiredDocumentTypeIds = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'))
                  ->where('is_required', true);
            })
            ->pluck('id');

        foreach ($requiredDocumentTypeIds as $typeId) {
            $hasCurrent = SupplierDocument::where('supplier_account_id', $account->id)
                ->where('document_type_id', $typeId)
                ->where('is_current', true)
                ->whereIn('status', ['pending', 'verified'])
                ->exists();

            if (! $hasCurrent) {
                return 'documents_incomplete';
            }
        }

        // 5. Capability status == revision_required?
        if ($cap->status === 'revision_required') {
            return 'revision_required';
        }

        // 6. Capability status == rejected?
        if ($cap->status === 'rejected') {
            return 'rejected';
        }

        // 7. Capability status == pending?
        if ($cap->status === 'pending') {
            // Check if any required current document is rejected
            $hasRejectedRequiredDoc = SupplierDocument::where('supplier_account_id', $account->id)
                ->whereIn('document_type_id', $requiredDocumentTypeIds)
                ->where('is_current', true)
                ->where('status', 'rejected')
                ->exists();

            if ($hasRejectedRequiredDoc) {
                return 'pending_document_action_required';
            }

            return 'pending';
        }

        // 8. Capability status == active + no active/trialing subscription
        if ($cap->status === 'active') {
            $hasActiveSub = $account->subscriptions()
                ->whereIn('status', ['active', 'trialing'])
                ->exists();

            if (! $hasActiveSub) {
                $hasPendingSub = $account->subscriptions()
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPendingSub) {
                    // 9. Subscription pending
                    return 'subscription_pending';
                }

                return 'approved_no_subscription';
            }

            // 10. Active capability + active subscription -> ready
            return 'ready';
        }

        return 'home';
    }

    /**
     * Resolve the corresponding HTTP route URL for the resolved state.
     */
    public function resolveRoute(User $user): string
    {
        $state = $this->resolveState($user);

        return match ($state) {
            'profile_incomplete'                => route('supplier.onboarding.profile'),
            'documents_incomplete'              => route('supplier.onboarding.documents'),
            'pending_document_action_required'  => route('supplier.onboarding.documents'),
            'revision_required'                 => route('supplier.onboarding.revision'),
            'rejected'                          => route('supplier.onboarding.rejected'),
            'pending'                           => route('supplier.dashboard'),
            'approved_no_subscription'          => route('supplier.onboarding.plan'),
            'subscription_pending'              => route('supplier.onboarding.subscription-pending'),
            'ready'                             => route('supplier.dashboard'),
            default                             => route('home'),
        };
    }
}
