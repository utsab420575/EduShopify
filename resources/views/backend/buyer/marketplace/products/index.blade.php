@extends('backend.layouts.buyer')

@section('title', 'Products')
@section('breadcrumb', 'Marketplace / Products')

@section('body')

    <x-backend.page-header title="Products" subtitle="Browse published products from suppliers on EduShopify." />

    <x-backend.form-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search products..." class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>
            <x-backend.select name="category" label="Category" placeholder="All Categories">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected($category === $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-backend.select>
            <x-backend.select name="brand" label="Brand" placeholder="All Brands">
                @foreach($brands as $b)
                    <option value="{{ $b->id }}" @selected($brand === $b->id)>{{ $b->name }}</option>
                @endforeach
            </x-backend.select>
            <div class="lg:col-span-4 flex justify-end">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </div>
        </form>
    </x-backend.form-card>

    @if($listings->isEmpty())
        <x-backend.empty-state icon="fa-boxes-stacked" title="No products found" description="Try adjusting your search or filters." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($listings as $listing)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <a href="{{ route('buyer.marketplace.products.show', $listing) }}" class="block h-32 bg-gray-50 flex items-center justify-center">
                        <i class="fa-solid fa-box text-3xl text-gray-300"></i>
                    </a>
                    <div class="p-3">
                        <a href="{{ route('buyer.marketplace.products.show', $listing) }}" class="text-sm font-medium text-gray-900 line-clamp-2 hover:underline">{{ $listing->name }}</a>
                        <p class="text-xs text-gray-400 mt-1 truncate">{{ $listing->supplierAccount?->supplierProfile?->display_name }}</p>
                        @if($listing->base_price)
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ number_format($listing->base_price, 2) }} {{ $listing->currency_code }}</p>
                        @endif
                        <div class="flex items-center justify-between mt-2">
                            <a href="{{ route('buyer.rfqs.create', ['listing' => $listing->id]) }}" class="text-xs font-medium" style="color:var(--theme-primary)">Request Quote</a>
                            <form method="POST" action="{{ route('buyer.saved-items.toggle') }}">
                                @csrf
                                <input type="hidden" name="type" value="listing">
                                <input type="hidden" name="id" value="{{ $listing->id }}">
                                <button type="submit" class="text-sm {{ $savedIds->contains($listing->id) ? 'text-red-500' : 'text-gray-300 hover:text-red-400' }}"><i class="fa-solid fa-bookmark"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <x-backend.pagination :paginator="$listings" />
        </div>
    @endif

@endsection
