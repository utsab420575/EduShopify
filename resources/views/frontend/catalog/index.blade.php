@extends('frontend.layouts.master')

@section('title', $title.' — EduShopify Marketplace')
@section('meta_description', $subtitle)

@section('content')
    <div class="fe-container py-6 sm:py-8">
        <x-frontend::navigation.breadcrumbs :items="[$title => null]" />

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">{{ $title }}</h1>
                <p class="text-sm mt-1" style="color:var(--fe-text-muted);">{{ $listings->total() }} {{ Str::plural('result', $listings->total()) }} &middot; {{ $subtitle }}</p>
            </div>

            <form method="GET" class="flex items-center gap-2">
                @foreach($filters as $key => $value)
                    @if(filled($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="fe-sort" class="sr-only">Sort by</label>
                <select id="fe-sort" name="sort" onchange="this.form.submit()" class="fe-focus-ring h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                    <option value="relevance" @selected($sort === 'relevance')>Relevance</option>
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="price_low" @selected($sort === 'price_low')>Price: Low to High</option>
                    <option value="price_high" @selected($sort === 'price_high')>Price: High to Low</option>
                    <option value="featured" @selected($sort === 'featured')>Featured</option>
                </select>
            </form>
        </div>

        <div class="mb-4">
            <x-frontend::search.filter-drawer>
                <input type="hidden" name="sort" value="{{ $sort }}">
                <div class="mb-4">
                    <label for="fe-catalog-q-mobile" class="sr-only">Search</label>
                    <input type="search" id="fe-catalog-q-mobile" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search in {{ strtolower($title) }}..." class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                </div>
                @include('frontend.catalog._filters')
            </x-frontend::search.filter-drawer>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <aside class="hidden lg:block lg:col-span-3">
                <div class="fe-card rounded-2xl p-5 sticky top-24">
                    <p class="text-sm font-semibold mb-4" style="color:var(--fe-text);">Filters</p>
                    <form method="GET">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <div class="mb-4">
                            <label for="fe-catalog-q" class="sr-only">Search</label>
                            <input type="search" id="fe-catalog-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search in {{ strtolower($title) }}..." class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
                        </div>
                        @include('frontend.catalog._filters')
                        <button type="submit" class="fe-btn-primary fe-focus-ring w-full mt-6 px-4 py-2.5 rounded-lg text-sm font-semibold">Apply Filters</button>
                        @if(collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty())
                            <a href="{{ url()->current() }}" class="block text-center mt-2 text-sm font-medium" style="color:var(--fe-text-muted);">Clear all</a>
                        @endif
                    </form>
                </div>
            </aside>

            <div class="lg:col-span-9">
                @if($listings->isEmpty())
                    <x-frontend::common.empty-state
                        icon="fa-box-open"
                        title="No listings match these filters"
                        description="Try adjusting or clearing your filters."
                        action-label="Clear filters"
                        :action-url="url()->current()"
                    />
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                        @foreach($listings as $listing)
                            <x-frontend::marketplace.listing-card :listing="$listing" />
                        @endforeach
                    </div>

                    <x-frontend::common.pagination :paginator="$listings" />
                @endif
            </div>
        </div>
    </div>
@endsection
