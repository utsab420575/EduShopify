@extends('frontend_new.layouts.app')

@section('title', 'Open RFQs – Edushopify')
@section('body_class', 'bg-gray-50')

@section('content')
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

    {{-- Hero / Header: Title + Search --}}
    @include('frontend_new.rfqs.partials._hero')

    {{-- Main Content: RFQ List + Empty State + Pagination --}}
    @include('frontend_new.rfqs.partials._content')

</main>
@endsection
