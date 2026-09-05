@extends('frontend_new.layouts.app')

@section('title', 'Suppliers – Edushopify')
@section('body_class', 'bg-gray-50')

@section('content')

    {{-- Hero: title + live search --}}
    @include('frontend_new.suppliers.partials._hero')

    {{-- Main: category tabs + supplier grid + pagination --}}
    @include('frontend_new.suppliers.partials._content')

@endsection
