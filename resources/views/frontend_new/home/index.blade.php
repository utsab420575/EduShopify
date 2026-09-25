@extends('frontend_new.layouts.app')

@section('title', 'Edushopify – Global Suppliers for Education')

@section('content')

    {{-- Header + Hero + Featured Suppliers together target exactly one full
         screen. --header-h is set by navbar.blade.php's inline script to the
         sticky header's real rendered height, so this only reserves the
         space actually left over (89px is just the pre-JS fallback) —
         flex-col lets Featured Suppliers grow to fill it instead of a hard
         height that would clip its card carousel on short viewports. --}}
    <div class="flex flex-col min-h-[calc(100dvh_-_var(--header-h,89px))]">
        {{-- Hero Section --}}
        @include('frontend_new.home.partial._hero')

        {{-- Featured Suppliers Section --}}
        @include('frontend_new.home.partial._featured_suppliers')
    </div>

    {{-- All Suppliers Section --}}
    @include('frontend_new.home.partial._all_suppliers')

    {{-- Why Choose Section --}}
    @include('frontend_new.home.partial._why_choose')

    {{-- Events Section --}}
    @include('frontend_new.home.partial._events')

@endsection
