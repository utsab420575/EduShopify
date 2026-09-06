{{--
    Read-only listing detail content, tabbed — used only by show.blade.php.
    Deliberately kept separate from listing-preview.blade.php (the flat
    version the Step 4 wizard's preview modal injects via x-html — see that
    file for why it can't gain this tab shell). Expects the same $listing /
    $groupedSpecifications as listing-preview.blade.php.

    Redesigned layout: left column = hero image + tabs, right = buy-box sidebar.
    Tab pattern matches resources/views/backend/buyer/procurement/rfqs/show.blade.php.
--}}
@php
    $gallery     = $listing->getMedia('gallery');
    $primaryId   = $listing->primary_image_media_id;
    // Sort: primary first, then rest
    $heroFirst   = $gallery->sortByDesc(fn($m) => $m->id === $primaryId)->values();
    $firstMedia  = $heroFirst->first();
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-6"
     x-data="{
         tab:     '{{ request('_tab', 'overview') }}',
         heroUrl: '{{ $firstMedia?->getUrl() }}',
     }">

    {{-- ═══════════════════════════════════════════════════════
         LEFT COLUMN — Hero Image + Tabs
    ═══════════════════════════════════════════════════════ --}}
    <div class="xl:col-span-8 space-y-5">

        {{-- ── Hero Media Panel ─────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {{-- Hero image area --}}
            <div class="relative bg-gray-50 flex items-center justify-center" style="min-height: 340px; max-height: 440px;">
                @if($firstMedia)
                    <img :src="heroUrl" alt="{{ $listing->name }}"
                         class="w-full object-contain transition-all duration-300"
                         style="max-height: 440px;">
                    {{-- Primary badge --}}
                    <span class="absolute top-3 left-3 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-full bg-amber-400/90 text-white shadow backdrop-blur-sm">
                        <i class="fa-solid fa-star text-[9px]"></i> Cover Photo
                    </span>
                @else
                    <div class="flex flex-col items-center justify-center py-20 text-gray-300">
                        <i class="fa-regular fa-image text-5xl mb-3"></i>
                        <p class="text-sm font-medium">No photos uploaded yet</p>
                        <a href="{{ route('supplier.catalog.listings.edit', $listing) }}"
                           class="mt-3 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Add photos in Edit Listing →
                        </a>
                    </div>
                @endif
            </div>

            {{-- Thumbnail strip --}}
            @if($gallery->count() > 1)
                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-100 overflow-x-auto">
                    @foreach($heroFirst as $media)
                        <button type="button"
                                @click="heroUrl = '{{ $media->getUrl() }}'"
                                class="flex-shrink-0 relative w-16 h-16 rounded-lg overflow-hidden border-2 transition-all duration-150"
                                :class="heroUrl === '{{ $media->getUrl() }}' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-400'">
                            <img src="{{ $media->getUrl() }}" alt="" class="w-full h-full object-cover">
                            @if($media->id === $primaryId)
                                <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-amber-400 flex items-center justify-center shadow">
                                    <i class="fa-solid fa-star text-white text-[8px]"></i>
                                </span>
                            @endif
                        </button>
                    @endforeach
                    <a href="{{ route('supplier.catalog.listings.edit', $listing) }}"
                       class="flex-shrink-0 w-16 h-16 rounded-lg border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 hover:border-indigo-400 hover:text-indigo-500 transition text-center">
                        <i class="fa-solid fa-plus text-sm"></i>
                        <span class="text-[10px] font-medium mt-0.5">Add</span>
                    </a>
                </div>
            @elseif($gallery->isEmpty())
                {{-- empty: no strip needed --}}
            @else
                {{-- Single image: show add-more prompt --}}
                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-100">
                    <div class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
                        <img src="{{ $firstMedia->getUrl() }}" alt="" class="w-full h-full object-cover">
                    </div>
                    <a href="{{ route('supplier.catalog.listings.edit', $listing) }}"
                       class="flex-shrink-0 w-16 h-16 rounded-lg border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 hover:border-indigo-400 hover:text-indigo-500 transition">
                        <i class="fa-solid fa-plus text-sm"></i>
                        <span class="text-[10px] font-medium mt-0.5">Add</span>
                    </a>
                    <p class="text-xs text-gray-400 ml-1">Add more product photos for better visibility.</p>
                </div>
            @endif
        </div>

        {{-- ── Detail Tabs ─────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {{-- Tab navigation --}}
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
                    @if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty())
                        <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600">{{ $groupedSpecifications->sum(fn($g) => count($g['items'])) }}</span>
                    @endif
                </button>
                @if($listing->isProduct())
                    <button type="button" @click="tab = 'variants'"
                            class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors"
                            :class="tab === 'variants' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                        <i class="fa-solid fa-layer-group mr-1.5 text-xs"></i>Variants
                        @if($listing->variants->isNotEmpty())
                            <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-600">{{ $listing->variants->count() }}</span>
                        @endif
                    </button>
                @endif
            </nav>

            {{-- Tab content --}}
            <div class="p-5">
                <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @include('backend.supplier.catalog.listings.partials.preview-overview')
                </div>

                <div x-show="tab === 'specifications'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @include('backend.supplier.catalog.listings.partials.preview-specifications')
                </div>

                @if($listing->isProduct())
                    <div x-show="tab === 'variants'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @include('backend.supplier.catalog.listings.partials.preview-variants')
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         RIGHT SIDEBAR
    ═══════════════════════════════════════════════════════ --}}
    <div class="xl:col-span-4 space-y-4">
        @include('backend.supplier.catalog.listings.partials.preview-sidebar')
    </div>

</div>
