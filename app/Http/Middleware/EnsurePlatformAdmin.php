<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Platform Admin gate for the /admin/* Controller+Blade dashboard (admin
 * workflow spec section "Admin Authority Model" / Part 18).
 *
 * Reuses User::isAdmin() — reserved system account + one of the platform
 * staff roles (super_admin, admin, admin_staff, moderator, support_agent,
 * finance_staff) — the exact same eligibility check the former Filament
 * panel used via canAccessPanel(), so no separate admin-eligibility concept
 * is introduced.
 *
 * Fine-grained permission checks (e.g. 'listings.approve') still happen in
 * Policies/Controllers via $user->hasPermissionTo(...) — this middleware only
 * answers "is this user platform admin staff at all".
 */
class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->redirectToLogin($request, 'Please sign in to continue.');
        }

        if (! $user->isActive() || ! $user->isAdmin()) {
            abort(403, 'You do not have access to the Admin dashboard.');
        }

        return $next($request);
    }

    private function redirectToLogin(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            abort(403, $message);
        }

        abort_unless(\Illuminate\Support\Facades\Route::has('login'), 403, $message);

        return redirect()->route('login')->with('error', $message);
    }
}
