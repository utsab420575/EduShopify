@extends('frontend_new.layouts.app')

@section('title', 'Browse by Category – Edushopify')

@section('content')

<div class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">
      <a href="{{ route('v2.home') }}" class="hover:text-gray-900">Home</a>
      <span class="text-gray-300">/</span>
      <span class="text-gray-900 font-medium">Categories</span>
    </nav>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Browse by Category</h1>
    <p class="text-sm text-gray-500 max-w-xl">Explore verified education suppliers and products organized by category.</p>
  </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
  @if($categories->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      @foreach($categories as $category)
        <a href="{{ route('v2.suppliers.index', ['category' => $category->slug]) }}"
           class="border border-gray-200 rounded-lg p-5 hover:bg-gray-50 hover:border-gray-300 transition-colors">
          {{-- Category::icon stores a Font Awesome class (e.g. "fa-shapes"),
               but this frontend uses inline SVG only (no icon font loaded)
               — a single generic glyph is used here rather than mapping
               each stored FA name to an SVG by hand. --}}
          <div class="w-12 h-12 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          </div>
          <p class="text-sm font-semibold text-gray-900 mb-1">{{ $category->name }}</p>
          <p class="text-xs text-gray-500 mb-3">{{ $category->supplier_count }} {{ Str::plural('Supplier', $category->supplier_count) }} · {{ $category->listing_count }} {{ Str::plural('Listing', $category->listing_count) }}</p>
          <span class="text-xs text-emerald-600 font-medium">Browse →</span>
        </a>
      @endforeach
    </div>
  @else
    <p class="text-sm text-gray-400 text-center py-16">No categories available yet.</p>
  @endif
</main>

@endsection
