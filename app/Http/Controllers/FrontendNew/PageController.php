<?php

namespace App\Http\Controllers\FrontendNew;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreContactRequest;
use App\Models\AccountLocation;
use App\Models\ContactInquiry;
use App\Models\Rfq;
use App\Models\SubscriptionPlan;
use App\Services\Account\PublicSupplierQuery;
use App\Services\Catalog\PublicListingQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Static/company pages linked from the v2 footer: About, Contact, Careers,
 * Help Center, Pricing, Terms, Privacy. Numbers shown on these pages come
 * from live DB queries — no hardcoded marketing stats.
 */
class PageController extends Controller
{
    public function about(): View
    {
        return view('frontend_new.pages.about', [
            'stats' => $this->platformStats(),
        ]);
    }

    public function contact(): View
    {
        return view('frontend_new.pages.contact', [
            'contactEmail' => config('contact.email'),
        ]);
    }

    /**
     * Stored as a platform-level guest inquiry (supplier_account_id = null),
     * handled by Admin — same table and shape as the legacy contact form.
     */
    public function contactSubmit(StoreContactRequest $request): RedirectResponse
    {
        ContactInquiry::create([
            'inquiry_number' => $this->generateInquiryNumber(),
            'supplier_account_id' => null,
            'listing_id' => null,
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone') ?: null,
            'organization' => $request->string('organization') ?: null,
            'subject' => $request->string('subject'),
            'message' => $request->string('message'),
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'source_url' => substr((string) $request->headers->get('referer', $request->fullUrl()), 0, 255),
        ]);

        return redirect()->route('v2.pages.contact')
            ->with('success', 'Thanks — your message has been sent. Our team will get back to you soon.');
    }

    public function careers(): View
    {
        return view('frontend_new.pages.careers', [
            'contactEmail' => config('contact.email'),
        ]);
    }

    public function help(): View
    {
        return view('frontend_new.pages.help');
    }

    public function pricing(): View
    {
        return view('frontend_new.pages.pricing', [
            'plans' => SubscriptionPlan::active()->get(),
        ]);
    }

    public function terms(): View
    {
        return view('frontend_new.pages.terms');
    }

    public function privacy(): View
    {
        return view('frontend_new.pages.privacy');
    }

    private function platformStats(): array
    {
        $supplierAccountIds = PublicSupplierQuery::base()->select('account_id');

        return [
            ['value' => PublicSupplierQuery::base()->count(), 'label' => 'Verified Suppliers'],
            ['value' => PublicListingQuery::base()->count(), 'label' => 'Products & Services'],
            ['value' => Rfq::globalVisibility()->open()->count(), 'label' => 'Open RFQs'],
            ['value' => AccountLocation::where('is_active', true)
                ->whereIn('account_id', $supplierAccountIds)
                ->distinct()
                ->count('country_id'), 'label' => 'Countries'],
        ];
    }

    private function generateInquiryNumber(): string
    {
        do {
            $number = 'INQ-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (ContactInquiry::where('inquiry_number', $number)->exists());

        return $number;
    }
}
