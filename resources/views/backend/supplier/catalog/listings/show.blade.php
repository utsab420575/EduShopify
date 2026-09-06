@extends('backend.layouts.supplier')

@section('title', $listing->name)
@section('breadcrumb', 'Catalog / Listing Details')

@section('body')

    @php
        $isLivePublic   = $listing->approval_status === 'approved' && $listing->is_active && $listing->published_at;
        $approvalStatus = $listing->approval_status;
    @endphp

    {{-- Page Header --}}
    <x-backend.page-header title="{{ $listing->name }}" subtitle="Listing ID: {{ $listing->listing_number }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <a href="{{ route('supplier.catalog.listings.index') }}"
                   class="text-xs font-medium px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> All Listings
                </a>

                @if($isLivePublic)
                    <a href="{{ route('frontend.listings.show', $listing) }}" target="_blank" rel="noopener"
                       class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View as Buyer
                    </a>
                @else
                    <button type="button" disabled title="Preview available once this listing is approved and published"
                            class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> View as Buyer
                    </button>
                @endif

                @if($approvalStatus === 'draft' || $approvalStatus === 'rejected')
                    <form method="POST" action="{{ route('supplier.catalog.listings.submit', $listing) }}">
                        @csrf
                        <button type="submit"
                                class="btn-primary text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Submit for Approval
                        </button>
                    </form>
                @endif

                <a href="{{ route('supplier.catalog.listings.edit', $listing) }}"
                   class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Listing
                </a>
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Status Alert Banner --}}
    @if($approvalStatus === 'draft')
        <div class="mb-5 flex items-start gap-3 rounded-xl bg-indigo-50 border border-indigo-200 px-5 py-4">
            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                <i class="fa-solid fa-circle-info text-indigo-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-indigo-900">This listing is a draft</p>
                <p class="text-xs text-indigo-700 mt-0.5">Complete your listing setup and click <strong>Submit for Approval</strong> when you're ready. It will then be reviewed by the platform team.</p>
            </div>
            <a href="{{ route('supplier.catalog.listings.edit', $listing) }}"
               class="flex-shrink-0 text-xs font-semibold text-indigo-700 hover:text-indigo-900 px-3 py-1.5 rounded-lg border border-indigo-300 hover:bg-indigo-100 transition">
                Continue Editing
            </a>
        </div>
    @elseif($approvalStatus === 'pending')
        <div class="mb-5 flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200 px-5 py-4">
            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                <i class="fa-solid fa-clock text-amber-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-amber-900">Awaiting Platform Review</p>
                <p class="text-xs text-amber-700 mt-0.5">Your listing has been submitted and is currently under review. You'll receive a notification once it is approved or if any changes are required.</p>
            </div>
        </div>
    @elseif($approvalStatus === 'rejected')
        <div class="mb-5 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 px-5 py-4">
            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fa-solid fa-circle-xmark text-red-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-red-900">Listing Was Rejected</p>
                @if($listing->rejection_reason)
                    <p class="text-xs text-red-700 mt-1"><span class="font-semibold">Reason:</span> {{ $listing->rejection_reason }}</p>
                @endif
                <p class="text-xs text-red-600 mt-1">Please address the feedback above, then re-submit for approval.</p>
            </div>
            <a href="{{ route('supplier.catalog.listings.edit', $listing) }}"
               class="flex-shrink-0 text-xs font-semibold text-red-700 hover:text-red-900 px-3 py-1.5 rounded-lg border border-red-300 hover:bg-red-100 transition">
                Fix & Resubmit
            </a>
        </div>
    @elseif($approvalStatus === 'approved' && !$isLivePublic)
        <div class="mb-5 flex items-start gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-4">
            <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-emerald-900">Approved — Awaiting Publication</p>
                <p class="text-xs text-emerald-700 mt-0.5">This listing has been approved. It will become publicly visible to buyers once it is published by the platform.</p>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    @include('backend.supplier.catalog.listings.partials.listing-preview-tabbed', [
        'listing'               => $listing,
        'groupedSpecifications' => $groupedSpecifications,
    ])

@endsection
