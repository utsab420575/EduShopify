@extends('backend.layouts.supplier')

@section('title', 'Add Catalog Listing')
@section('breadcrumb', 'Catalog / Add Listing')

@section('body')

    <x-backend.page-header title="Add Listing" subtitle="Create a new product or service listing for the EduShopify education marketplace." />

    @include('backend.supplier.catalog.listings.wizard', [
        'listing'         => null,
        'categoryOptions' => $categoryOptions,
        'brands'          => $brands,
        'units'           => $units,
        'currencies'      => $currencies ?? collect(),
    ])

@endsection
