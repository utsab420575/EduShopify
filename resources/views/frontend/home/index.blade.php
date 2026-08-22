@extends('frontend.layouts.master')

@section('title', 'EduShopify — B2B Education Procurement Marketplace')
@section('meta_description', 'Discover verified suppliers, products and services for educational institutions. Post an RFQ or browse the marketplace on EduShopify.')

@section('content')
    @include('frontend.home.sections._hero', ['topCategories' => $topCategories])
    @include('frontend.home.sections._top_categories', ['topCategories' => $topCategories])
    @include('frontend.home.sections._featured_products', ['featuredProducts' => $featuredProducts])
    @include('frontend.home.sections._featured_services', ['featuredServices' => $featuredServices])
    @include('frontend.home.sections._featured_suppliers', ['featuredSuppliers' => $featuredSuppliers])
    @include('frontend.home.sections._rfq_opportunities', ['openRfqOpportunities' => $openRfqOpportunities])
    @include('frontend.home.sections._how_it_works')
    @include('frontend.home.sections._why_edushopify')
    @include('frontend.home.sections._buyer_supplier_cta')
    @include('frontend.home.sections._pricing_teaser', ['featuredPlans' => $featuredPlans])
    @include('frontend.home.sections._marketplace_stats', ['stats' => $stats])
@endsection
