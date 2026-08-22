<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireBuyerCapability
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

        if (! $account->hasActiveCapability('buyer')) {
            abort(403, 'Your Buyer capability is not active. Please complete onboarding and wait for approval.');
        }

        return $next($request);
    }
}
