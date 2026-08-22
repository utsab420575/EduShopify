<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreContactRequest;
use App\Models\ContactInquiry;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function howItWorks()
    {
        return view('frontend.pages.how-it-works');
    }

    public function pricing()
    {
        return view('frontend.pages.pricing', [
            'plans' => SubscriptionPlan::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function about()
    {
        return view('frontend.pages.about');
    }

    public function contact()
    {
        return view('frontend.pages.contact');
    }

    public function contactSubmit(StoreContactRequest $request)
    {
        ContactInquiry::create([
            'inquiry_number' => $this->generateNumber(),
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

        return back()->with('success', 'Thanks — your message has been sent. Our team will get back to you soon.');
    }

    public function faqs()
    {
        return view('frontend.pages.faqs');
    }

    public function terms()
    {
        return view('frontend.pages.terms');
    }

    public function privacy()
    {
        return view('frontend.pages.privacy');
    }

    private function generateNumber(): string
    {
        do {
            $number = 'INQ-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (ContactInquiry::where('inquiry_number', $number)->exists());

        return $number;
    }
}
