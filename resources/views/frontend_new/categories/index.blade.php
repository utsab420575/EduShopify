@extends('frontend_new.layouts.app')

@section('title', 'Marketplace – Browse Education Products | Edushopify')

@section('body_class', 'bg-gray-50')

@section('content')

    {{-- Hero: title + live search --}}
    @include('frontend_new.categories.partials._hero')

    {{-- Main: tabs + product grid + pagination --}}
    @include('frontend_new.categories.partials._content')

@endsection
