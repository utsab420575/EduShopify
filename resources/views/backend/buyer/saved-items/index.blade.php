@extends('backend.layouts.buyer')

@section('title', 'Saved Items')
@section('breadcrumb', 'Saved Items')

@section('body')

    <x-backend.page-header title="Saved Items" subtitle="Suppliers, products, RFQs and quotations you've bookmarked." />

    <x-backend.tabs>
        @foreach(['supplier' => 'Suppliers', 'listing' => 'Products / Listings', 'rfq' => 'RFQs', 'quotation' => 'Quotations'] as $key => $label)
            <x-backend.tab :href="route('buyer.saved-items.index', ['type' => $key])" :active="$type === $key">
                {{ $label }} ({{ $counts[$key] }})
            </x-backend.tab>
        @endforeach
    </x-backend.tabs>

    <x-backend.table>
        @if($items->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-bookmark" title="Nothing saved here yet" description="Items you save from the marketplace will appear here." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    @if($type === 'supplier')
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    @elseif($type === 'listing')
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Price</th>
                    @elseif($type === 'rfq')
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    @else
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">RFQ</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Total</th>
                    @endif
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>

            @foreach($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $paginator->firstItem() + $loop->index }}</td>

                    @if($type === 'supplier')
                        <td class="px-5 py-3.5">
                            <p class="text-sm font-medium text-gray-900">{{ $item->supplierProfile?->display_name }}</p>
                            <p class="text-xs text-gray-400">{{ $item->supplierProfile?->country?->name }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('buyer.suppliers.show', $item) }}" title="View Profile" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                                @include('backend.buyer.saved-items.partials._remove-button', ['type' => $type, 'item' => $item])
                            </div>
                        </td>
                    @elseif($type === 'listing')
                        <td class="px-5 py-3.5">
                            <a href="{{ $item->isProduct() ? route('buyer.marketplace.products.show', $item) : route('buyer.marketplace.services.show', $item) }}" class="text-sm font-medium text-gray-900 hover:underline line-clamp-2">{{ $item->name }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->supplierAccount?->supplierProfile?->display_name }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">
                            @if($item->base_price)
                                {{ number_format($item->base_price, 2) }} {{ $item->currency_code }}
                            @else
                                <span class="text-gray-400 font-normal">Quote only</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ $item->isProduct() ? route('buyer.marketplace.products.show', $item) : route('buyer.marketplace.services.show', $item) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                                @include('backend.buyer.saved-items.partials._remove-button', ['type' => $type, 'item' => $item])
                            </div>
                        </td>
                    @elseif($type === 'rfq')
                        <td class="px-5 py-3.5">
                            <p class="text-sm font-medium text-gray-900">{{ $item->title }}</p>
                            <p class="text-xs text-gray-400">{{ $item->rfq_number }}</p>
                        </td>
                        <td class="px-5 py-3.5"><x-backend.status-badge :status="$item->status" /></td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('buyer.rfqs.show', $item) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                                @include('backend.buyer.saved-items.partials._remove-button', ['type' => $type, 'item' => $item])
                            </div>
                        </td>
                    @else
                        <td class="px-5 py-3.5 text-sm text-gray-900 font-medium">{{ $item->supplierAccount?->supplierProfile?->display_name }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-600">{{ $item->rfq?->title }}</td>
                        <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">{{ number_format($item->grand_total, 2) }} {{ $item->currency_code }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('buyer.quotations.show', $item) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                                @include('backend.buyer.saved-items.partials._remove-button', ['type' => $type, 'item' => $item])
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$paginator" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
