<?php

namespace App\Http\Middleware;

use App\Services\BuyerOnboardingStateService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Ensures logged-in users with incomplete onboarding profiles
 * are redirected back to their onboarding steps whenever they
 * try to navigate to home or marketplace pages.
 */
class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // System admin bypasses onboarding checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Skip onboarding middleware for onboarding routes, auth routes, and static assets
        $allowedRouteNames = [
            'buyer.onboarding.profile',
            'supplier.onboarding.profile',
            'verification.notice',
            'verification.verify',
            'verification.send',
            'logout',
        ];

        $currentRouteName = Route::currentRouteName() ?? '';

        // Skip onboarding middleware for all onboarding routes, auth routes, and verification
        if (
            str_starts_with($currentRouteName, 'buyer.onboarding.') ||
            str_starts_with($currentRouteName, 'supplier.onboarding.') ||
            in_array($currentRouteName, ['verification.notice', 'verification.verify', 'verification.send', 'logout'])
        ) {
            return $next($request);
        }

        // Email must be verified first
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $account = $user->activateTeamContext();

        // Check Supplier onboarding status first (draft capability/profile)
        // — a dual buyer+supplier registration always finishes Supplier
        // onboarding before Buyer's (see BuyerOnboardingStateService), so
        // while both are still draft, Supplier wins here too.
        if ($account && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists()) {
            $cap = $account->supplierCapability;

            if ($cap && $cap->status === 'draft') {
                $supplierProfile = $account->supplierProfile;

                if (! $supplierProfile || ! $supplierProfile->isComplete()) {
                    return redirect()->route('supplier.onboarding.profile');
                }

                return redirect()->route('supplier.onboarding.documents');
            }
        }

        // Check Buyer onboarding status
        if ($account && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))->exists()) {
            $cap = $account->buyerCapability;

            if ($cap && $cap->status === 'draft') {
                // Review and submission now happen inside the wizard's last
                // step — mount() resumes at the right step, so there's only
                // ever one route to send a draft buyer to.
                return redirect()->route('buyer.onboarding.profile');
            }
        }

        return $next($request);
    }
}
