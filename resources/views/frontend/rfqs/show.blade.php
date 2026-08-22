@extends('frontend.layouts.master')

@section('title', $opportunity->title.' ('.$opportunity->rfq_number.') — EduShopify')
@section('meta_description', 'Public RFQ opportunity summary on EduShopify.')

@section('content')
    @php
        $location = collect([$opportunity->delivery_city, $opportunity->delivery_state, $opportunity->delivery_country])->filter()->implode(', ');
    @endphp

    <div class="fe-container py-6 sm:py-8 max-w-4xl mx-auto">
        <x-frontend::navigation.breadcrumbs :items="[
            'Opportunities' => route('frontend.rfqs.index'),
            $opportunity->rfq_number => null,
        ]" />

        <div class="fe-card rounded-2xl p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                <div>
                    <p class="text-xs font-mono mb-1" style="color:var(--fe-text-subtle);">{{ $opportunity->rfq_number }}</p>
                    <h1 class="text-xl sm:text-2xl font-bold" style="font-family:var(--font-display);color:var(--fe-text);">{{ $opportunity->title }}</h1>
                </div>
                <x-frontend::common.badge variant="verified"><span class="w-1.5 h-1.5 rounded-full" style="background:var(--fe-primary);"></span> Open</x-frontend::common.badge>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-5 border-y" style="border-color:var(--fe-border);">
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Category</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ $opportunity->category_summary ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Delivery Location</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ $location ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Items</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ $opportunity->item_count }}</p>
                </div>
                <div>
                    <p class="text-xs" style="color:var(--fe-text-muted);">Quotation Deadline</p>
                    <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ $opportunity->quotation_deadline?->format('M j, Y') ?? '—' }}</p>
                </div>
            </div>

            <div class="py-5 space-y-3 text-sm" style="color:var(--fe-text-muted);">
                @if($opportunity->item_types)
                    <p><strong style="color:var(--fe-text);">Item types:</strong> {{ str_replace(',', ', ', $opportunity->item_types) }}</p>
                @endif
                @if($opportunity->expected_delivery_date)
                    <p><strong style="color:var(--fe-text);">Expected delivery:</strong> {{ $opportunity->expected_delivery_date->format('M j, Y') }}</p>
                @endif
                <p><strong style="color:var(--fe-text);">Published:</strong> {{ $opportunity->published_at?->format('M j, Y') ?? '—' }}</p>
                <p><strong style="color:var(--fe-text);">Quotations received so far:</strong> {{ $opportunity->quotations_count }}</p>
            </div>

            <div class="rounded-xl p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4 justify-between" style="background:var(--fe-surface-soft);">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-lock mt-0.5" style="color:var(--fe-text-subtle);"></i>
                    <p class="text-sm" style="color:var(--fe-text-muted);">
                        Full item specifications, buyer details and quotation submission are only available to eligible, authenticated suppliers.
                    </p>
                </div>
                @auth
                    <a href="{{ route('frontend.handoff.submit-quotation', $opportunity->rfq_number) }}" class="fe-btn-primary fe-focus-ring shrink-0 px-5 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap">
                        View Full Opportunity
                    </a>
                @else
                    <a href="{{ route('frontend.handoff.submit-quotation', $opportunity->rfq_number) }}" class="fe-btn-primary fe-focus-ring shrink-0 px-5 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap">
                        Login / Register to Continue
                    </a>
                @endauth
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('frontend.rfqs.index') }}" class="fe-focus-ring text-sm font-semibold" style="color:var(--fe-primary);">&larr; Back to all opportunities</a>
        </div>
    </div>
@endsection
