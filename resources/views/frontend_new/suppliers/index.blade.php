@extends('frontend_new.layouts.app')

@section('title', 'Suppliers – Edushopify')
@section('body_class', 'bg-gray-50')

@section('content')
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

  <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="{{ route('v2.home') }}" class="hover:text-gray-900">Home</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Suppliers</span>
  </nav>
  <h1 class="text-3xl font-bold text-gray-900 mb-6">Suppliers</h1>

  <div class="flex flex-col lg:flex-row gap-6 items-start">

    {{-- Sidebar filters --}}
    <aside class="w-full lg:w-64 shrink-0 lg:sticky lg:top-20">
      <form method="GET" class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Supplier Type</label>
          <select name="type" class="w-full border border-gray-200 rounded-md text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <option value="">All Types</option>
            @foreach($supplierTypes as $type)
              <option value="{{ $type->slug }}" @selected($filters['type'] === $type->slug)>{{ $type->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Search</label>
          <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Supplier name..." class="w-full border border-gray-200 rounded-md text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-400" />
        </div>
        @if($filters['category'])
          <input type="hidden" name="category" value="{{ $filters['category'] }}" />
          <p class="text-xs text-gray-500 mb-4">Filtered by category: <span class="font-medium text-gray-800">{{ $filters['category'] }}</span></p>
        @endif
        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2 rounded-md mb-2">Apply Filters</button>
        <a href="{{ route('v2.suppliers.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-800">Clear All</a>
      </form>
    </aside>

    {{-- Results --}}
    <div class="flex-1 w-full min-w-0">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $suppliers->total() }} {{ Str::plural('supplier', $suppliers->total()) }} found</p>
        <form method="GET">
          @foreach($filters as $key => $value)
            @if($value)<input type="hidden" name="{{ $key }}" value="{{ $value }}" />@endif
          @endforeach
          <select name="sort" onchange="this.form.submit()" class="border border-gray-200 rounded-md text-sm px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <option value="rating" @selected($sort === 'rating')>Highest Rated</option>
            <option value="newest" @selected($sort === 'newest')>Newest</option>
          </select>
        </form>
      </div>

      @if($suppliers->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          @foreach($suppliers as $supplier)
            @include('frontend_new.components.supplier-card', ['supplier' => $supplier])
          @endforeach
        </div>

        @if($suppliers->hasPages())
          <div class="flex items-center justify-between gap-4 mt-8">
            <div>
              @if($suppliers->onFirstPage())
                <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Previous</span>
              @else
                <a href="{{ $suppliers->previousPageUrl() }}" class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50">Previous</a>
              @endif
            </div>
            <p class="text-sm text-gray-500">Page {{ $suppliers->currentPage() }} of {{ $suppliers->lastPage() }}</p>
            <div>
              @if($suppliers->hasMorePages())
                <a href="{{ $suppliers->nextPageUrl() }}" class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-600 border border-gray-300 hover:bg-gray-50">Next</a>
              @else
                <span class="px-3.5 py-2 rounded-md text-sm font-medium text-gray-300 border border-gray-200">Next</span>
              @endif
            </div>
          </div>
        @endif
      @else
        <div class="text-center py-20 bg-white border border-gray-200 rounded-lg">
          <p class="font-semibold text-gray-900 mb-1">No suppliers found</p>
          <p class="text-sm text-gray-500">Try adjusting your filters.</p>
        </div>
      @endif
    </div>

  </div>
</main>
@endsection
