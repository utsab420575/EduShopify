@extends('backend.layouts.buyer')

@section('title', 'Supplier Directory')
@section('breadcrumb', 'Marketplace / Suppliers')

@section('body')

    <x-backend.page-header title="Supplier Directory" subtitle="Discover verified suppliers on EduShopify." />

    <x-backend.table>
        <x-slot:toolbar>
            <x-backend.table-search
                title="Suppliers" :count="$suppliers->total()"
                search-param="search" page-param="page"
                :current-search="$search" placeholder="Search suppliers...">
                <x-slot:filters>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Supplier Type</label>
                        <select name="type" onchange="this.form.submit()" class="focus-accent w-44 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Types</option>
                            @foreach($supplierTypes as $t)
                                <option value="{{ $t->id }}" @selected($type === $t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Country</label>
                        <select name="country" onchange="this.form.submit()" class="focus-accent w-44 text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                            <option value="">All Countries</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" @selected($country === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($type || $country)
                        <a href="{{ request()->fullUrlWithQuery(['type' => null, 'country' => null, 'page' => null]) }}"
                           class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-2">Clear</a>
                    @endif
                </x-slot:filters>
            </x-backend.table-search>
        </x-slot:toolbar>

        @if($suppliers->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-store" title="No suppliers found" description="Try adjusting your search or filters." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SL</th>
                    <x-backend.sortable-th column="name" label="Supplier" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type(s)</th>
                    <x-backend.sortable-th column="rating" label="Rating" />
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>

            @foreach($suppliers as $supplier)
                @php($profile = $supplier->supplierProfile)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $suppliers->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=eef2ff&color=4f46e5' }}" class="w-9 h-9 rounded-lg object-contain bg-white border border-gray-100" alt="">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $profile?->display_name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $profile?->country?->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex flex-wrap gap-1">
                            @forelse($supplier->supplierTypes as $t)
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $t->name }}</span>
                            @empty
                                <span class="text-gray-400 text-sm">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm">
                        @if($profile?->rating)
                            <span class="text-amber-500"><i class="fa-solid fa-star"></i> {{ number_format($profile->rating, 1) }}</span>
                            <span class="text-gray-400">({{ $profile->reviews_count }})</span>
                        @else
                            <span class="text-gray-400">No reviews</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button" title="Quick View" @click="$dispatch('open-modal-supplier-{{ $supplier->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></button>
                            <a href="{{ route('buyer.suppliers.show', $supplier) }}" title="Go to Profile" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                            <form method="POST" action="{{ route('buyer.suppliers.save', $supplier) }}" class="inline">
                                @csrf
                                <button type="submit" title="Save" class="w-8 h-8 rounded-lg inline-flex items-center justify-center {{ $savedIds->contains($supplier->id) ? 'text-red-500' : 'text-gray-400 hover:bg-gray-100' }}"><i class="fa-solid fa-heart"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$suppliers" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Quick-view modals per design.md §0.3.6 --}}
    @foreach($suppliers as $supplier)
        @php($profile = $supplier->supplierProfile)
        <x-backend.modal :id="'supplier-'.$supplier->id" :title="$profile?->display_name" width="max-w-lg">
            <div class="flex items-center gap-3 mb-4">
                <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=eef2ff&color=4f46e5' }}" class="w-14 h-14 rounded-xl object-contain bg-white border border-gray-100" alt="">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $profile?->display_name }}</p>
                    <p class="text-xs text-gray-400">{{ collect([$profile?->city?->name, $profile?->country?->name])->filter()->implode(', ') }}</p>
                    @if($profile?->rating)
                        <p class="text-xs text-amber-500 mt-0.5"><i class="fa-solid fa-star"></i> {{ number_format($profile->rating, 1) }} <span class="text-gray-400">({{ $profile->reviews_count }} reviews)</span></p>
                    @endif
                </div>
            </div>

            @if($supplier->supplierTypes->isNotEmpty())
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach($supplier->supplierTypes as $t)
                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $t->name }}</span>
                    @endforeach
                </div>
            @endif

            @if($profile?->description)
                <p class="text-sm text-gray-600 mb-4">{{ $profile->description }}</p>
            @endif

            <a href="{{ route('buyer.suppliers.show', $supplier) }}" target="_self" class="block text-center text-sm font-medium py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50" style="color:var(--theme-primary)">
                See Full Profile &rarr;
            </a>
        </x-backend.modal>
    @endforeach

@endsection
