<?php

namespace App\Services\Account;

use App\Models\Account;
use App\Models\Rfq;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Services\BuyerOnboardingStateService;
use App\Services\SupplierOnboardingStateService;
use App\Services\Catalog\PublicListingQuery;
use App\Services\MessagingService;

/**
 * Resolves a public frontend CTA intent into a real dashboard destination
 * after authentication (frontend_workflow.md Parts 50-53). Always
 * re-resolves the target resource against current public/eligibility state
 * rather than trusting anything captured before login.
 */
class PublicHandoffResolver
{
    public function resolve(User $user, string $action, array $params): string
    {
        $account = $user->accountMember?->account;
        if ($account) {
            $account->loadMissing('capabilities.capabilityType');
        }

        // Actions requiring active buyer capability: route to onboarding if not active yet
        if (in_array($action, ['post_rfq', 'request_quote_listing', 'request_quote_supplier', 'compare_rfq'])) {
            if (! $account || ! $account->hasActiveCapability('buyer')) {
                return app(BuyerOnboardingStateService::class)->resolve($user);
            }
        }

        // Actions requiring active supplier capability: route to onboarding if not active yet
        if (in_array($action, ['submit_quotation'])) {
            if (! $account || ! $account->hasActiveCapability('supplier')) {
                return app(SupplierOnboardingStateService::class)->resolveRoute($user);
            }
        }

        if ($action === 'contact_supplier') {
            return $this->contactSupplier($user, $account, $params);
        }

        return match ($action) {
            'post_rfq' => route('buyer.rfqs.create'),
            'request_quote_listing' => $this->requestQuoteListing($params),
            'request_quote_supplier' => $this->requestQuoteSupplier($params),
            'compare_rfq' => $this->compareRfq($params),
            'submit_quotation' => $this->submitQuotation($params),
            'save_listing' => $this->saveListing($params),
            'save_supplier' => $this->saveSupplier($params),
            default => route('home'),
        };
    }

    /**
     * "Contact Supplier" — branches to whichever messaging rule the viewer's
     * account already follows: an active Buyer messages through
     * buyer.suppliers.message's flow (same MessagingService call,
     * landing on buyer.messages.show); an active Supplier lands on
     * supplier.messages.show instead. Neither capability active yet →
     * buyer onboarding, since messaging a supplier is a buyer action here
     * and this mirrors every other buyer-gated CTA in this resolver.
     */
    private function contactSupplier(User $user, ?Account $account, array $params): string
    {
        $supplierAccount = SupplierProfile::where('slug', $params['slug'] ?? null)->first()?->account;

        if (! $supplierAccount || ($account && $account->id === $supplierAccount->id)) {
            return route('home');
        }

        if ($account && $account->hasActiveCapability('buyer')) {
            $conversation = app(MessagingService::class)->startOrGetConversation($account, $user, $supplierAccount, 'general');

            return route('buyer.messages.show', $conversation);
        }

        if ($account && $account->hasActiveCapability('supplier')) {
            $conversation = app(MessagingService::class)->startOrGetConversation($account, $user, $supplierAccount, 'general');

            return route('supplier.messages.show', $conversation);
        }

        return app(BuyerOnboardingStateService::class)->resolve($user);
    }

    private function requestQuoteListing(array $params): string
    {
        $listing = PublicListingQuery::base()->where('slug', $params['slug'] ?? null)->first();

        return $listing
            ? route('buyer.rfqs.create', ['listing' => $listing->id])
            : route('buyer.rfqs.create');
    }

    /**
     * "Send RFQ to All Suppliers" from /compare, resumed after login —
     * re-resolves every slug fresh against current public eligibility
     * rather than trusting the pre-login list (a listing could have been
     * unpublished in the meantime).
     */
    private function compareRfq(array $params): string
    {
        $slugs = $params['slugs'] ?? [];
        if (empty($slugs)) {
            return route('buyer.rfqs.create');
        }

        $ids = PublicListingQuery::base()->whereIn('slug', $slugs)->pluck('id');

        return $ids->isNotEmpty()
            ? route('buyer.rfqs.create', ['listings' => $ids->implode(',')])
            : route('buyer.rfqs.create');
    }

    private function requestQuoteSupplier(array $params): string
    {
        $profile = SupplierProfile::where('slug', $params['slug'] ?? null)->first();

        return $profile
            ? route('buyer.rfqs.create', ['supplier' => $profile->account_id])
            : route('buyer.rfqs.create');
    }

    private function submitQuotation(array $params): string
    {
        $rfq = Rfq::where('rfq_number', $params['rfq_number'] ?? null)->first();

        return $rfq
            ? route('supplier.opportunities.show', $rfq)
            : route('supplier.opportunities.index');
    }

    private function saveListing(array $params): string
    {
        $listing = PublicListingQuery::base()->where('slug', $params['slug'] ?? null)->first();

        return $listing
            ? route('frontend.listings.show', $listing->slug).'?save_intent=1'
            : route('frontend.catalog.index');
    }

    private function saveSupplier(array $params): string
    {
        $profile = SupplierProfile::where('slug', $params['slug'] ?? null)->first();

        return $profile
            ? route('frontend.suppliers.show', $profile->slug).'?save_intent=1'
            : route('frontend.suppliers.index');
    }
}
