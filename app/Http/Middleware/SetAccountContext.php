<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Models\AccountMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establishes the account (Spatie team) context for an authenticated request.
 *
 * Implements the context half of spec section 30.1: resolve the user's
 * account_members row, set the Spatie team id, reload role/permission relations
 * and expose the account and capability on the request.
 *
 * This middleware never denies. A user part-way through onboarding has a draft
 * account and a pending_verification status, and still needs to reach the
 * onboarding screens. Enforcement is the job of EnsureAccountContext, applied
 * to the routes that actually require an active account.
 *
 * Without this running, Spatie Teams resolves every role and permission check
 * against a null team and silently denies everything.
 */
class SetAccountContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $member = AccountMember::with('account')
            ->where('user_id', $user->getKey())
            ->first();

        $account = $member?->account;

        if ($account) {
            // Set the team before route model binding and any gate check.
            app(PermissionRegistrar::class)->setPermissionsTeamId($account->getKey());

            // Drop relations cached under a previous team.
            $user->unsetRelation('roles')->unsetRelation('permissions');

            $capability = $this->resolveCapability($request, $account);

            $request->attributes->set('account', $account);
            $request->attributes->set('account_member', $member);
            $request->attributes->set('capability', $capability);

            if ($request->hasSession()) {
                $request->session()->put('account_id', $account->getKey());
                $request->session()->put('capability', $capability);
            }

            app()->instance('current.account', $account);
        }

        return $next($request);
    }

    /**
     * Capability context comes from the URL prefix (/buyer, /supplier) where
     * present, otherwise from the account's saved dashboard preference, and
     * finally from whichever capability is active.
     */
    private function resolveCapability(Request $request, Account $account): ?string
    {
        foreach (['buyer', 'supplier'] as $capability) {
            if ($request->is($capability, "$capability/*")) {
                return $capability;
            }
        }

        if ($account->is_system_account) {
            return null;
        }

        if ($preferred = $account->dashboardPreference?->default_mode) {
            return $preferred;
        }

        return match (true) {
            $account->hasActiveCapability('buyer')    => 'buyer',
            $account->hasActiveCapability('supplier') => 'supplier',
            default                                   => null,
        };
    }
}
