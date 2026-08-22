@extends('frontend.layouts.master')

@section('title', 'About EduShopify')
@section('meta_description', 'EduShopify is a B2B marketplace connecting educational institutions with verified suppliers through structured RFQ procurement.')

@section('content')
    <div class="fe-container py-10 sm:py-14">
        <x-frontend::navigation.breadcrumbs :items="['About' => null]" />

        <div class="max-w-3xl mx-auto text-center mb-14">
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Procurement, built for education</h1>
            <p class="mt-4 text-base sm:text-lg" style="color:var(--fe-text-muted);">
                EduShopify is a B2B marketplace that connects educational institutions with verified suppliers of products and services, through a structured, transparent RFQ procurement process.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-4xl mx-auto mb-14">
            <div class="fe-card rounded-2xl p-6">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>
                <h3 class="text-base font-semibold mb-1.5" style="color:var(--fe-text);">For institutional buyers</h3>
                <p class="text-sm" style="color:var(--fe-text-muted);">Post structured RFQs, receive comparable quotations, and manage sourcing as a team with roles and permissions.</p>
            </div>
            <div class="fe-card rounded-2xl p-6">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
                    <i class="fa-solid fa-store"></i>
                </span>
                <h3 class="text-base font-semibold mb-1.5" style="color:var(--fe-text);">For suppliers</h3>
                <p class="text-sm" style="color:var(--fe-text-muted);">Publish your catalog, get matched with eligible RFQ opportunities, and grow your reach into the education sector.</p>
            </div>
        </div>

        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-xl font-bold mb-3" style="font-family:var(--font-display);color:var(--fe-text);">Our procurement principles</h2>
            <p class="text-sm sm:text-base" style="color:var(--fe-text-muted);">
                Every RFQ, quotation and award on EduShopify follows a structured workflow — built for transparency, fair comparison, and accountable procurement decisions.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('frontend.handoff.post-rfq') }}" class="fe-btn-primary fe-focus-ring px-5 py-2.5 rounded-lg text-sm font-semibold">Post an RFQ</a>
                <a href="{{ route('frontend.pages.contact') }}" class="fe-focus-ring px-5 py-2.5 rounded-lg text-sm font-semibold border bg-white" style="border-color:var(--fe-border-strong);color:var(--fe-text);">Contact Us</a>
            </div>
        </div>
    </div>
@endsection
