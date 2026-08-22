@extends('frontend.layouts.master')

@section('title', 'Terms of Service — EduShopify')
@section('meta_description', 'EduShopify Terms of Service.')

@section('content')
    <div class="fe-container py-10 sm:py-14 max-w-4xl mx-auto">
        <x-frontend::navigation.breadcrumbs :items="['Terms' => null]" />

        <h1 class="text-3xl font-bold tracking-tight mb-2" style="font-family:var(--font-display);color:var(--fe-text);">Terms of Service</h1>
        <p class="text-sm mb-8" style="color:var(--fe-text-muted);">Last updated: {{ now()->format('F j, Y') }}</p>

        <div class="prose-sm max-w-none space-y-6 text-sm leading-relaxed" style="color:var(--fe-text-muted);">
            <p>These Terms of Service govern access to and use of the EduShopify marketplace. By creating an account or using the platform, you agree to these terms.</p>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">1. The Marketplace</h2>
                <p>EduShopify is a B2B education procurement marketplace connecting institutional Buyers with verified Suppliers through a structured Request for Quotation (RFQ) process. EduShopify facilitates discovery, sourcing and communication between Buyers and Suppliers.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">2. Accounts</h2>
                <p>You must provide accurate information when registering. Buyer and Supplier capabilities are subject to eligibility, approval and ongoing compliance with these terms.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">3. RFQs, Quotations and Purchase Orders</h2>
                <p>An RFQ posted by a Buyer is a structured sourcing request. A guest inquiry submitted through the public marketplace is not an RFQ, quotation, Award or Purchase Order. Official procurement actions require an authenticated, eligible account.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">4. Fulfilment & Payment</h2>
                <p>In this phase of the platform, payment and fulfilment for products and services occur outside EduShopify between the Buyer and Supplier. EduShopify processes Supplier subscription payments only.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">5. Conduct</h2>
                <p>Users must not misuse the platform, submit false information, or attempt to access data or accounts they are not authorized to access.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">6. Changes</h2>
                <p>We may update these terms from time to time. Continued use of the platform after changes constitutes acceptance of the revised terms.</p>
            </div>

            <p>For questions about these terms, please <a href="{{ route('frontend.pages.contact') }}" class="font-semibold" style="color:var(--fe-primary);">contact us</a>.</p>
        </div>
    </div>
@endsection
