@extends('backend.layouts.admin')

@section('title', $listing->name . ' — Listing Review')
@section('breadcrumb', 'Catalog & Taxonomy / Listings / ' . $listing->name)

@section('body')
    @include('backend.admin.catalog.listings._panel', [
        'listing' => $listing,
        'groupedSpecifications' => $groupedSpecifications,
    ])
@endsection
