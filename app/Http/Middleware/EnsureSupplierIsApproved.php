<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks supplier-panel routes until the Supplier CAPABILITY is approved.
 *
 * Approval lives in account_capabilities, not on the supplier profile — the
 * profile deliberately stores no approval state (spec section 11.2).
 */
class EnsureSupplierIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $account = $request->attributes->get('account') ?? $user->account;

        // Not a supplier journey at all — nothing for this middleware to say.
        if (! $account || ! $account->hasCapability('supplier')) {
            return $next($request);
        }

        if (! $account->hasActiveCapability('supplier')) {
            return redirect()->route('supplier.pending');
        }

        return $next($request);
    }
}
