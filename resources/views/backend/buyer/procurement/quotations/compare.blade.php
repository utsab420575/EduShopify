@extends('backend.layouts.buyer')

@section('title', 'Compare Quotations')
@section('breadcrumb', 'Procurement / RFQs / Compare Quotations')

@php
    $buyerBudgetLabel = '—';
    if ($rfq->budget_min || $rfq->budget_max) {
        $buyerBudgetLabel = trim(
            ($rfq->budget_min ? number_format($rfq->budget_min, 2) : '')
            .($rfq->budget_min && $rfq->budget_max ? ' – ' : '')
            .($rfq->budget_max ? number_format($rfq->budget_max, 2) : '')
        ).' '.$rfq->currency_code;
    }
@endphp

@section('body')

    <div x-data="quotationComparePage({{ $rfq->id }}, {{ $maxItems }}, '{{ route('buyer.quotations.compare.data', $rfq) }}', '{{ url('/buyer/quotations') }}', {{ json_encode($defaultQuotationIds ?? []) }})" x-cloak class="space-y-6">

        {{-- Page Header --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                            RFQ #{{ $rfq->rfq_number }}
                        </span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full border {{ $rfq->status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                            {{ ucfirst(str_replace('_', ' ', $rfq->status)) }}
                        </span>
                        @if($rfq->allow_alternative_products)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200" title="Suppliers can propose alternative products and offers">
                                <i class="fa-solid fa-code-branch text-[10px] mr-1"></i> Alternatives Allowed
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $rfq->title }}</h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Compare received supplier quotations, evaluate alternative offers, and award the winning proposal.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <a href="{{ route('buyer.rfqs.show', $rfq) }}" class="inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-2 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i class="fa-solid fa-arrow-left text-[11px]"></i> Back to RFQ
                    </a>
                    <a href="{{ route('buyer.quotations.index', ['rfq' => $rfq->id]) }}" class="inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-2 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i class="fa-solid fa-list-check text-[11px]"></i> All RFQ Quotations
                    </a>
                </div>
            </div>

            {{-- Quick RFQ Meta Bar --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px]">Target Budget</span>
                    <span class="font-semibold text-gray-800">{{ $buyerBudgetLabel }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Total Items</span>
                    <span class="font-semibold text-gray-800">{{ $rfq->items_count ?? $rfq->items()->count() }} Required Items</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Quotation Deadline</span>
                    <span class="font-semibold text-gray-800">{{ $rfq->quotation_deadline ? \Carbon\Carbon::parse($rfq->quotation_deadline)->format('d M Y, h:i A') : 'No deadline' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Comparison Mode</span>
                    <span class="font-semibold text-indigo-700">Multi-Offer Direct Evaluation</span>
                </div>
            </div>
        </div>

        {{-- Loading State --}}
        <div x-show="loading" class="py-20 text-center bg-white rounded-2xl border border-gray-200 shadow-xs">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 mb-3">
                <i class="fa-solid fa-circle-notch fa-spin text-xl"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">Loading Quotations Comparison…</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Retrieving latest offers, specification values, and commercial summaries from the database.</p>
        </div>

        {{-- Empty / Too Few States --}}
        <div x-show="!loading && count === 0">
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">No Quotations Selected for Comparison</h3>
                <p class="text-xs text-gray-500 mt-1.5 max-w-md mx-auto">
                    Please visit the RFQ Responses page and check "Add to Compare" on at least two quotations you'd like to evaluate side-by-side.
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ route('buyer.quotations.index', ['rfq' => $rfq->id]) }}" class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm">
                        <i class="fa-solid fa-list-check"></i> View RFQ Responses
                    </a>
                </div>
            </div>
        </div>

        <div x-show="!loading && count === 1">
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-scale-unbalanced"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">Select At Least Two Quotations to Compare</h3>
                <p class="text-xs text-gray-500 mt-1.5 max-w-md mx-auto">
                    You currently have 1 quotation selected. Add at least one more quotation from the RFQ responses to perform a side-by-side comparison.
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ route('buyer.quotations.index', ['rfq' => $rfq->id]) }}" class="inline-flex items-center gap-2 text-xs font-bold px-4 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm">
                        <i class="fa-solid fa-plus"></i> Select Another Quotation
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Comparison View --}}
        <template x-if="!loading && count >= 2 && data">
            <div class="space-y-6">

                {{-- Interactive Controls Toolbar --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-xs flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-700">
                            <i class="fa-solid fa-scale-balanced text-indigo-600"></i>
                            <span>Comparing <span class="text-indigo-600" x-text="count"></span> Quotations</span>
                        </div>

                        <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-xl border border-gray-200 transition">
                            <input type="checkbox" x-model="highlightDiffs" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" style="accent-color:var(--theme-primary)">
                            <span>Highlight Differences</span>
                        </label>

                        <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-xl border border-gray-200 transition">
                            <input type="checkbox" x-model="showDiffsOnly" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" style="accent-color:var(--theme-primary)">
                            <span>Show Differences Only</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="if (confirm('Clear the entire quotation comparison?')) clearAll()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-xl border border-red-200 transition">
                            <i class="fa-regular fa-trash-can text-[11px]"></i> Clear All
                        </button>
                    </div>
                </div>

                {{-- ══ 1. TOP STICKY SUPPLIER COMMERCIAL CARDS ══ --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <template x-for="q in data.summary" :key="q.quotation_id">
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs hover:shadow-md transition-shadow flex flex-col overflow-hidden relative"
                             :class="data.commercial.badges.lowest_grand_total_id === q.quotation_id ? 'ring-2 ring-emerald-500/30' : ''">

                            {{-- Highlight Banner for Winner / Lowest Price --}}
                            <div x-show="data.commercial.badges.lowest_grand_total_id === q.quotation_id" class="bg-emerald-600 text-white text-[11px] font-bold px-3 py-1 text-center flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-trophy text-[10px]"></i> Lowest Effective Total Price
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                {{-- Supplier Header --}}
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <template x-if="q.supplier_logo">
                                            <img :src="q.supplier_logo" :alt="q.supplier_name" class="w-10 h-10 rounded-xl object-cover border border-gray-200 shrink-0">
                                        </template>
                                        <template x-if="!q.supplier_logo">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0 uppercase" x-text="q.supplier_name.charAt(0)"></div>
                                        </template>
                                        <div class="min-w-0">
                                            <a :href="'/buyer/quotations/' + q.quotation_id" class="text-sm font-bold text-gray-900 hover:text-indigo-600 transition truncate block" x-text="q.supplier_name"></a>
                                            <span class="text-[11px] text-gray-500 font-mono block" x-text="q.quotation_number"></span>
                                        </div>
                                    </div>

                                    <button type="button" title="Remove from comparison" @click="remove(q.quotation_id)"
                                            class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition shrink-0">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>

                                {{-- Badges & Status Row --}}
                                <div class="flex flex-wrap items-center gap-1.5 mb-3.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border"
                                          :class="{
                                              'bg-blue-50 text-blue-700 border-blue-200': q.status === 'shortlisted',
                                              'bg-emerald-50 text-emerald-700 border-emerald-200': q.status === 'awarded',
                                              'bg-amber-50 text-amber-800 border-amber-200': ['submitted','under_review','revised'].includes(q.status),
                                              'bg-gray-50 text-gray-700 border-gray-200': !['shortlisted','awarded','submitted','under_review','revised'].includes(q.status),
                                          }" x-text="q.status.replace('_',' ')"></span>

                                    <span x-show="q.is_shortlisted" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-star text-[9px]"></i> Shortlisted
                                    </span>

                                    <span x-show="q.rfq_version_stale" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1" :title="'Based on RFQ v' + q.rfq_version_no + ', current is v' + {{ $rfq->current_version_no }}">
                                        <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Older RFQ Version
                                    </span>

                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-md border"
                                          :class="partialFor(q.quotation_id).is_full ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                          x-text="partialFor(q.quotation_id).is_full ? 'Full Coverage' : 'Partial (' + partialFor(q.quotation_id).quoted_count + '/' + partialFor(q.quotation_id).total_count + ')'"></span>
                                </div>

                                {{-- Supplier Rating --}}
                                <div class="flex items-center gap-1 text-xs text-amber-500 mb-3">
                                    <template x-for="n in 5" :key="n">
                                        <i class="fa-solid fa-star text-[11px]" :class="q.supplier_rating && n <= Math.round(q.supplier_rating) ? 'text-amber-400' : 'text-gray-200'"></i>
                                    </template>
                                    <span class="text-gray-600 font-semibold ml-1" x-text="q.supplier_rating ? Number(q.supplier_rating).toFixed(1) : 'New'"></span>
                                    <span class="text-gray-400 text-[11px]" x-show="q.supplier_reviews_count" x-text="'(' + q.supplier_reviews_count + ' reviews)'"></span>
                                </div>

                                {{-- Commercial Price Box --}}
                                <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100 mb-4 space-y-2">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <span class="text-xs font-medium text-gray-500">Effective Total:</span>
                                        <span class="text-lg font-black"
                                              :class="data.commercial.badges.lowest_grand_total_id === q.quotation_id ? 'text-emerald-600' : 'text-gray-900'"
                                              x-text="money(commercialFor(q.quotation_id).effective_grand_total || commercialFor(q.quotation_id).grand_total, commercialFor(q.quotation_id).currency_code)"></span>
                                    </div>

                                    <template x-if="commercialFor(q.quotation_id).has_alternative_selected">
                                        <div class="flex items-center justify-between text-[11px] text-indigo-700 bg-indigo-50/70 px-2 py-1 rounded-md">
                                            <span>Active Offer Selection</span>
                                            <span class="text-gray-400 line-through" x-text="'Original: ' + money(commercialFor(q.quotation_id).grand_total, commercialFor(q.quotation_id).currency_code)"></span>
                                        </div>
                                    </template>

                                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200/60 text-[11px]">
                                        <div>
                                            <span class="text-gray-400 block">Lead Time:</span>
                                            <span class="font-bold text-gray-800 flex items-center gap-1">
                                                <i class="fa-solid fa-truck-fast text-[10px] text-gray-400"></i>
                                                <span x-text="commercialFor(q.quotation_id).lead_time_days ? commercialFor(q.quotation_id).lead_time_days + ' days' : '—'"></span>
                                                <span x-show="data.commercial.badges.shortest_lead_time_id === q.quotation_id" class="text-[9px] px-1 rounded bg-emerald-100 text-emerald-800 font-bold">Fastest</span>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block">Validity:</span>
                                            <span class="font-bold text-gray-800 flex items-center gap-1">
                                                <i class="fa-regular fa-calendar text-[10px] text-gray-400"></i>
                                                <span x-text="commercialFor(q.quotation_id).valid_until || '—'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="mt-auto pt-3 border-t border-gray-100 flex items-center gap-2">
                                    <template x-if="q.can_award">
                                        <button type="button" @click="openAwardModal(q)"
                                                class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm">
                                            <i class="fa-solid fa-award"></i> Award Quotation
                                        </button>
                                    </template>

                                    <form method="POST" action="{{ route('buyer.messages.start') }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="recipient_account_id" :value="q.supplier_account_id">
                                        <input type="hidden" name="context_type" value="quotation">
                                        <input type="hidden" name="context_id" :value="q.quotation_id">
                                        <button type="submit" title="Message Supplier" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                                            <i class="fa-regular fa-comment-dots text-sm"></i>
                                        </button>
                                    </form>

                                    <a :href="'/buyer/quotations/' + q.quotation_id" title="View Full Quotation" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- ══ 2. RFQ ITEM BY ITEM COMPARISON MATRIX ══ --}}
                <div class="space-y-6">
                    <template x-for="item in data.items" :key="item.rfq_item_id">
                        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs">

                            {{-- Item Header Bar --}}
                            <div class="p-4 sm:p-5 bg-gradient-to-r from-gray-50 to-indigo-50/20 border-b border-gray-200">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 class="text-base font-bold text-gray-900" x-text="item.item_name"></h3>
                                            <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border shadow-2xs inline-flex items-center gap-1"
                                                  :class="{
                                                      'bg-emerald-50 text-emerald-800 border-emerald-200': item.item_type === 'marketplace_product',
                                                      'bg-purple-50 text-purple-800 border-purple-200': item.item_type === 'custom_product',
                                                      'bg-amber-50 text-amber-800 border-amber-200': item.item_type === 'quotation_only',
                                                  }">
                                                <i class="fa-solid" :class="{
                                                    'fa-store': item.item_type === 'marketplace_product',
                                                    'fa-sliders': item.item_type === 'custom_product',
                                                    'fa-file-lines': item.item_type === 'quotation_only'
                                                }"></i>
                                                <span x-text="itemTypeLabel(item.item_type)"></span>
                                            </span>

                                            <template x-if="item.category_name">
                                                <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md" x-text="item.category_name"></span>
                                            </template>
                                        </div>

                                        <p class="text-xs text-gray-500" x-show="item.description" x-text="item.description"></p>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        <div class="bg-white border border-gray-200 px-3 py-1.5 rounded-xl text-xs font-medium text-gray-700 shadow-2xs">
                                            <span class="text-gray-400">Required Quantity:</span>
                                            <span class="font-bold text-gray-900 ml-1" x-text="item.quantity + ' ' + (item.unit || 'Units')"></span>
                                        </div>

                                        <template x-if="item.estimated_unit_price">
                                            <div class="bg-white border border-gray-200 px-3 py-1.5 rounded-xl text-xs font-medium text-gray-700 shadow-2xs">
                                                <span class="text-gray-400">Target:</span>
                                                <span class="font-bold text-gray-900 ml-1" x-text="money(item.estimated_unit_price, '{{ $rfq->currency_code }}')"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Buyer Attachments --}}
                                <div x-show="(item.buyer_attachments || []).length > 0" x-cloak class="flex items-center gap-2 flex-wrap mt-3 pt-3 border-t border-gray-200/50">
                                    <span class="text-[11px] font-medium text-gray-500">Buyer Attached Specs:</span>
                                    <template x-for="doc in (item.buyer_attachments || [])" :key="doc.id">
                                        <a :href="doc.url" target="_blank" class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-indigo-700 hover:border-indigo-300 hover:bg-indigo-50/50 transition shadow-2xs">
                                            <i class="fa-solid fa-paperclip text-[10px]"></i>
                                            <span x-text="doc.name"></span>
                                            <span class="text-[10px] text-gray-400" x-text="'(' + doc.size + ')'"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>

                            {{-- Side-by-Side Supplier Columns --}}
                            <div class="overflow-x-auto">
                                <div class="flex divide-x divide-gray-200 min-w-[950px]">
                                    <template x-for="q in data.summary" :key="q.quotation_id">
                                        <div class="flex-1 min-w-[310px] p-5 flex flex-col space-y-4">

                                            {{-- Supplier Title in Column --}}
                                            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                                                <span class="text-xs font-bold text-gray-900" x-text="q.supplier_name"></span>
                                                <span class="text-[11px] text-gray-400 font-mono" x-text="q.quotation_number"></span>
                                            </div>

                                            {{-- Unquoted Placeholder --}}
                                            <template x-if="offersFor(item, q.quotation_id).length === 0">
                                                <div class="py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 my-auto">
                                                    <i class="fa-solid fa-ban text-gray-300 text-lg mb-1"></i>
                                                    <p class="text-xs font-medium text-gray-400">Not quoted for this item</p>
                                                </div>
                                            </template>

                                            {{-- Product Response(s) Under This Supplier --}}
                                            <template x-for="productResponse in offersFor(item, q.quotation_id)" :key="productResponse.quotation_item_id">
                                                <div class="space-y-3 flex-1 flex flex-col">

                                                    {{-- If supplier submitted multiple product responses for this same RFQ item --}}
                                                    <div x-show="offersFor(item, q.quotation_id).length > 1" class="bg-indigo-50/60 border border-indigo-100 px-3 py-1.5 rounded-lg flex items-center justify-between">
                                                        <span class="text-xs font-bold text-indigo-900" x-text="productResponse.item_name"></span>
                                                        <span class="text-[10px] font-semibold text-indigo-600 bg-white px-2 py-0.5 rounded shadow-2xs">Alternative Proposal</span>
                                                    </div>

                                                    {{-- Offer Selector Pills (when multiple offers exist) --}}
                                                    <template x-if="productResponse.offers.length > 1">
                                                        <div>
                                                            <div class="flex items-center justify-between mb-1.5">
                                                                <span class="text-[11px] font-semibold text-gray-500">
                                                                    Available Offers (<span x-text="productResponse.offers.length"></span>):
                                                                </span>
                                                                <button type="button" @click="openAllOffersModal(productResponse, q.supplier_name)"
                                                                        class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                                                                    <i class="fa-solid fa-table-columns text-[10px]"></i> Compare All
                                                                </button>
                                                            </div>

                                                            <div class="flex flex-wrap gap-1.5">
                                                                <template x-for="offerOption in productResponse.offers" :key="offerOption.id">
                                                                    <button type="button" @click="setViewedOffer(productResponse.quotation_item_id, offerOption.id)"
                                                                            class="text-xs font-semibold px-2.5 py-1.5 rounded-lg border transition flex items-center gap-1.5"
                                                                            :class="getViewedOffer(productResponse)?.id === offerOption.id
                                                                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs'
                                                                                : (offerOption.is_selected ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100')">
                                                                        <i class="fa-solid fa-check text-[10px]" x-show="offerOption.is_selected"></i>
                                                                        <i class="fa-solid fa-star text-[10px]" x-show="offerOption.is_primary && !offerOption.is_selected"></i>
                                                                        <span x-text="offerOption.product_name"></span>
                                                                        <span class="text-[10px] opacity-80" x-text="'(' + money(offerOption.total_price, commercialFor(q.quotation_id).currency_code) + ')'"></span>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    {{-- The Active / Viewed Offer Card --}}
                                                    <template x-if="getViewedOffer(productResponse)">
                                                        <div class="rounded-xl border p-4 transition-all flex flex-col space-y-3"
                                                             :class="getViewedOffer(productResponse).is_selected
                                                                ? 'border-emerald-300 bg-emerald-50/30 ring-2 ring-emerald-500/20'
                                                                : 'border-gray-200 bg-white hover:border-indigo-200'">

                                                            {{-- Offer Title & Badges --}}
                                                            <div>
                                                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                                                    <h4 class="text-sm font-bold text-gray-900 leading-snug" x-text="getViewedOffer(productResponse).product_name"></h4>
                                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border shrink-0"
                                                                          :class="getViewedOffer(productResponse).is_selected ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-gray-100 text-gray-700 border-gray-200'"
                                                                          x-text="getViewedOffer(productResponse).is_selected ? 'Selected Offer' : (getViewedOffer(productResponse).is_primary ? 'Primary Offer' : 'Alternative')"></span>
                                                                </div>

                                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-gray-100 text-gray-600" x-text="methodLabel(getViewedOffer(productResponse).offer_method)"></span>
                                                                    <span class="text-[10px] text-gray-500 font-medium" x-text="'Qty: ' + getViewedOffer(productResponse).quantity + ' ' + (getViewedOffer(productResponse).unit || '')"></span>
                                                                </div>

                                                                <p class="text-xs text-gray-500 mt-1" x-show="getViewedOffer(productResponse).description" x-text="getViewedOffer(productResponse).description"></p>
                                                            </div>

                                                            {{-- Marketplace Product Card with Thumbnail (if marketplace method) --}}
                                                            <template x-if="getViewedOffer(productResponse).offered_listing">
                                                                <div class="flex items-center gap-2.5 p-2 rounded-lg bg-gray-50 border border-gray-100">
                                                                    <template x-if="getViewedOffer(productResponse).offered_listing.thumb_url">
                                                                        <img :src="getViewedOffer(productResponse).offered_listing.thumb_url" :alt="getViewedOffer(productResponse).offered_listing.name" class="w-9 h-9 rounded-md object-cover border border-gray-200 shrink-0">
                                                                    </template>
                                                                    <template x-if="!getViewedOffer(productResponse).offered_listing.thumb_url">
                                                                        <div class="w-9 h-9 rounded-md bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                                                            <i class="fa-solid fa-box text-xs"></i>
                                                                        </div>
                                                                    </template>
                                                                    <div class="min-w-0 flex-1">
                                                                        <a :href="'/listing/' + getViewedOffer(productResponse).offered_listing.slug" target="_blank" class="text-xs font-semibold text-indigo-600 hover:underline truncate block" x-text="getViewedOffer(productResponse).offered_listing.name"></a>
                                                                        <span class="text-[10px] text-gray-400">Marketplace Catalog Product</span>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            {{-- Pricing & Delivery Grid --}}
                                                            <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 space-y-1.5">
                                                                <div class="flex items-baseline justify-between">
                                                                    <span class="text-xs font-medium text-gray-500">Unit Price:</span>
                                                                    <span class="text-xs font-bold text-gray-800" x-text="money(getViewedOffer(productResponse).unit_price, commercialFor(q.quotation_id).currency_code)"></span>
                                                                </div>
                                                                <div class="flex items-baseline justify-between pt-1 border-t border-gray-200/60">
                                                                    <span class="text-xs font-bold text-gray-700">Line Total:</span>
                                                                    <span class="text-sm font-black text-gray-900" x-text="money(getViewedOffer(productResponse).total_price, commercialFor(q.quotation_id).currency_code)"></span>
                                                                </div>
                                                                <div class="flex items-center justify-between pt-1 text-[11px] text-gray-500">
                                                                    <span>Delivery Time:</span>
                                                                    <span class="font-semibold text-gray-700" x-text="getViewedOffer(productResponse).delivery_time ? getViewedOffer(productResponse).delivery_time + ' days' : '—'"></span>
                                                                </div>
                                                            </div>

                                                            {{-- Documents Attached to this Offer --}}
                                                            <template x-if="(getViewedOffer(productResponse).documents || []).length > 0">
                                                                <div class="space-y-1">
                                                                    <span class="text-[11px] font-semibold text-gray-400 block">Offer Documents:</span>
                                                                    <div class="flex flex-wrap gap-1.5">
                                                                        <template x-for="doc in (getViewedOffer(productResponse).documents || [])" :key="doc.id">
                                                                            <a :href="doc.url" target="_blank" class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-md bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition">
                                                                                <i class="fa-solid fa-file-pdf text-[10px]"></i>
                                                                                <span x-text="doc.name"></span>
                                                                            </a>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            {{-- Free-text Specs --}}
                                                            <template x-if="(getViewedOffer(productResponse).specifications || []).length > 0">
                                                                <div class="space-y-1">
                                                                    <span class="text-[11px] font-semibold text-gray-400 block">Specifications:</span>
                                                                    <div class="space-y-0.5">
                                                                        <template x-for="spec in (getViewedOffer(productResponse).specifications || [])" :key="spec.name">
                                                                            <div class="text-[11px] flex justify-between text-gray-600">
                                                                                <span class="text-gray-400" x-text="spec.name"></span>
                                                                                <span class="font-medium" x-text="spec.value"></span>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </template>

                                                            {{-- Selection Action / Winning State --}}
                                                            <div class="pt-2 border-t border-gray-100">
                                                                <template x-if="getViewedOffer(productResponse).is_selected">
                                                                    <div class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-300">
                                                                        <i class="fa-solid fa-circle-check text-emerald-600"></i> Selected Winning Offer
                                                                    </div>
                                                                </template>

                                                                <template x-if="!getViewedOffer(productResponse).is_selected && q.can_select_offer">
                                                                    <button type="button" @click="selectOfferAjax(q.quotation_id, productResponse.quotation_item_id, getViewedOffer(productResponse).id)"
                                                                            :disabled="selectingOfferId === getViewedOffer(productResponse).id"
                                                                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-200 text-indigo-700 bg-white hover:bg-indigo-50 text-xs font-semibold transition shadow-2xs">
                                                                        <i class="fa-solid fa-check" x-show="selectingOfferId !== getViewedOffer(productResponse).id"></i>
                                                                        <i class="fa-solid fa-circle-notch fa-spin text-xs" x-show="selectingOfferId === getViewedOffer(productResponse).id"></i>
                                                                        <span x-text="selectingOfferId === getViewedOffer(productResponse).id ? 'Selecting…' : 'Select This Offer for Award'"></span>
                                                                    </button>
                                                                </template>
                                                            </div>

                                                        </div>
                                                    </template>

                                                    {{-- Offer-Level Attributes Match breakdown --}}
                                                    <template x-if="getViewedOffer(productResponse) && (getViewedOffer(productResponse).matched_attributes || []).length > 0">
                                                        <div class="bg-gray-50/70 rounded-xl p-3 border border-gray-100 space-y-1.5">
                                                            <span class="text-[11px] font-bold text-gray-500 block mb-1">Specification Match Status:</span>
                                                            <template x-for="attr in getViewedOffer(productResponse).matched_attributes" :key="attr.attribute_id">
                                                                <div class="text-[11px] flex items-center justify-between gap-1.5 py-0.5">
                                                                    <span class="text-gray-500 truncate" x-text="attr.name"></span>
                                                                    <div class="flex items-center gap-1.5 shrink-0">
                                                                        <span class="font-medium text-gray-800" x-text="attr.supplier_value || 'Missing'"></span>
                                                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                                                                              :class="attrStatusClass(attr.status)"
                                                                              x-text="attr.status"></span>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>

                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Detailed Attribute Comparison Matrix Accordion (Expandable for Deep Inspection) --}}
                            <div x-data="{ specOpen: false }" class="border-t border-gray-200">
                                <button type="button" @click="specOpen = !specOpen" class="w-full flex items-center justify-between px-5 py-3 bg-gray-50 hover:bg-gray-100 text-xs font-bold text-gray-700 transition">
                                    <span class="inline-flex items-center gap-2">
                                        <i class="fa-solid fa-table-list text-indigo-600"></i>
                                        <span>Detailed Specifications Comparison Table</span>
                                        <span class="text-gray-400 font-normal" x-text="'(' + (item.buyer_attributes || []).length + ' requirements)'"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px] text-indigo-600">
                                        <span x-text="specOpen ? 'Hide Specifications' : 'Show Specifications'"></span>
                                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="specOpen ? 'rotate-180' : ''"></i>
                                    </span>
                                </button>

                                <div x-show="specOpen" x-cloak class="overflow-x-auto border-t border-gray-200 bg-white">
                                    <table class="w-full text-left border-collapse min-w-[950px] text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-5 py-2.5 font-bold text-gray-600 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 w-64">Required Specification</th>
                                                <th class="px-5 py-2.5 font-bold text-indigo-700 bg-indigo-50/50 uppercase tracking-wider w-56">Buyer Requires</th>
                                                <template x-for="q in data.summary" :key="q.quotation_id">
                                                    <th class="px-5 py-2.5 min-w-[240px] font-bold text-gray-700" x-text="q.supplier_name"></th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="row in item.buyer_attributes" :key="row.attribute_id">
                                                <tr x-show="buyerAttrRowVisible(item, row.attribute_id)" class="hover:bg-gray-50/50">
                                                    <td class="px-5 py-2.5 font-semibold text-gray-800 sticky left-0 bg-white z-10" x-text="row.name"></td>
                                                    <td class="px-5 py-2.5 font-semibold text-indigo-900 bg-indigo-50/30" x-text="row.value || '—'"></td>
                                                    <template x-for="q in data.summary" :key="q.quotation_id">
                                                        <td class="px-5 py-2.5">
                                                            <template x-if="offersFor(item, q.quotation_id).length === 0">
                                                                <span class="text-gray-300">—</span>
                                                            </template>
                                                            <template x-for="productResponse in offersFor(item, q.quotation_id)" :key="productResponse.quotation_item_id">
                                                                <div>
                                                                    <template x-for="attr in productResponse.attributes.filter(a => a.attribute_id === row.attribute_id)" :key="attr.attribute_id">
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-[11px] font-medium"
                                                                              :class="highlightDiffs ? attrStatusClass(attr.status) : 'text-gray-700 bg-gray-50 border-gray-200'"
                                                                              x-text="attr.supplier_value || 'Not Specified'"></span>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </td>
                                                    </template>
                                                </tr>
                                            </template>

                                            {{-- Additional Specifications Row --}}
                                            <tr class="bg-gray-50/30">
                                                <td class="px-5 py-2.5 font-bold text-gray-700 sticky left-0 bg-gray-50/30 z-10 align-top">Additional Specifications Offered</td>
                                                <td class="px-5 py-2.5 bg-indigo-50/20 text-gray-400 italic">None required</td>
                                                <template x-for="q in data.summary" :key="q.quotation_id">
                                                    <td class="px-5 py-2.5 align-top">
                                                        <template x-for="productResponse in offersFor(item, q.quotation_id)" :key="productResponse.quotation_item_id">
                                                            <div class="space-y-1">
                                                                <template x-if="productResponse.additional_specifications.length === 0">
                                                                    <span class="text-gray-300 text-xs">—</span>
                                                                </template>
                                                                <template x-for="spec in productResponse.additional_specifications" :key="spec.attribute_id">
                                                                    <p class="text-xs text-gray-700"><span class="text-gray-400" x-text="spec.name + ': '"></span><span class="font-medium" x-text="spec.value"></span></p>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </td>
                                                </template>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- ══ 3. COMMERCIAL TERMS & PROPOSALS COMPARISON ══ --}}
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs" x-data="{ open: {} }">
                    <div class="px-5 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-file-contract text-indigo-600"></i>
                            <h3 class="text-sm font-bold text-gray-900">Commercial Terms &amp; Conditions Comparison</h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[950px] text-xs">
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="[label, field] in [
                                    ['Payment Terms', 'payment_terms'],
                                    ['Warranty Terms', 'warranty_terms'],
                                    ['Support Terms', 'support_terms'],
                                    ['Proposal / Notes', 'proposal']
                                ]" :key="field">
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-5 py-3 font-bold text-gray-700 sticky left-0 bg-white z-10 w-48 align-top">
                                            <span x-text="label"></span>
                                        </td>
                                        <template x-for="q in data.summary" :key="q.quotation_id">
                                            <td class="px-5 py-3 align-top max-w-sm">
                                                <template x-if="!commercialFor(q.quotation_id)[field]">
                                                    <span class="text-gray-300 italic">Not specified</span>
                                                </template>
                                                <template x-if="commercialFor(q.quotation_id)[field]">
                                                    <div class="space-y-1">
                                                        <p class="text-gray-700 leading-relaxed whitespace-pre-line"
                                                           :class="!open[q.quotation_id + field] ? 'line-clamp-3' : ''"
                                                           x-text="commercialFor(q.quotation_id)[field]"></p>
                                                        <button type="button" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700"
                                                                @click="open[q.quotation_id + field] = !open[q.quotation_id + field]"
                                                                x-text="open[q.quotation_id + field] ? 'Show less' : 'Show more'"></button>
                                                    </div>
                                                </template>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ══ 4. ALL OFFERS COMPARISON MODAL ══ --}}
                <div x-show="allOffersModalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeAllOffersModal()"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-2xl max-w-4xl w-full p-6 shadow-xl border border-gray-200 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">Compare All Supplier Offers</h3>
                                    <p class="text-xs text-gray-500" x-text="activeModalProductResponse?.supplierName + ' — ' + activeModalProductResponse?.item_name"></p>
                                </div>
                                <button type="button" @click="closeAllOffersModal()" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2.5 font-bold text-gray-600 uppercase">Offer Name</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-600 uppercase">Method</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-600 uppercase">Unit Price</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-600 uppercase">Total</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-600 uppercase">Delivery</th>
                                            <th class="px-4 py-2.5 font-bold text-gray-600 uppercase">Status / Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="offer in (activeModalProductResponse?.offers || [])" :key="offer.id">
                                            <tr :class="offer.is_selected ? 'bg-emerald-50/40' : ''">
                                                <td class="px-4 py-3">
                                                    <span class="font-bold text-gray-900 block" x-text="offer.product_name"></span>
                                                    <span class="text-[11px] text-gray-400" x-show="offer.is_primary">Default Primary Offer</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-[10px] font-medium" x-text="methodLabel(offer.offer_method)"></span>
                                                </td>
                                                <td class="px-4 py-3 font-semibold text-gray-800" x-text="money(offer.unit_price)"></td>
                                                <td class="px-4 py-3 font-bold text-gray-900" x-text="money(offer.total_price)"></td>
                                                <td class="px-4 py-3 text-gray-600" x-text="offer.delivery_time ? offer.delivery_time + ' days' : '—'"></td>
                                                <td class="px-4 py-3">
                                                    <template x-if="offer.is_selected">
                                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-md">
                                                            <i class="fa-solid fa-check"></i> Selected
                                                        </span>
                                                    </template>
                                                    <template x-if="!offer.is_selected">
                                                        <button type="button" @click="selectOfferAjax(activeModalProductResponse.quotation_id, activeModalProductResponse.quotation_item_id, offer.id); closeAllOffersModal();"
                                                                class="text-xs font-semibold px-2.5 py-1 rounded-md border border-indigo-200 text-indigo-700 bg-white hover:bg-indigo-50 shadow-2xs">
                                                            Select Offer
                                                        </button>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="pt-3 border-t border-gray-100 flex justify-end">
                                <button type="button" @click="closeAllOffersModal()" class="text-xs font-semibold px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══ 5. DIRECT AWARD CONFIRMATION MODAL ══ --}}
                <div x-show="awardModalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeAwardModal()"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 space-y-5">
                            <div class="flex items-start justify-between pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                                        <i class="fa-solid fa-award"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900">Award Quotation</h3>
                                        <p class="text-xs text-gray-500" x-text="awardQuotationData?.quotation_number + ' · ' + awardQuotationData?.supplier_name"></p>
                                    </div>
                                </div>
                                <button type="button" @click="closeAwardModal()" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Effective Award Amount:</span>
                                    <span class="font-bold text-sm text-gray-900" x-text="money(commercialFor(awardQuotationData?.quotation_id).effective_grand_total || commercialFor(awardQuotationData?.quotation_id).grand_total, commercialFor(awardQuotationData?.quotation_id).currency_code)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Estimated Lead Time:</span>
                                    <span class="font-semibold text-gray-800" x-text="(commercialFor(awardQuotationData?.quotation_id).lead_time_days || '—') + ' days'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Coverage:</span>
                                    <span class="font-semibold text-gray-800" x-text="partialFor(awardQuotationData?.quotation_id).quoted_count + ' of ' + partialFor(awardQuotationData?.quotation_id).total_count + ' items quoted'"></span>
                                </div>
                            </div>

                            <p class="text-xs text-gray-600">
                                Awarding this quotation initiates a formal contract notification to <strong x-text="awardQuotationData?.supplier_name"></strong>. Once the supplier accepts the award, a binding Purchase Order will automatically be generated using your selected winning offers.
                            </p>

                            <form method="POST" :action="awardUrl(awardQuotationData?.quotation_id)" @submit="awardSubmitting = true">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="space-y-1.5 mb-5">
                                    <label class="block text-xs font-bold text-gray-700">Award Note / Special Instructions (Optional)</label>
                                    <textarea name="award_note" x-model="awardNote" rows="3" maxlength="1000"
                                              placeholder="Add any delivery instructions, purchase reference notes, or comments for the supplier…"
                                              class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>

                                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                                    <button type="button" @click="closeAwardModal()" class="text-xs font-semibold px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit" :disabled="awardSubmitting"
                                            class="inline-flex items-center gap-2 text-xs font-bold px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm">
                                        <i class="fa-solid fa-circle-notch fa-spin" x-show="awardSubmitting"></i>
                                        <i class="fa-solid fa-award" x-show="!awardSubmitting"></i>
                                        <span>Confirm &amp; Award Contract</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </template>

    </div>

    @include('backend.buyer.procurement.quotations.partials._compare-store')

@endsection
