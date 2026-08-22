@extends('frontend.layouts.master')

@section('title', 'How It Works — EduShopify')
@section('meta_description', 'Learn how EduShopify connects education buyers with verified suppliers through structured RFQ procurement.')

@section('content')
    <div class="fe-container py-10 sm:py-14 max-w-4xl mx-auto" x-data="{ track: 'buyer' }">
        <x-frontend::navigation.breadcrumbs :items="['How It Works' => null]" />

        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">How EduShopify works</h1>
            <p class="mt-3 text-base max-w-2xl mx-auto" style="color:var(--fe-text-muted);">A structured RFQ marketplace connecting educational institutions with verified suppliers.</p>
        </div>

        <div class="flex justify-center mb-10">
            <div class="inline-flex items-center bg-white rounded-xl p-1 gap-1 border" style="border-color:var(--fe-border);">
                <button @click="track = 'buyer'" :class="track === 'buyer' ? 'text-white' : 'text-slate-600'" :style="track === 'buyer' ? 'background:var(--fe-primary)' : ''" class="px-6 py-2 rounded-lg text-sm font-semibold transition-colors">For Buyers</button>
                <button @click="track = 'supplier'" :class="track === 'supplier' ? 'text-white' : 'text-slate-600'" :style="track === 'supplier' ? 'background:var(--fe-primary)' : ''" class="px-6 py-2 rounded-lg text-sm font-semibold transition-colors">For Suppliers</button>
            </div>
        </div>

        <div x-show="track === 'buyer'" x-cloak class="space-y-4">
            @foreach([
                ['title' => 'Register', 'text' => 'Create your account and complete Buyer eligibility.'],
                ['title' => 'Discover the marketplace', 'text' => 'Browse products, services and suppliers across education categories.'],
                ['title' => 'Create an RFQ', 'text' => 'Post a structured request describing what your institution needs.'],
                ['title' => 'Receive quotations', 'text' => 'Verified suppliers respond with competitive quotations.'],
                ['title' => 'Compare, shortlist or request revisions', 'text' => 'Review quotations side by side and ask for changes if needed.'],
                ['title' => 'Award a supplier', 'text' => 'Select the winning quotation and award the business.'],
                ['title' => 'Supplier accepts, PO is created', 'text' => 'Once accepted, a purchase order documents the agreement.'],
                ['title' => 'Fulfilment & review', 'text' => 'Fulfilment happens outside the platform in Phase 1. Leave a review once eligible.'],
            ] as $i => $step)
                <div class="flex gap-4 fe-card rounded-2xl p-5">
                    <span class="w-9 h-9 rounded-full flex items-center justify-center font-semibold shrink-0" style="background:var(--fe-primary-soft);color:var(--fe-primary);">{{ $i + 1 }}</span>
                    <div>
                        <p class="text-sm font-semibold" style="color:var(--fe-text);">{{ $step['title'] }}</p>
                        <p class="text-sm mt-0.5" style="color:var(--fe-text-muted);">{{ $step['text'] }}</p>
                    </div>
                </div>
            @endforeach
            <div class="text-center pt-4">
                <a href="{{ route('frontend.handoff.post-rfq') }}" class="fe-btn-primary fe-focus-ring inline-block px-6 py-3 rounded-lg text-sm font-semibold">Post an RFQ</a>
            </div>
        </div>

        <div x-show="track === 'supplier'" x-cloak class="space-y-4">
            @foreach([
                ['title' => 'Register', 'text' => 'Create your account and submit a Supplier application.'],
                ['title' => 'Complete profile & documents', 'text' => 'Provide company profile details and required verification documents.'],
                ['title' => 'Admin approval', 'text' => 'Our team reviews and approves eligible suppliers.'],
                ['title' => 'Select a subscription', 'text' => 'Choose a plan that matches your business needs.'],
                ['title' => 'Publish listings', 'text' => 'Create products and services, then submit them for approval.'],
                ['title' => 'Access eligible RFQs', 'text' => 'Receive RFQ opportunities matched to your listings and plan.'],
                ['title' => 'Submit quotations', 'text' => 'Respond to open RFQs with competitive quotations, revise if requested.'],
                ['title' => 'Win business', 'text' => 'Accept awards, fulfil the purchase order, and grow your reputation.'],
            ] as $i => $step)
                <div class="flex gap-4 fe-card rounded-2xl p-5">
                    <span class="w-9 h-9 rounded-full flex items-center justify-center font-semibold shrink-0" style="background:var(--fe-primary-soft);color:var(--fe-primary);">{{ $i + 1 }}</span>
                    <div>
                        <p class="text-sm font-semibold" style="color:var(--fe-text);">{{ $step['title'] }}</p>
                        <p class="text-sm mt-0.5" style="color:var(--fe-text-muted);">{{ $step['text'] }}</p>
                    </div>
                </div>
            @endforeach
            <div class="text-center pt-4">
                <a href="{{ route('register') }}" class="fe-btn-primary fe-focus-ring inline-block px-6 py-3 rounded-lg text-sm font-semibold">Become a Supplier</a>
            </div>
        </div>
    </div>
@endsection
