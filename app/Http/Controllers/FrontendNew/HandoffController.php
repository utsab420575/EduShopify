<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
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
}
