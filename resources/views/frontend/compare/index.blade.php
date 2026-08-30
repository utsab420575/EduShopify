@extends('frontend.layouts.master')

@section('title', 'Compare Products — EduShopify')
@section('meta_description', 'Compare marketplace listings side by side — pricing, MOQ, supplier and structured specifications, sourced fresh from EduShopify.')

@push('head')
    {{-- Dynamic, user-selected combinations — never indexed as permanent pages (spec §50). --}}
    <meta name="robots" content="noindex, follow">
@endpush

@push('styles')
<style>
    .comparison-table th.sticky, .comparison-table td.sticky {
        position: sticky;
        left: 0;
    }
    .comparison-row-diff {
        background: var(--fe-warning-soft);
    }
    @media print {
        header, #main-content > .fe-container > nav, .comparison-hide-print,
        .comparison-controls, footer, [aria-label="Site menu"] {
            display: none !important;
        }
        #comparison-print-area {
            border: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="fe-container py-6 sm:py-8" x-data="comparePage()">

    <x-frontend::navigation.breadcrumbs :items="['Compare Products' => null]" />

    <template x-if="loading">
        <div class="py-20 text-center text-sm" style="color:var(--fe-text-muted);">
            <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading comparison…
        </div>
    </template>

    <template x-if="!loading && count === 0">
        <div>
            <x-frontend::common.empty-state
                icon="fa-arrow-right-arrow-left"
                title="No products selected for comparison."
                description="Browse products and click &quot;Add to Compare&quot; to start comparing."
                actionLabel="Browse Products"
                :actionUrl="route('frontend.catalog.index')" />
        </div>
    </template>

    <template x-if="!loading && count > 0">
        <div>
            <div class="flex items-center justify-between flex-wrap gap-3 mb-5 mt-4">
                <h1 class="text-xl font-bold" style="font-family:var(--font-display);color:var(--fe-text);">
                    Compare Products
                    <span class="text-sm font-normal" style="color:var(--fe-text-muted);" x-text="'(' + count + ' of ' + maxItems + ')'"></span>
                </h1>
                <div class="comparison-controls comparison-hide-print flex items-center gap-3 flex-wrap">
                    <label class="inline-flex items-center gap-1.5 text-xs font-medium cursor-pointer" style="color:var(--fe-text);">
                        <input type="checkbox" x-model="highlightDiffs" class="rounded" style="accent-color:var(--fe-primary);">
                        Highlight Differences
                    </label>
                    <label class="inline-flex items-center gap-1.5 text-xs font-medium cursor-pointer" style="color:var(--fe-text);">
                        <input type="checkbox" x-model="showDiffsOnly" class="rounded" style="accent-color:var(--fe-primary);">
                        Show Differences Only
                    </label>
                    <button type="button" @click="window.print()" class="fe-focus-ring text-xs font-semibold px-3 py-1.5 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                        <i class="fa-solid fa-print mr-1"></i> Print
                    </button>
                    <button type="button" @click="if (confirm('Clear all products from comparison?')) clearAll()" class="fe-focus-ring text-xs font-semibold px-3 py-1.5 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-danger);">
                        <i class="fa-solid fa-trash mr-1"></i> Clear All
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border overflow-hidden" style="border-color:var(--fe-border);" id="comparison-print-area">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse comparison-table" style="min-width:720px;">
                        <thead>
                            <tr>
                                <th class="sticky z-20 text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide" style="background:var(--fe-surface-soft);color:var(--fe-text-muted);width:160px;min-width:160px;">
                                    Product
                                </th>
                                <template x-for="(item, idx) in listings" :key="item.listing_id + ':' + (item.variant_id ?? 0)">
                                    @include('frontend.components.marketplace.comparison-header')
                                </template>
                                <th class="comparison-hide-print px-4 py-4 align-top" x-show="canAddMore" x-cloak style="width:180px;min-width:180px;background:var(--fe-surface-soft);border-left:1px solid var(--fe-border);">
                                    <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                                        <button type="button" @click="open = !open" class="fe-focus-ring w-full flex flex-col items-center justify-center gap-1.5 text-xs font-semibold py-6 rounded-xl border-2 border-dashed" style="border-color:var(--fe-border-strong);color:var(--fe-text-muted);">
                                            <i class="fa-solid fa-plus text-base"></i> Add Product
                                        </button>
                                        <div x-show="open" x-cloak class="absolute z-30 mt-2 left-0 w-64 bg-white border rounded-xl shadow-lg p-3" style="border-color:var(--fe-border);">
                                            <input type="text" x-model="addMoreQuery" @input.debounce.300ms="searchAddMore()" placeholder="Search products…" class="fe-focus-ring w-full h-10 px-3 rounded-lg border text-sm mb-2" style="border-color:var(--fe-border);">
                                            <template x-if="addMoreResults.length > 0">
                                                <div class="max-h-56 overflow-y-auto space-y-1">
                                                    <template x-for="r in addMoreResults" :key="r.id">
                                                        <button type="button" @click="addMore(r.id); open = false" class="w-full text-left px-2 py-2 rounded-lg hover:bg-slate-50 text-xs flex items-center gap-2">
                                                            <img :src="r.thumb_url" x-show="r.thumb_url" class="w-8 h-8 rounded object-cover shrink-0" alt="">
                                                            <span class="min-w-0">
                                                                <span class="block font-medium truncate" x-text="r.name" style="color:var(--fe-text);"></span>
                                                                <span class="block text-[10px]" style="color:var(--fe-text-muted);" x-text="r.category"></span>
                                                            </span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                            <p x-show="addMoreQuery.length >= 2 && addMoreResults.length === 0" class="text-xs py-2 text-center" style="color:var(--fe-text-muted);">No matching products.</p>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        {{-- Key / basic specifications --}}
                        <tbody>
                            <template x-for="row in matrix.key_specs" :key="'key-' + row.attribute_id">
                                @include('frontend.components.marketplace.comparison-row')
                            </template>
                        </tbody>

                        {{-- Additional information — grouped, shown only when expanded --}}
                        <template x-if="showAdditional">
                            <template x-for="group in matrix.additional_groups" :key="group.group_name">
                                <tbody>
                                    <tr>
                                        <td class="sticky z-10 px-4 py-2 text-[11px] font-bold uppercase tracking-wide" style="background:var(--fe-surface-soft);color:var(--fe-primary);border-top:1px solid var(--fe-border);" x-text="group.group_name"></td>
                                        <td :colspan="listings.length" class="px-4 py-2 text-[11px] font-bold uppercase tracking-wide" style="background:var(--fe-surface-soft);border-top:1px solid var(--fe-border);border-left:1px solid var(--fe-border);"></td>
                                    </tr>
                                    <template x-for="row in group.rows" :key="group.group_name + '-' + row.attribute_id">
                                        @include('frontend.components.marketplace.comparison-row')
                                    </template>
                                </tbody>
                            </template>
                        </template>
                    </table>
                </div>
            </div>

            <div class="comparison-hide-print text-center mt-5">
                <button type="button" @click="showAdditional = !showAdditional" class="fe-focus-ring text-sm font-semibold px-5 py-2.5 rounded-xl border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                    <span x-show="!showAdditional"><i class="fa-solid fa-chevron-down mr-1.5"></i> Show Additional Information</span>
                    <span x-show="showAdditional" x-cloak><i class="fa-solid fa-chevron-up mr-1.5"></i> Show Basic Information</span>
                </button>
            </div>
        </div>
    </template>

</div>
@endsection
