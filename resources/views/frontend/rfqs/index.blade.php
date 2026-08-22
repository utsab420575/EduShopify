@extends('frontend.layouts.master')

@section('title', 'RFQ Opportunities — EduShopify Marketplace')
@section('meta_description', 'Browse open, publicly visible RFQ sourcing opportunities on EduShopify.')

@section('content')
    <div class="fe-container py-6 sm:py-8">
        <x-frontend::navigation.breadcrumbs :items="['Opportunities' => null]" />

        <div class="flex items-center gap-2 mb-2">
            <span class="w-2 h-2 rounded-full" style="background:var(--fe-primary);"></span>
            <span class="text-xs font-semibold uppercase tracking-wide" style="color:var(--fe-primary);">Live Sourcing</span>
        </div>
        <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight mb-1" style="font-family:var(--font-display);color:var(--fe-text);">RFQ Opportunities</h1>
        <p class="text-sm mb-6" style="color:var(--fe-text-muted);">{{ $opportunities->total() }} open {{ Str::plural('opportunity', $opportunities->total()) }}. Suppliers must log in or register to submit a quotation.</p>

        <form method="GET" class="fe-card rounded-2xl p-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search opportunities..." class="fe-focus-ring w-full h-11 pl-11 pr-4 rounded-xl border text-sm" style="border-color:var(--fe-border);">
            </div>
            <input type="text" name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Category" class="fe-focus-ring h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
            <div class="flex gap-2">
                <select name="sort" class="fe-focus-ring flex-1 h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                    <option value="deadline" @selected($sort === 'deadline')>Closing Soonest</option>
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                </select>
                <button type="submit" class="fe-btn-primary fe-focus-ring px-4 rounded-xl text-sm font-semibold shrink-0">Filter</button>
            </div>
        </form>

        @if($opportunities->isEmpty())
            <x-frontend::common.empty-state
                icon="fa-file-circle-question"
                title="No public RFQ opportunities match your search"
                description="Try a different search term or check back soon."
                action-label="Clear filters"
                :action-url="route('frontend.rfqs.index')"
            />
        @else
            <div class="space-y-3">
                @foreach($opportunities as $opportunity)
                    <x-frontend::marketplace.rfq-card :opportunity="$opportunity" />
                @endforeach
            </div>

            <x-frontend::common.pagination :paginator="$opportunities" />
        @endif
    </div>
@endsection
