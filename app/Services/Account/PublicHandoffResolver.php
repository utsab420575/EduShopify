<?php

namespace App\Services\Account;

use App\Models\Rfq;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Services\BuyerOnboardingStateService;
use App\Services\SupplierOnboardingStateService;
use App\Services\Catalog\PublicListingQuery;

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
        if (in_array($action, ['post_rfq', 'request_quote_listing', 'request_quote_supplier'])) {
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

        return match ($action) {
            'post_rfq' => route('buyer.rfqs.create'),
            'request_quote_listing' => $this->requestQuoteListing($params),
            'request_quote_supplier' => $this->requestQuoteSupplier($params),
            'submit_quotation' => $this->submitQuotation($params),
            'save_listing' => $this->saveListing($params),
            'save_supplier' => $this->saveSupplier($params),
            default => route('home'),
        };
    }

    private function requestQuoteListing(array $params): string
    {
        $listing = PublicListingQuery::base()->where('slug', $params['slug'] ?? null)->first();

        return $listing
            ? route('buyer.rfqs.create', ['listing_id' => $listing->id])
            : route('buyer.rfqs.create');
    }

    private function requestQuoteSupplier(array $params): string
    {
        $profile = SupplierProfile::where('slug', $params['slug'] ?? null)->first();

        return $profile
            ? route('buyer.rfqs.create', ['supplier_account_id' => $profile->account_id])
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
