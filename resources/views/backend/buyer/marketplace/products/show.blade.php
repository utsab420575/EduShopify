@extends('backend.layouts.buyer')

@section('title', $listing->name)
@section('breadcrumb', 'Marketplace / Products / ' . $listing->name)

@section('body')

    {{-- Hero-gallery + tabs + sidebar shell — mirrors the supplier's own
         listing detail page (design.md's "Detail Page: Hero Gallery + Tabs +
         Sidebar" pattern), adapted for a buyer viewer: no edit links, no
         approval-status banners, buy-box focused on Request Quotation. --}}
    @php
        $gallery = $listing->getMedia('gallery');
        $primaryId = $listing->primary_image_media_id;
        $heroFirst = $gallery->sortByDesc(fn ($m) => $m->id === $primaryId)->values();
        $firstMedia = $heroFirst->first();
        $hasVariants = $listing->isProduct() && $listing->variants->isNotEmpty();
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6" x-data="{ tab: 'overview', heroUrl: '{{ $firstMedia?->getUrl() }}' }">

        <div class="xl:col-span-8 space-y-5">
            @include('backend.buyer.marketplace.products.partials._hero', ['heroFirst' => $heroFirst, 'firstMedia' => $firstMedia])

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <nav class="flex gap-0 border-b border-gray-100 px-1 overflow-x-auto">
                    <button type="button" @click="tab = 'overview'"
                            class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                            :class="tab === 'overview' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                        <i class="fa-regular fa-file-lines mr-1.5 text-xs"></i>Overview
                    </button>
                    <button type="button" @click="tab = 'specifications'"
                            class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                            :class="tab === 'specifications' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                        <i class="fa-solid fa-sliders mr-1.5 text-xs"></i>Specifications
                        @if($listing->attributeValues->isNotEmpty())
                            <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600">{{ $listing->attributeValues->count() }}</span>
                        @endif
                    </button>
                    @if($hasVariants)
                        <button type="button" @click="tab = 'variants'"
                                class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                                :class="tab === 'variants' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                            <i class="fa-solid fa-layer-group mr-1.5 text-xs"></i>Variants
                            <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600">{{ $listing->variants->count() }}</span>
                        </button>
                    @endif
                </nav>

                <div class="p-5">
                    <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @include('backend.buyer.marketplace.products.partials._overview')
                    </div>

                    <div x-show="tab === 'specifications'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @include('backend.buyer.marketplace.products.partials._specifications')
                    </div>

                    @if($hasVariants)
                        <div x-show="tab === 'variants'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            @include('backend.buyer.marketplace.products.partials._variants')
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="xl:col-span-4 space-y-4">
            @include('backend.buyer.marketplace.products.partials._sidebar')
        </div>

    </div>

@endsection
