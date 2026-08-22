@extends('backend.layouts.buyer')

@section('title', 'Saved Items')
@section('breadcrumb', 'Saved Items')

@section('body')

    <x-backend.page-header title="Saved Items" subtitle="Suppliers, products, RFQs and quotations you've bookmarked." />

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @foreach(['supplier' => 'Suppliers', 'listing' => 'Products / Listings', 'rfq' => 'RFQs', 'quotation' => 'Quotations'] as $key => $label)
            <a href="{{ route('buyer.saved-items.index', ['type' => $key]) }}"
               class="text-xs font-medium px-3 py-1.5 rounded-full border {{ $type === $key ? 'text-white' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }}"
               @if($type === $key) style="background:var(--theme-primary);border-color:var(--theme-primary)" @endif>
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </div>

    @if($items->isEmpty())
        <x-backend.empty-state icon="fa-bookmark" title="Nothing saved here yet" description="Items you save from the marketplace will appear here." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($items as $item)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    @if($type === 'supplier')
                        <p class="text-sm font-semibold text-gray-900">{{ $item->supplierProfile?->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ $item->supplierProfile?->country?->name }}</p>
                        <a href="{{ route('buyer.suppliers.show', $item) }}" class="block text-sm font-medium mt-3 pt-3 border-t border-gray-100 text-center" style="color:var(--theme-primary)">View Profile</a>
                    @elseif($type === 'listing')
                        <p class="text-sm font-semibold text-gray-900 line-clamp-2">{{ $item->name }}</p>
                        <p class="text-xs text-gray-400">{{ $item->supplierAccount?->supplierProfile?->display_name }}</p>
                        @if($item->base_price)<p class="text-sm font-semibold text-gray-900 mt-1">{{ number_format($item->base_price, 2) }} {{ $item->currency_code }}</p>@endif
                        <a href="{{ $item->isProduct() ? route('buyer.marketplace.products.show', $item) : route('buyer.marketplace.services.show', $item) }}" class="block text-sm font-medium mt-3 pt-3 border-t border-gray-100 text-center" style="color:var(--theme-primary)">View Listing</a>
                    @elseif($type === 'rfq')
                        <p class="text-sm font-semibold text-gray-900">{{ $item->title }}</p>
                        <p class="text-xs text-gray-400">{{ $item->rfq_number }}</p>
                        <x-backend.status-badge :status="$item->status" class="mt-2" />
                        <a href="{{ route('buyer.rfqs.show', $item) }}" class="block text-sm font-medium mt-3 pt-3 border-t border-gray-100 text-center" style="color:var(--theme-primary)">View RFQ</a>
                    @else
                        <p class="text-sm font-semibold text-gray-900">{{ $item->supplierAccount?->supplierProfile?->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ $item->rfq?->title }}</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ number_format($item->grand_total, 2) }} {{ $item->currency_code }}</p>
                        <a href="{{ route('buyer.quotations.show', $item) }}" class="block text-sm font-medium mt-3 pt-3 border-t border-gray-100 text-center" style="color:var(--theme-primary)">View Quotation</a>
                    @endif

                    <form method="POST" action="{{ route('buyer.saved-items.toggle') }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <button type="submit" class="w-full text-xs font-medium text-red-600 hover:text-red-700">Remove from saved</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

@endsection
