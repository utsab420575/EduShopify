@extends('backend.layouts.supplier')

@section('title', 'Edit Listing — ' . $listing->name)
@section('breadcrumb', 'Catalog / Edit Listing')

@section('body')

    <x-backend.page-header title="Edit Listing" subtitle="{{ $listing->listing_number }} — {{ $listing->name }}" />

    @include('backend.supplier.catalog.listings.wizard', [
        'listing'         => $listing,
        'categoryOptions' => $categoryOptions,
        'brands'          => $brands,
        'units'           => $units,
        'currencies'      => $currencies ?? collect(),
        'existingValues'  => $existingValues,
    ])

@endsection
