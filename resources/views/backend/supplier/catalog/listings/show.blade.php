@extends('backend.layouts.supplier')

@section('title', $listing->name)
@section('breadcrumb', 'Catalog / Listing Details')

@section('body')

    <x-backend.page-header title="{{ $listing->name }}" subtitle="Listing ID: {{ $listing->listing_number }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if($listing->approval_status === 'draft' || $listing->approval_status === 'rejected')
                    <form method="POST" action="{{ route('supplier.catalog.listings.submit', $listing) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Submit for Approval
                        </button>
                    </form>
                @endif
                <a href="{{ route('supplier.catalog.listings.edit', $listing) }}" class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Listing
                </a>
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    @include('backend.supplier.catalog.listings.partials.listing-preview', [
        'listing' => $listing,
        'groupedSpecifications' => $groupedSpecifications,
    ])

@endsection
