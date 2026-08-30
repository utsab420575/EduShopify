@extends('backend.layouts.supplier')

@section('title', 'Catalog Listings')
@section('breadcrumb', 'Catalog / All Listings')

@section('body')

    <x-backend.page-header title="Catalog Listings" subtitle="Manage your educational product and service listings, pricing, and availability.">
        <x-slot:actions>
            <a href="{{ route('supplier.catalog.listings.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Listing
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('supplier.catalog.listings.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, SKU, or listing ID..." class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <select name="type" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Types</option>
                <option value="product" @selected($type === 'product')>Products</option>
                <option value="service" @selected($type === 'service')>Services</option>
            </select>
            <select name="status" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Statuses</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
                <option value="pending" @selected($status === 'pending')>Pending Approval</option>
                <option value="approved" @selected($status === 'approved')>Approved / Active</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
            </select>
            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2.5 rounded-lg">
                Filter
            </button>
            @if($search || $type || $status)
                <a href="{{ route('supplier.catalog.listings.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2">Reset</a>
            @endif
        </form>
    </div>

    {{-- Listings Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($listings->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-box-open" title="No listings found" description="Create your first catalog listing to start selling to institutions.">
                    <x-slot:actions>
                        <a href="{{ route('supplier.catalog.listings.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Add Listing</a>
                    </x-slot:actions>
                </x-backend.empty-state>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Listing</th>
                            <th class="px-3 py-3.5 font-semibold">Type</th>
                            <th class="px-3 py-3.5 font-semibold">Category</th>
                            <th class="px-3 py-3.5 font-semibold">Price</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($listings as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('supplier.catalog.listings.show', $item) }}" class="font-semibold text-gray-900 hover:text-indigo-600 truncate block max-w-xs">
                                        {{ $item->name }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->listing_number }} @if($item->sku) &middot; SKU: {{ $item->sku }} @endif</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $item->isProduct() ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                        {{ ucfirst($item->listing_type) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-600">
                                    {{ $item->mainCategory?->name ?? 'Uncategorized' }}
                                </td>
                                <td class="px-3 py-3.5 font-semibold text-gray-900 text-xs">
                                    @if($item->base_price)
                                        {{ $item->currency_code }} {{ number_format($item->base_price, 2) }}
                                    @else
                                        <span class="text-gray-400 uppercase text-[10px]">{{ $item->pricingType?->name ?? str_replace('_', ' ', $item->pricing_type ?? '') }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-backend.status-badge :status="$item->approval_status" />
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('supplier.catalog.listings.show', $item) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 rounded" title="View details">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('supplier.catalog.listings.edit', $item) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 rounded" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('supplier.catalog.listings.destroy', $item) }}" onsubmit="return confirm('Delete this listing?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded" title="Delete">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($listings->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $listings->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
