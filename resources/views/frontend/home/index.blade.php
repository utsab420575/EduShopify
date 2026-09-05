@extends('frontend.layouts.master')

@section('title', 'EduShopify — B2B Education Procurement Marketplace')
@section('meta_description', 'Discover verified suppliers, products and services for educational institutions. Post an RFQ or browse the marketplace on EduShopify.')

@section('content')
    @include('frontend.home.sections._hero')
    @include('frontend.home.sections._stats_bar', ['stats' => $stats])
    @include('frontend.home.sections._featured_suppliers', ['featuredSuppliers' => $featuredSuppliers])
    @include('frontend.home.sections._all_suppliers', ['allSuppliers' => $allSuppliers, 'tabs' => $allSuppliersTabs])
    @include('frontend.home.sections._why_choose_edushopify')
    @include('frontend.home.sections._events')
@endsection
