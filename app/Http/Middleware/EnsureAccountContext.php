<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Models\AccountMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the authorization order from spec section 4.7, steps 1–4 and 7.
 *
 * Apply to routes that require a usable account:
 *
 *   ->middleware('account.context')            // active user + account + membership
 *   ->middleware('account.context:buyer')      // ... plus an active Buyer capability
 *   ->middleware('account.context:supplier')   // ... plus an active Supplier capability
 *
 * SetAccountContext has already resolved the account and set the Spatie team;
 * this middleware decides whether the request may proceed.
 */
class EnsureAccountContext
{
    public function handle(Request $request, Closure $next, ?string $capability = null): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $this->reject($request, 'Please sign in to continue.', 'login');
        }

        // 1 — user status
        if ($user->status !== 'active') {
            return $this->reject($request, 'Your user account is not active.', 'login');
        }

        $member = $request->attributes->get('account_member')
            ?? AccountMember::with('account')->where('user_id', $user->getKey())->first();

        // 3 — membership status
        if (! $member || $member->status !== 'active') {
            return $this->reject($request, 'You do not have an active account membership.', 'login');
        }

        /** @var Account|null $account */
        $account = $member->account;

        // 2 — account status
        if (! $account || $account->status !== 'active') {
            return $this->reject($request, 'Your account is not active yet.', 'onboarding');
        }

        // 7 — a route naming an account must name THIS account
        $routeAccount = $request->route('account') ?? $request->route('account_id');

        if ($routeAccount !== null) {
            $routeAccountId = $routeAccount instanceof Account
                ? (int) $routeAccount->getKey()
                : (int) $routeAccount;

            abort_if($routeAccountId !== (int) $account->getKey(), 403, 'This resource belongs to another account.');
        }

        // 4 — required Buyer/Supplier capability
        if ($capability !== null && ! $account->hasActiveCapability($capability)) {
            return $this->reject(
                $request,
                sprintf('Your account does not have an active %s capability yet.', $capability),
                'onboarding'
            );
        }

        return $next($request);
    }

    private function reject(Request $request, string $message, string $target): Response
    {
        if ($request->expectsJson()) {
            abort(403, $message);
        }

        $route = $target === 'onboarding' && Route::has('dashboard') ? 'dashboard' : 'login';

        if (! Route::has($route)) {
            abort(403, $message);
        }

        return redirect()->route($route)->with('error', $message);
    }
}
