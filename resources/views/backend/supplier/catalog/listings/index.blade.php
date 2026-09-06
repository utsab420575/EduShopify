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

    <x-backend.table>
        <x-slot:toolbar>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
                {{-- Left: Record Badge & Utility Actions --}}
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-900">Listings</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $listings->total() }} total</span>
                    <button type="button" onclick="window.print()" class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center gap-1.5 ml-2 transition">
                        <i class="fa-solid fa-print text-gray-500"></i> Print
                    </button>
                </div>

                {{-- Right: Live Search & Contextual Filters --}}
                <form method="GET" action="{{ route('supplier.catalog.listings.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, SKU, or ID..."
                               @input.debounce.300ms="$el.form.submit()"
                               class="focus-accent w-full sm:w-60 pl-9 pr-3 py-1.5 text-xs rounded-lg border border-gray-300">
                    </div>

                    <select name="type" onchange="this.form.submit()" class="focus-accent text-xs rounded-lg border border-gray-300 px-2.5 py-1.5 bg-white">
                        <option value="">All Types</option>
                        <option value="product" @selected($type === 'product')>Products</option>
                        <option value="service" @selected($type === 'service')>Services</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="focus-accent text-xs rounded-lg border border-gray-300 px-2.5 py-1.5 bg-white">
                        <option value="">All Statuses</option>
                        <option value="draft" @selected($status === 'draft')>Draft</option>
                        <option value="pending" @selected($status === 'pending')>Pending Approval</option>
                        <option value="approved" @selected($status === 'approved')>Approved / Active</option>
                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                    </select>

                    @if($search || $type || $status || request('sort'))
                        <a href="{{ route('supplier.catalog.listings.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2 font-medium">Reset</a>
                    @endif
                </form>
            </div>
        </x-slot:toolbar>

        @if($listings->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-box-open" title="No listings found" description="Create your first catalog listing to start selling to institutions.">
                    <x-slot:actions>
                        <a href="{{ route('supplier.catalog.listings.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Add Listing
                        </a>
                    </x-slot:actions>
                </x-backend.empty-state>
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-12 text-center">SL</th>
                    <x-backend.sortable-th column="name" label="Listing" :current-sort="$sort" :current-direction="$direction" />
                    <x-backend.sortable-th column="type" label="Type" :current-sort="$sort" :current-direction="$direction" />
                    <x-backend.sortable-th column="category" label="Category" :current-sort="$sort" :current-direction="$direction" />
                    <x-backend.sortable-th column="price" label="Price" :current-sort="$sort" :current-direction="$direction" />
                    <x-backend.sortable-th column="status" label="Status" :current-sort="$sort" :current-direction="$direction" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>

            @foreach($listings as $item)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-4 py-3.5 text-xs text-gray-500 text-center font-medium">
                        {{ $listings->firstItem() ? $listings->firstItem() + $loop->index : $loop->iteration }}
                    </td>
                    <td class="px-4 py-3.5">
                        <a href="{{ route('supplier.catalog.listings.show', $item) }}" class="font-semibold text-gray-900 hover:text-indigo-600 truncate block max-w-xs transition-colors">
                            {{ $item->name }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="font-mono text-gray-500">{{ $item->listing_number }}</span>
                            @if($item->sku)
                                &middot; SKU: <span class="font-mono text-gray-600">{{ $item->sku }}</span>
                            @endif
                        </p>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $item->isProduct() ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                            {{ $item->listingType?->name ?? ucfirst($item->listing_type ?? 'product') }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        {{ $item->mainCategory?->name ?? 'Uncategorized' }}
                    </td>
                    <td class="px-4 py-3.5 text-xs font-semibold text-gray-900">
                        @if($item->base_price)
                            {{ $item->currency_code }} {{ number_format($item->base_price, 2) }}
                        @else
                            <span class="text-gray-400 uppercase text-[10px] font-normal">{{ $item->pricingType?->name ?? str_replace('_', ' ', $item->pricing_type ?? 'Quote Only') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <x-backend.status-badge :status="$item->approval_status" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('supplier.catalog.listings.show', $item) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition" title="View details">
                                <i class="fa-regular fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('supplier.catalog.listings.edit', $item) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition" title="Edit listing">
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('supplier.catalog.listings.destroy', $item) }}" onsubmit="return confirm('Are you sure you want to delete this listing?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete listing">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$listings" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
