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
            'buyer.onboarding.review',
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

        // Check Buyer onboarding status
        $account = $user->activateTeamContext();

        if ($account && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))->exists()) {
            $cap = $account->buyerCapability;

            if ($cap && $cap->status === 'draft') {
                $profile = $account->buyerProfile;

                // Profile not complete — force redirect to onboarding profile or review
                if (! $profile || ! $profile->isComplete()) {
                    return redirect()->route('buyer.onboarding.profile');
                }

                return redirect()->route('buyer.onboarding.review');
            }
        }

        // Check Supplier onboarding status (draft capability/profile)
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

        return $next($request);
    }
}
