@extends('frontend.layouts.master')

@section('title', 'Categories — EduShopify Marketplace')
@section('meta_description', 'Browse all product and service categories on EduShopify.')

@section('content')
    <div class="fe-container py-6 sm:py-8">
        <x-frontend::navigation.breadcrumbs :items="['Categories' => null]" />

        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Browse categories</h1>
            <p class="text-sm mt-1" style="color:var(--fe-text-muted);">Explore the categories institutions source most.</p>
        </div>

        <form method="GET" class="mb-6 max-w-md">
            <label for="fe-category-search" class="sr-only">Search categories</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" id="fe-category-search" name="q" value="{{ $search }}" placeholder="Search categories..." class="fe-focus-ring w-full h-11 pl-11 pr-4 rounded-xl border text-sm" style="border-color:var(--fe-border);">
            </div>
        </form>

        @if($categories->isEmpty())
            <x-frontend::common.empty-state icon="fa-shapes" title="No categories found" description="Try a different search term." />
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $category)
                    <x-frontend::marketplace.category-card :category="$category" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
