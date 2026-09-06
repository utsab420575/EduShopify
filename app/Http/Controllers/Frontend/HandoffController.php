<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Account\PublicHandoffResolver;
use App\Support\FrontendIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Public → protected handoff for CTAs that require Buyer/Supplier workflow
 * (frontend_workflow.md Parts 50-53). Guests are sent to login with a
 * remembered safe intent; authenticated users are resolved immediately.
 */
class HandoffController extends Controller
{
    public function postRfq()
    {
        return $this->handle('post_rfq', []);
    }

    /**
     * "Send RFQ to All Suppliers" from the /compare page. Slugs, not ids —
     * every other handoff action re-resolves its target fresh by slug after
     * login rather than trusting anything captured before authentication.
     */
    public function compareRfq(Request $request)
    {
        $slugs = collect(explode(',', (string) $request->query('listings')))
            ->map(fn ($slug) => trim($slug))
            ->filter()
            ->values()
            ->all();

        return $this->handle('compare_rfq', ['slugs' => $slugs]);
    }

    public function requestQuoteListing(string $listing)
    {
        return $this->handle('request_quote_listing', ['slug' => $listing]);
    }

    public function requestQuoteSupplier(string $supplier)
    {
        return $this->handle('request_quote_supplier', ['slug' => $supplier]);
    }

    public function submitQuotation(string $rfqNumber)
    {
        return $this->handle('submit_quotation', ['rfq_number' => $rfqNumber]);
    }

    public function saveListing(string $listing)
    {
        return $this->handle('save_listing', ['slug' => $listing]);
    }

    public function saveSupplier(string $supplier)
    {
        return $this->handle('save_supplier', ['slug' => $supplier]);
    }

    private function handle(string $action, array $params)
    {
        if (! Auth::check()) {
            FrontendIntent::remember($action, $params);

            return redirect()->route('login');
        }

        return redirect(app(PublicHandoffResolver::class)->resolve(Auth::user(), $action, $params));
    }
}
