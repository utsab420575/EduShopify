@extends('frontend_new.layouts.app')

@section('title', 'Edushopify – Global Suppliers for Education')

@section('content')

    {{-- Hero Section --}}
    @include('frontend_new.home.partial._hero')

    {{-- Featured Suppliers Section --}}
    @include('frontend_new.home.partial._featured_suppliers')

    {{-- All Suppliers Section --}}
    @include('frontend_new.home.partial._all_suppliers')

    {{-- Why Choose Section --}}
    @include('frontend_new.home.partial._why_choose')

    {{-- Events Section --}}
    @include('frontend_new.home.partial._events')

@endsection
