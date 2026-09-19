@extends('backend.layouts.supplier')

@section('title', 'Add Product')
@section('breadcrumb', 'My Products / Add Product')

@section('body')

    <x-backend.page-header title="Add Product" subtitle="Create a new product or service for the EduShopify education marketplace." />

    @include('backend.supplier.catalog.listings.wizard', [
        'listing'         => null,
        'categoryOptions' => $categoryOptions,
        'brands'          => $brands,
        'units'           => $units,
        'currencies'      => $currencies ?? collect(),
    ])

@endsection
