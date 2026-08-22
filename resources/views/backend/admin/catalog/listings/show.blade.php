@extends('backend.layouts.admin')

@section('title', $listing->name)
@section('breadcrumb', 'Catalog & Taxonomy / Listings / ' . $listing->name)

@section('body')

    <x-backend.page-header :title="$listing->name" :subtitle="$listing->listing_number">
        <x-slot:actions>
            <x-backend.status-badge :status="$listing->approval_status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if($listing->approval_status === 'pending')
            <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Approve &amp; Publish</button>
            </form>
            <button @click="$dispatch('open-modal-reject')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Reject</button>
        @elseif($listing->is_active)
            <button @click="$dispatch('open-modal-deactivate')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Deactivate</button>
        @else
            <form method="POST" action="{{ route('admin.catalog.listings.reactivate', $listing) }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reactivate</button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.catalog.listings.feature', $listing) }}">
            @csrf
            <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-star mr-1.5 {{ $listing->is_featured ? 'text-amber-400' : '' }}"></i>{{ $listing->is_featured ? 'Unfeature' : 'Feature' }}
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Listing Details">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Type</dt><dd class="font-medium text-gray-900">{{ ucfirst($listing->listing_type) }}</dd></div>
                    <div><dt class="text-gray-500">SKU</dt><dd class="font-medium text-gray-900">{{ $listing->sku ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Category</dt><dd class="font-medium text-gray-900">{{ $listing->mainCategory?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Brand</dt><dd class="font-medium text-gray-900">{{ $listing->brand?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Pricing Type</dt><dd class="font-medium text-gray-900">{{ ucfirst($listing->pricing_type ?? '—') }}</dd></div>
                    <div><dt class="text-gray-500">Base Price</dt><dd class="font-medium text-gray-900">{{ $listing->base_price ? number_format($listing->base_price, 2).' '.$listing->currency_code : '—' }}</dd></div>
                    <div><dt class="text-gray-500">Min Order Qty</dt><dd class="font-medium text-gray-900">{{ $listing->min_order_quantity ?? '—' }} {{ $listing->unit?->symbol }}</dd></div>
                    <div><dt class="text-gray-500">Created</dt><dd class="font-medium text-gray-900">{{ $listing->created_at->format('d M Y') }}</dd></div>
                </dl>
                @if($listing->short_description)
                    <p class="text-sm text-gray-600 mt-4">{{ $listing->short_description }}</p>
                @endif
            </x-backend.form-card>

            @if($listing->categories->isNotEmpty())
                <x-backend.form-card title="Categories">
                    <div class="flex flex-wrap gap-2">
                        @foreach($listing->categories as $category)
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">{{ $category->name }}</span>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif

            @if($listing->attributeValues->isNotEmpty())
                <x-backend.form-card title="Specifications">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        @foreach($listing->attributeValues as $value)
                            <div><dt class="text-gray-500">{{ $value->attribute?->name }}</dt><dd class="font-medium text-gray-900">{{ $value->value }}</dd></div>
                        @endforeach
                    </dl>
                </x-backend.form-card>
            @endif

            @if($listing->rejection_reason)
                <x-backend.form-card title="Rejection Reason"><p class="text-sm text-gray-600">{{ $listing->rejection_reason }}</p></x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Supplier">
                @if($listing->supplierAccount)
                    <p class="text-sm font-medium text-gray-900">{{ $listing->supplierAccount->supplierProfile?->display_name ?? $listing->supplierAccount->display_name }}</p>
                    <a href="{{ route('admin.suppliers.show', $listing->supplierAccount) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Supplier &rarr;</a>
                @endif
            </x-backend.form-card>

            <x-backend.form-card title="Approval">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Approved By</dt><dd class="font-medium text-gray-900">{{ $listing->approvedBy?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Approved At</dt><dd class="font-medium text-gray-900">{{ $listing->approved_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Published At</dt><dd class="font-medium text-gray-900">{{ $listing->published_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
            </x-backend.form-card>
        </div>
    </div>

    @if($listing->approval_status === 'pending')
        <x-backend.modal id="reject" title="Reject Listing">
            <form method="POST" action="{{ route('admin.catalog.listings.reject', $listing) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason for rejection" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                </div>
            </form>
        </x-backend.modal>
    @elseif($listing->is_active)
        <x-backend.modal id="deactivate" title="Deactivate Listing">
            <form method="POST" action="{{ route('admin.catalog.listings.deactivate', $listing) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Deactivate</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
