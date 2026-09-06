@extends('backend.layouts.buyer')

@section('title', 'Products')
@section('breadcrumb', 'Marketplace / Products')

@section('body')

    <x-backend.page-header title="Products" subtitle="Browse published products from suppliers on EduShopify." />

    <x-backend.table>
        <x-slot:toolbar>
            <x-backend.table-search
                title="Products" :count="$listings->total()"
                search-param="search" page-param="page"
                :current-search="$search" placeholder="Search products...">
                <x-slot:filters>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Category</label>
                        <select name="category" onchange="this.form.submit()" class="focus-accent w-44 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Categories</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected($category === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Brand</label>
                        <select name="brand" onchange="this.form.submit()" class="focus-accent w-44 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Brands</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}" @selected($brand === $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($category || $brand)
                        <a href="{{ request()->fullUrlWithQuery(['category' => null, 'brand' => null, 'page' => null]) }}"
                           class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-2">Clear</a>
                    @endif
                </x-slot:filters>
            </x-backend.table-search>
        </x-slot:toolbar>

        @if($listings->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-boxes-stacked" title="No products found" description="Try adjusting your search or filters." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    <x-backend.sortable-th column="name" label="Product" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Brand</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <x-backend.sortable-th column="base_price" label="Price" align="right" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>

            @foreach($listings as $listing)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $listings->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                                @if($listing->primaryImage)
                                    <img src="{{ $listing->primaryImage->getUrl() }}" class="w-full h-full object-contain" alt="">
                                @else
                                    <i class="fa-solid fa-box text-gray-300"></i>
                                @endif
                            </div>
                            <a href="{{ route('buyer.marketplace.products.show', $listing) }}" class="text-sm font-medium text-gray-900 hover:underline line-clamp-2">{{ $listing->name }}</a>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $listing->mainCategory?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $listing->brand?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $listing->supplierAccount?->supplierProfile?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-900 text-right font-medium">
                        @if($listing->base_price)
                            {{ number_format($listing->base_price, 2) }} {{ $listing->currency_code }}
                        @else
                            <span class="text-gray-400 font-normal">Quote only</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('buyer.marketplace.products.show', $listing) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                            <a href="{{ route('buyer.rfqs.create', ['listing' => $listing->id]) }}" title="Request Quote" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-file-lines"></i></a>
                            <form method="POST" action="{{ route('buyer.saved-items.toggle') }}" class="inline">
                                @csrf
                                <input type="hidden" name="type" value="listing">
                                <input type="hidden" name="id" value="{{ $listing->id }}">
                                <button type="submit" title="Save" class="w-8 h-8 rounded-lg inline-flex items-center justify-center {{ $savedIds->contains($listing->id) ? 'text-red-500' : 'text-gray-400 hover:bg-gray-100' }}"><i class="fa-solid fa-bookmark"></i></button>
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
