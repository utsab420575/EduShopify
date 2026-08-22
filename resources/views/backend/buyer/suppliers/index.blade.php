@extends('backend.layouts.buyer')

@section('title', 'Supplier Directory')
@section('breadcrumb', 'Marketplace / Suppliers')

@section('body')

    <x-backend.page-header title="Supplier Directory" subtitle="Discover verified suppliers on EduShopify." />

    <x-backend.form-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search suppliers..." class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>
            <x-backend.select name="type" label="Supplier Type" placeholder="All Types">
                @foreach($supplierTypes as $t)
                    <option value="{{ $t->id }}" @selected($type === $t->id)>{{ $t->name }}</option>
                @endforeach
            </x-backend.select>
            <x-backend.select name="country" label="Country" placeholder="All Countries">
                @foreach($countries as $c)
                    <option value="{{ $c->id }}" @selected($country === $c->id)>{{ $c->name }}</option>
                @endforeach
            </x-backend.select>
            <div class="lg:col-span-4 flex justify-end">
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </div>
        </form>
    </x-backend.form-card>

    @if($suppliers->isEmpty())
        <x-backend.empty-state icon="fa-store" title="No suppliers found" description="Try adjusting your search or filters." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($suppliers as $supplier)
                @php($profile = $supplier->supplierProfile)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=eef2ff&color=4f46e5' }}" class="w-12 h-12 rounded-lg object-contain bg-white border border-gray-100" alt="">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $profile?->display_name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $profile?->country?->name }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('buyer.suppliers.save', $supplier) }}">
                            @csrf
                            <button type="submit" class="text-lg {{ $savedIds->contains($supplier->id) ? 'text-red-500' : 'text-gray-300 hover:text-red-400' }}">
                                <i class="fa-solid fa-heart"></i>
                            </button>
                        </form>
                    </div>

                    @if($profile?->rating)
                        <div class="flex items-center gap-1 mt-2 text-xs text-amber-500">
                            <i class="fa-solid fa-star"></i> {{ number_format($profile->rating, 1) }} <span class="text-gray-400">({{ $profile->reviews_count }})</span>
                        </div>
                    @endif

                    @if($supplier->supplierTypes->isNotEmpty())
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($supplier->supplierTypes as $t)
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $t->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($profile?->description)
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2">{{ $profile->description }}</p>
                    @endif

                    <a href="{{ route('buyer.suppliers.show', $supplier) }}" class="block text-center text-sm font-medium mt-3 pt-3 border-t border-gray-100" style="color:var(--theme-primary)">View Profile</a>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <x-backend.pagination :paginator="$suppliers" />
        </div>
    @endif

@endsection
