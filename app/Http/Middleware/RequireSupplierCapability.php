<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Gate-keeps all Supplier dashboard routes that require an active Supplier capability.
 *
 * Mirrors RequireBuyerCapability — see ARCHITECTURE.md §22 middleware order:
 *   auth → active user → active account → active supplier capability.
 *
 * The root /supplier/ dashboard route intentionally bypasses this middleware
 * so that a pending/rejected/revision_required Supplier still sees a status page.
 */
class RequireSupplierCapability
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->isActive()) {
            abort(403, 'User account is not active.');
        }

        $account = $user->activateTeamContext();

        if (! $account || ! $account->isActive()) {
            abort(403, 'Account is not active.');
        }

        if (! $account->hasActiveCapability('supplier')) {
            abort(403, 'Your Supplier capability is not active. Please complete onboarding and wait for approval.');
        }

        return $next($request);
    }
}
