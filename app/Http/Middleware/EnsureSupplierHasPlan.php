<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires an approved supplier to hold an active subscription before using
 * the supplier panel, apart from the pricing and checkout routes themselves.
 *
 * Spec section 15.2: a supplier is never given a plan automatically — Admin
 * creates plans and the supplier selects one after capability approval.
 */
class EnsureSupplierHasPlan
{
    /**
     * Routes reachable without a subscription, so the supplier can buy one.
     */
    private const EXEMPT_ROUTES = [
        'supplier.pricing',
        'supplier.subscribe',
        'supplier.subscribe.success',
        'supplier.subscribe.cancel',
        'supplier.pending',
        'stripe.webhook',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $account = $request->attributes->get('account') ?? $user->account;

        // Approval is enforced by EnsureSupplierIsApproved; until the capability
        // is active there is nothing to subscribe to.
        if (! $account || ! $account->hasActiveCapability('supplier')) {
            return $next($request);
        }

        if ($account->hasActiveSubscription()) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::EXEMPT_ROUTES, true)) {
            return $next($request);
        }

        return redirect()->route('supplier.pricing');
    }
}
