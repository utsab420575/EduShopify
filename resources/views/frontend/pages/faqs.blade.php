@extends('frontend.layouts.master')

@section('title', 'FAQs — EduShopify')
@section('meta_description', 'Frequently asked questions about buying, selling and using EduShopify.')

@section('content')
    @php
        $faqGroups = [
            'Buyer Registration' => [
                ['q' => 'How do I register as a Buyer?', 'a' => 'Click Join Free or Register, choose the Buyer capability, and complete your account and profile details.'],
                ['q' => 'Is registration free for Buyers?', 'a' => 'Yes, creating a Buyer account and posting RFQs is free.'],
            ],
            'Supplier Verification' => [
                ['q' => 'How does Supplier verification work?', 'a' => 'After registering as a Supplier, you complete your company profile and upload required documents for Admin review before your account is approved.'],
                ['q' => 'How long does approval take?', 'a' => 'Review times vary, but our team reviews applications as quickly as possible.'],
            ],
            'RFQs & Quotations' => [
                ['q' => 'What is an RFQ?', 'a' => 'A Request for Quotation is a structured sourcing request a Buyer posts describing what they need. Suppliers respond with quotations.'],
                ['q' => 'Can I submit a quotation without an account?', 'a' => 'No — submitting an official quotation requires an approved, eligible Supplier account.'],
            ],
            'Subscription & Payments' => [
                ['q' => 'Do Suppliers need a subscription?', 'a' => 'Yes, Suppliers select a subscription plan that determines listing limits, RFQ access and features.'],
                ['q' => 'Does EduShopify process product payments?', 'a' => 'In this phase, product and service payment happens outside the platform. EduShopify processes Supplier subscription payments only.'],
            ],
            'Privacy & Support' => [
                ['q' => 'Is my RFQ visible to everyone?', 'a' => 'Only RFQs marked as globally visible appear on the public opportunities board with a safe summary. Selected-Supplier RFQs are never shown publicly.'],
                ['q' => 'How do I get help?', 'a' => 'Use our Contact page, or reach out from your dashboard support section once logged in.'],
            ],
        ];
    @endphp

    <div class="fe-container py-10 sm:py-14 max-w-3xl mx-auto">
        <x-frontend::navigation.breadcrumbs :items="['FAQs' => null]" />

        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Frequently asked questions</h1>
        </div>

        <div class="space-y-8">
            @foreach($faqGroups as $group => $items)
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide mb-3" style="color:var(--fe-primary);">{{ $group }}</h2>
                    <div class="fe-card rounded-2xl divide-y" style="border-color:var(--fe-border);">
                        @foreach($items as $item)
                            <div x-data="{ open: false }" class="px-5" style="border-color:var(--fe-border);">
                                <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-3 py-4 text-left fe-focus-ring" :aria-expanded="open.toString()">
                                    <span class="text-sm font-semibold" style="color:var(--fe-text);">{{ $item['q'] }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs shrink-0 transition-transform" :class="open && 'rotate-180'" style="color:var(--fe-text-subtle);"></i>
                                </button>
                                <div x-show="open" x-cloak x-transition class="pb-4 text-sm" style="color:var(--fe-text-muted);">
                                    {{ $item['a'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <p class="text-sm" style="color:var(--fe-text-muted);">Still have questions?</p>
            <a href="{{ route('frontend.pages.contact') }}" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-primary);">Contact our team &rarr;</a>
        </div>
    </div>
@endsection
