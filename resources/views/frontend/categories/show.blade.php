@extends('frontend.layouts.master')

@section('title', $category->name.' — EduShopify Marketplace')
@section('meta_description', Str::limit(strip_tags($category->description ?? ('Browse '.$category->name.' listings on EduShopify.')), 155))

@section('content')
    <div class="fe-container py-6 sm:py-8">
        <x-frontend::navigation.breadcrumbs :items="[
            'Categories' => route('frontend.categories.index'),
            ...($category->parent ? [$category->parent->name => route('frontend.categories.show', $category->parent->slug)] : []),
            $category->name => null,
        ]" />

        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-sm mt-1 max-w-2xl" style="color:var(--fe-text-muted);">{{ $category->description }}</p>
            @endif
        </div>

        @if($category->children->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-8">
                @foreach($category->children as $child)
                    <a href="{{ route('frontend.categories.show', $child->slug) }}" class="fe-focus-ring px-3.5 py-1.5 rounded-full text-xs font-medium bg-white border hover:border-[--fe-primary] hover:text-[--fe-primary]" style="border-color:var(--fe-border);color:var(--fe-text-muted);">
                    {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if($listings->isEmpty())
            <x-frontend::common.empty-state
                icon="fa-box-open"
                title="No listings in this category yet"
                description="Check back soon, or browse the full marketplace."
                action-label="Browse marketplace"
                :action-url="route('frontend.catalog.index')"
            />
        @else
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($listings as $listing)
                    <x-frontend::marketplace.listing-card :listing="$listing" />
                @endforeach
            </div>

            <x-frontend::common.pagination :paginator="$listings" />
        @endif
    </div>
@endsection
