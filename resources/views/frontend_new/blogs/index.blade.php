@extends('frontend_new.layouts.app')

@section('title', 'Education Insights & Blog – Global Procurement & Innovation | Edushopify')

@section('body_class', 'bg-gray-50')

@section('content')

    {{-- Hero: title + live search --}}
    @include('frontend_new.blogs.partials._hero')

    {{-- Main: category tabs + articles grid + live ajax search + pagination --}}
    @include('frontend_new.blogs.partials._content')

@endsection
