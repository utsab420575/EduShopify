@extends('backend.layouts.supplier')

@section('title', 'Edit Product — ' . $listing->name)
@section('breadcrumb', 'My Products / Edit Product')

@section('body')

    <x-backend.page-header title="Edit Product" subtitle="{{ $listing->listing_number }} — {{ $listing->name }}" />

    @include('backend.supplier.catalog.listings.wizard', [
        'listing'         => $listing,
        'categoryOptions' => $categoryOptions,
        'brands'          => $brands,
        'units'           => $units,
        'currencies'      => $currencies ?? collect(),
        'existingValues'  => $existingValues,
    ])

@endsection
