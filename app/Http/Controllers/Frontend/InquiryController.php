<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StorePublicInquiryRequest;
use App\Models\ContactInquiry;
use App\Models\Listing;
use App\Models\SupplierProfile;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Guest/public Supplier or listing inquiries (frontend_workflow.md Part 54).
 * A contact_inquiries row is a lead, never an RFQ/quotation/Award/PO.
 */
class InquiryController extends Controller
{
    public function listing(StorePublicInquiryRequest $request, Listing $listing)
    {
        abort_unless(PublicListingQuery::base()->whereKey($listing->id)->exists(), 404);

        $this->create($request, $listing->supplier_account_id, $listing->id);

        return back()->with('success', 'Your inquiry has been sent to the supplier.');
    }

    public function supplier(StorePublicInquiryRequest $request, SupplierProfile $supplier)
    {
        abort_unless(\App\Services\Account\PublicSupplierQuery::base()->whereKey($supplier->id)->exists(), 404);

        $this->create($request, $supplier->account_id, null);

        return back()->with('success', 'Your inquiry has been sent to the supplier.');
    }

    private function create(Request $request, int $supplierAccountId, ?int $listingId): ContactInquiry
    {
        return ContactInquiry::create([
            'inquiry_number' => $this->generateNumber(),
            'supplier_account_id' => $supplierAccountId,
            'listing_id' => $listingId,
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone') ?: null,
            'organization' => $request->string('organization') ?: null,
            'subject' => $request->string('subject') ?: null,
            'message' => $request->string('message'),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'source_url' => substr((string) $request->headers->get('referer', $request->fullUrl()), 0, 255),
        ]);
    }

    private function generateNumber(): string
    {
        do {
            $number = 'INQ-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (ContactInquiry::where('inquiry_number', $number)->exists());

        return $number;
    }
}
