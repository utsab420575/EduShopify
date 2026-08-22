@extends('backend.layouts.buyer')

@section('title', 'Services')
@section('breadcrumb', 'Marketplace / Services')

@section('body')

    <x-backend.page-header title="Services" subtitle="Browse published services from suppliers on EduShopify." />

    <x-backend.form-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search services..." class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>
            <x-backend.select name="category" label="Category" placeholder="All Categories">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected($category === $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-backend.select>
            <div class="lg:col-span-3 flex justify-end">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </div>
        </form>
    </x-backend.form-card>

    @if($listings->isEmpty())
        <x-backend.empty-state icon="fa-briefcase" title="No services found" description="Try adjusting your search or filters." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($listings as $listing)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <a href="{{ route('buyer.marketplace.services.show', $listing) }}" class="text-sm font-semibold text-gray-900 hover:underline">{{ $listing->name }}</a>
                    <p class="text-xs text-gray-400 mt-1">{{ $listing->supplierAccount?->supplierProfile?->display_name }}</p>
                    @if($listing->serviceDetail?->service_mode)
                        <span class="inline-block text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 mt-2">{{ ucfirst($listing->serviceDetail->service_mode) }}</span>
                    @endif
                    @if($listing->short_description)
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $listing->short_description }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        <a href="{{ route('buyer.rfqs.create', ['listing' => $listing->id]) }}" class="text-xs font-medium" style="color:var(--theme-primary)">Request Quote</a>
                        <form method="POST" action="{{ route('buyer.saved-items.toggle') }}">
                            @csrf
                            <input type="hidden" name="type" value="listing">
                            <input type="hidden" name="id" value="{{ $listing->id }}">
                            <button type="submit" class="text-sm {{ $savedIds->contains($listing->id) ? 'text-red-500' : 'text-gray-300 hover:text-red-400' }}"><i class="fa-solid fa-bookmark"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <x-backend.pagination :paginator="$listings" />
        </div>
    @endif

@endsection
