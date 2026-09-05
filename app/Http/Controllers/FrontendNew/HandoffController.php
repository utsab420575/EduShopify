<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Account\PublicHandoffResolver;
use App\Support\FrontendIntent;
use Illuminate\Support\Facades\Auth;

/**
 * Public → protected handoff for V2 CTAs that require the Buyer/Supplier
 * workflow. Independent of App\Http\Controllers\Frontend\HandoffController
 * (legacy) but reuses the same underlying, presentation-agnostic services —
 * PublicHandoffResolver and FrontendIntent carry no view/route coupling.
 */
class HandoffController extends Controller
{
    public function submitQuotation(string $rfqNumber)
    {
        if (! Auth::check()) {
            FrontendIntent::remember('submit_quotation', ['rfq_number' => $rfqNumber]);

            return redirect()->route('login');
        }

        return redirect(app(PublicHandoffResolver::class)->resolve(Auth::user(), 'submit_quotation', ['rfq_number' => $rfqNumber]));
    }

    /**
     * "Post an RFQ" — buyers go to their RFQ list, suppliers (who can't
     * post RFQs) go to opportunities they can bid on instead. Not one of
     * PublicHandoffResolver's actions (that resolver's own 'post_rfq' targets
     * buyer.rfqs.create and always forces buyer onboarding, with no supplier
     * branch) — a new action name here, resolved via a static helper that
     * routes/web.php's post-login handoff also calls directly for the
     * guest → login → destination leg (see that file for why).
     */
    public function postRfq()
    {
        if (! Auth::check()) {
            FrontendIntent::remember('post_rfq_v2', []);

            return redirect()->route('login');
        }

        return redirect(self::resolvePostRfqDestination(Auth::user()));
    }

    public static function resolvePostRfqDestination(User $user): string
    {
        $account = $user->accountMember?->account;

        if ($account?->isSupplier() && ! $account?->isBuyer()) {
            return route('supplier.opportunities.index');
        }

        return route('buyer.rfqs.index');
    }

    /**
     * Single post-login intent resolver for routes/web.php's two handoff
     * call sites (password login and email verification): V2-only actions
     * are resolved here, everything else falls through to the shared
     * PublicHandoffResolver unchanged — same actions, same behavior the
     * legacy frontend already depends on.
     */
    public static function resolveIntent(User $user, array $intent): string
    {
        if (($intent['action'] ?? null) === 'post_rfq_v2') {
            return self::resolvePostRfqDestination($user);
        }

        return app(PublicHandoffResolver::class)->resolve($user, $intent['action'], $intent['params']);
    }
}
