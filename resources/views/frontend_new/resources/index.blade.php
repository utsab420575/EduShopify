@extends('frontend_new.layouts.app')

@section('title', 'Education Resources Center | Edushopify')

@section('body_class', 'bg-gray-50')

@section('content')

    {{-- 1. Hero: Title, Search, Popular Tags, and Statistics --}}
    @include('frontend_new.resources.partials._hero')

    {{-- 2. 7 Quick-Access Resource Category Cards --}}
    @include('frontend_new.resources.partials._categories')

    {{-- 3. 4-Column Content: Featured Guides, Latest Insights, Upcoming Exhibitions, RFQ Templates --}}
    @include('frontend_new.resources.partials._content')

    {{-- 4. Value Propositions / Trust Features --}}
    @include('frontend_new.resources.partials._features')

@endsection
