@extends('frontend.layouts.master')

@section('title', 'Privacy Policy — EduShopify')
@section('meta_description', 'EduShopify Privacy Policy.')

@section('content')
    <div class="fe-container py-10 sm:py-14 max-w-4xl mx-auto">
        <x-frontend::navigation.breadcrumbs :items="['Privacy' => null]" />

        <h1 class="text-3xl font-bold tracking-tight mb-2" style="font-family:var(--font-display);color:var(--fe-text);">Privacy Policy</h1>
        <p class="text-sm mb-8" style="color:var(--fe-text-muted);">Last updated: {{ now()->format('F j, Y') }}</p>

        <div class="prose-sm max-w-none space-y-6 text-sm leading-relaxed" style="color:var(--fe-text-muted);">
            <p>This Privacy Policy explains how EduShopify collects, uses and protects information when you use the marketplace.</p>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">1. Information We Collect</h2>
                <p>We collect account information you provide during registration, profile and company details, RFQ and quotation content, and inquiry/contact form submissions. We also collect standard technical data such as IP address and browser information for security purposes.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">2. How We Use Information</h2>
                <p>Information is used to operate the marketplace, facilitate RFQ and quotation workflows, verify Supplier eligibility, respond to inquiries, and maintain platform security.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">3. Public Information</h2>
                <p>Certain information — such as published listings, Supplier storefront details, published reviews, and global RFQ summaries — is intentionally public. Private information, including RFQ buyer contact details, selected-Supplier RFQ data, and Supplier verification documents, is not exposed publicly.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">4. Guest Inquiries</h2>
                <p>When you submit a Contact Supplier or Contact Us form, we store your submitted details and route them to the relevant Supplier or our support team as a lead — not as an RFQ or official procurement request.</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">5. Data Sharing</h2>
                <p>We do not sell personal information. Information is shared with other users only as necessary to facilitate marketplace functionality (for example, sharing your inquiry with the Supplier you contacted).</p>
            </div>

            <div>
                <h2 class="text-base font-semibold mb-2" style="color:var(--fe-text);">6. Your Choices</h2>
                <p>You may update your account information at any time from your dashboard, or contact us to request assistance with your data.</p>
            </div>

            <p>For privacy questions, please <a href="{{ route('frontend.pages.contact') }}" class="font-semibold" style="color:var(--fe-primary);">contact us</a>.</p>
        </div>
    </div>
@endsection
