@extends('frontend.layouts.master')

@section('title', 'Supplier Directory — EduShopify Marketplace')
@section('meta_description', 'Browse verified education suppliers on EduShopify.')

@section('content')
    <div class="fe-container py-6 sm:py-8">
        <x-frontend::navigation.breadcrumbs :items="['Suppliers' => null]" />

        <div class="mb-6">
            <h1 class="text-2xl sm:text-[28px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">Supplier Directory</h1>
            <p class="text-sm mt-1" style="color:var(--fe-text-muted);">{{ $suppliers->total() }} verified {{ Str::plural('supplier', $suppliers->total()) }} ready to quote.</p>
        </div>

        <form method="GET" class="fe-card rounded-2xl p-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search suppliers..." class="fe-focus-ring w-full h-11 pl-11 pr-4 rounded-xl border text-sm" style="border-color:var(--fe-border);">
            </div>
            <select name="type" class="fe-focus-ring h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                <option value="">All Types</option>
                @foreach($supplierTypes as $type)
                    <option value="{{ $type->slug }}" @selected(($filters['type'] ?? '') === $type->slug)>{{ $type->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <select name="sort" class="fe-focus-ring flex-1 h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
                    <option value="rating" @selected($sort === 'rating')>Top Rated</option>
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                </select>
                <button type="submit" class="fe-btn-primary fe-focus-ring px-4 rounded-xl text-sm font-semibold shrink-0">Filter</button>
            </div>
        </form>

        @if($suppliers->isEmpty())
            <x-frontend::common.empty-state
                icon="fa-store-slash"
                title="No suppliers found"
                description="Try a different search term or filter."
                action-label="Clear filters"
                :action-url="route('frontend.suppliers.index')"
            />
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($suppliers as $supplier)
                    <x-frontend::marketplace.supplier-card :supplier="$supplier" />
                @endforeach
            </div>

            <x-frontend::common.pagination :paginator="$suppliers" />
        @endif
    </div>
@endsection
