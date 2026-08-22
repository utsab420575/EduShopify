@extends('backend.layouts.admin')

@section('title', 'Listings')
@section('breadcrumb', 'Catalog & Taxonomy / Listings')

@section('body')

    <x-backend.page-header title="Listings" subtitle="Platform-wide product and service listings." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search listings..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="type" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Types</option>
                    <option value="product" @selected($type === 'product')>Product</option>
                    <option value="service" @selected($type === 'service')>Service</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($listings->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-box" title="No listings found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Listing</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($listings as $listing)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $listing->name }} @if($listing->is_featured)<i class="fa-solid fa-star text-amber-400 text-xs ml-1" title="Featured"></i>@endif</p>
                        <p class="text-xs text-gray-400">{{ ucfirst($listing->listing_type) }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $listing->supplierAccount?->supplierProfile?->display_name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $listing->mainCategory?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $listing->base_price ? number_format($listing->base_price, 2).' '.$listing->currency_code : '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$listing->approval_status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($listing->approval_status === 'pending')
                                <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}">
                                    @csrf
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                                </form>
                                <button type="button" title="Reject" @click="$dispatch('open-modal-reject-listing-{{ $listing->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
                            @endif
                            <a href="{{ route('admin.catalog.listings.show', $listing) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$listings" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($listings as $listing)
        @if($listing->approval_status === 'pending')
            <x-backend.modal :id="'reject-listing-'.$listing->id" title="Reject Listing">
                <form method="POST" action="{{ route('admin.catalog.listings.reject', $listing) }}">
                    @csrf
                    <x-backend.textarea name="reason" label="Rejection reason" required />
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
