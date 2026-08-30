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

    <div x-data="quotationComparePage({{ $rfq->id }}, {{ $maxItems }}, '{{ route('buyer.quotations.compare.data', $rfq) }}')" x-cloak>

        <x-backend.page-header title="Quotation Comparison" :subtitle="$rfq->title . ' — RFQ ' . $rfq->rfq_number">
            <x-slot:actions>
                <a href="{{ route('buyer.rfqs.show', $rfq) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back to RFQ</a>
                <a href="{{ route('buyer.quotations.index', ['rfq' => $rfq->id]) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back to RFQ Responses</a>
            </x-slot:actions>
        </x-backend.page-header>

        {{-- Loading --}}
        <div x-show="loading" class="py-16 text-center text-gray-400">
            <i class="fa-solid fa-circle-notch fa-spin text-2xl"></i>
            <p class="text-sm mt-2">Loading comparison…</p>
        </div>

        {{-- Empty / too few --}}
        <div x-show="!loading && count === 0">
            <x-backend.empty-state icon="fa-scale-balanced" title="No quotations selected" description="Go to RFQ Responses and check &quot;Add to Compare&quot; on the quotations you'd like to compare." />
        </div>
        <div x-show="!loading && count === 1">
            <x-backend.empty-state icon="fa-scale-balanced" title="Select at least two quotations to compare" description="You currently have 1 quotation selected. Add at least one more from RFQ Responses." />
        </div>

        {{-- Main comparison --}}
        <template x-if="!loading && count >= 2 && data">
        <div>

            {{-- Controls --}}
            <div class="flex flex-wrap items-center gap-4 mb-6 bg-white rounded-xl border border-gray-200 px-4 py-3">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" x-model="highlightDiffs" class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                    Highlight Differences
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" x-model="showDiffsOnly" class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                    Show Differences Only
                </label>
                <button type="button" @click="if (confirm('Clear the entire comparison?')) clearAll()" class="ml-auto text-sm font-medium text-red-600 hover:text-red-700">
                    <i class="fa-regular fa-trash-can mr-1"></i> Clear Comparison
                </button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">

                {{-- ══ 1. QUOTATION SUMMARY ══ --}}
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">Quotation Summary</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-indigo-50/60">Buyer Requirement</th>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <th class="px-5 py-3 min-w-[220px] align-top">
                                <p class="text-sm font-semibold text-gray-900" x-text="q.supplier_name"></p>
                                <p class="text-xs text-gray-400" x-text="q.quotation_number"></p>
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full border"
                                          :class="{
                                              'bg-blue-50 text-blue-700 border-blue-200': q.status === 'shortlisted',
                                              'bg-green-50 text-green-700 border-green-200': q.status === 'awarded',
                                              'bg-amber-50 text-amber-800 border-amber-200': ['submitted','under_review','revised'].includes(q.status),
                                          }" x-text="q.status.replace('_',' ')"></span>
                                    <span x-show="q.is_shortlisted" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-star"></i> Shortlisted</span>
                                    <span x-show="q.rfq_version_stale" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200" :title="'Quotation based on RFQ v' + q.rfq_version_no + ', current is v' + {{ $rfq->current_version_no }}">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Older RFQ version
                                    </span>
                                </div>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10">RFQ Version</td>
                        <td class="px-5 py-2.5 text-gray-500 bg-indigo-50/30" x-text="'Current v' + {{ $rfq->current_version_no }}"></td>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <td class="px-5 py-2.5" x-text="'v' + q.rfq_version_no"></td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10">Revision</td>
                        <td class="px-5 py-2.5 text-gray-400 bg-indigo-50/30">—</td>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <td class="px-5 py-2.5" x-text="'#' + q.current_revision_no"></td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10">Submitted</td>
                        <td class="px-5 py-2.5 text-gray-400 bg-indigo-50/30">—</td>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <td class="px-5 py-2.5" x-text="q.submitted_at || '—'"></td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10">Valid Until</td>
                        <td class="px-5 py-2.5 text-gray-400 bg-indigo-50/30">—</td>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <td class="px-5 py-2.5" x-text="q.valid_until || '—'"></td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10">Response</td>
                        <td class="px-5 py-2.5 text-gray-400 bg-indigo-50/30">—</td>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <td class="px-5 py-2.5">
                                <span x-text="partialFor(q.quotation_id).is_full ? 'Full Response' : 'Partial Response'"></span>
                                <span class="text-gray-400" x-text="'(' + partialFor(q.quotation_id).quoted_count + ' of ' + partialFor(q.quotation_id).total_count + ' items quoted)'"></span>
                            </td>
                        </template>
                    </tr>
                </tbody>

                {{-- ══ 2. COMMERCIAL COMPARISON ══ --}}
                <thead>
                    <tr class="bg-gray-50">
                        <th colspan="100%" class="px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">Commercial Comparison</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10">Buyer Budget</td>
                        <td class="px-5 py-2.5 text-gray-700 bg-indigo-50/30">{{ $buyerBudgetLabel }}</td>
                        <template x-for="q in data.summary" :key="q.quotation_id"><td class="px-5 py-2.5 text-gray-400">—</td></template>
                    </tr>
                    <template x-for="[label, field] in [['Subtotal','subtotal'],['Discount','discount_amount'],['Tax','tax_amount'],['Shipping','shipping_charge']]" :key="field">
                        <tr>
                            <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10" x-text="label"></td>
                            <td class="px-5 py-2.5 text-gray-400 bg-indigo-50/30">—</td>
                            <template x-for="q in data.summary" :key="q.quotation_id">
                                <td class="px-5 py-2.5" x-text="money(commercialFor(q.quotation_id)[field], commercialFor(q.quotation_id).currency_code)"></td>
                            </template>
                        </tr>
                    </template>
                    <tr class="font-semibold">
                        <td class="px-5 py-3 text-gray-900 sticky left-0 bg-white z-10">Grand Total</td>
                        <td class="px-5 py-3 text-gray-400 bg-indigo-50/30">—</td>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <td class="px-5 py-3">
                                <span :class="highlightDiffs && data.commercial.badges.lowest_grand_total_id === q.quotation_id ? 'text-emerald-700' : 'text-gray-900'" x-text="money(commercialFor(q.quotation_id).grand_total, commercialFor(q.quotation_id).currency_code)"></span>
                                <span x-show="data.commercial.badges.lowest_grand_total_id === q.quotation_id" class="ml-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Lowest Grand Total</span>
                            </td>
                        </template>
                    </tr>
                    <tr x-show="!data.commercial.badges.same_currency">
                        <td colspan="100%" class="px-5 py-2 text-xs text-amber-700 bg-amber-50 sticky left-0">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> Selected quotations use different currencies — totals are not converted, so "Lowest Grand Total" cannot be determined across them.
                        </td>
                    </tr>
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10">Lead Time</td>
                        <td class="px-5 py-2.5 text-gray-400 bg-indigo-50/30">—</td>
                        <template x-for="q in data.summary" :key="q.quotation_id">
                            <td class="px-5 py-2.5">
                                <span x-text="commercialFor(q.quotation_id).lead_time_days ? commercialFor(q.quotation_id).lead_time_days + ' days' : '—'"></span>
                                <span x-show="data.commercial.badges.shortest_lead_time_id === q.quotation_id" class="ml-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Shortest Lead Time</span>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
            </div>
            </div>

            {{-- ══ 3. RFQ ITEM COMPARISON ══ --}}
            <template x-for="item in data.items" :key="item.rfq_item_id">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
                    <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900" x-text="item.item_name"></h3>
                        <p class="text-xs text-gray-500" x-text="'Requested: ' + item.quantity + ' ' + (item.unit || '')"></p>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px] text-sm">
                        <thead class="bg-gray-50/60">
                            <tr>
                                <th class="px-5 py-2 text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50/60 z-10">Attribute</th>
                                <th class="px-5 py-2 text-xs font-semibold text-gray-500 uppercase bg-indigo-50/60">Buyer Requires</th>
                                <template x-for="q in data.summary" :key="q.quotation_id">
                                    <th class="px-5 py-2 min-w-[200px]">
                                        <template x-if="offersFor(item, q.quotation_id).length === 0">
                                            <span class="text-xs font-semibold text-gray-400">Not Quoted</span>
                                        </template>
                                        <template x-for="offer in offersFor(item, q.quotation_id)" :key="offer.quotation_item_id">
                                            <div class="mb-1">
                                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full border"
                                                      :class="{
                                                          'bg-emerald-50 text-emerald-700 border-emerald-200': offer.offer_type === 'existing_product',
                                                          'bg-gray-100 text-gray-600 border-gray-200': offer.offer_type === 'custom',
                                                          'bg-blue-50 text-blue-700 border-blue-200': offer.offer_type === 'alternative',
                                                      }" x-text="offerTypeLabel(offer.offer_type)"></span>
                                                <a x-show="offer.offered_listing" :href="offer.offered_listing ? '/listing/' + offer.offered_listing.slug : '#'" target="_blank" class="block text-xs mt-0.5" style="color:var(--theme-primary)" x-text="offer.offered_listing?.name"></a>
                                            </div>
                                        </template>
                                    </th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="row in item.buyer_attributes" :key="row.attribute_id">
                                <tr x-show="buyerAttrRowVisible(item, row.attribute_id)">
                                    <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10" x-text="row.name"></td>
                                    <td class="px-5 py-2.5 text-gray-700 bg-indigo-50/30" x-text="row.value"></td>
                                    <template x-for="q in data.summary" :key="q.quotation_id">
                                        <td class="px-5 py-2.5">
                                            <template x-if="offersFor(item, q.quotation_id).length === 0">
                                                <span class="text-gray-300">—</span>
                                            </template>
                                            <template x-for="offer in offersFor(item, q.quotation_id)" :key="offer.quotation_item_id">
                                                <div class="mb-1">
                                                    <template x-for="attr in offer.attributes.filter(a => a.attribute_id === row.attribute_id)" :key="attr.attribute_id">
                                                        <span class="inline-flex items-center text-xs px-1.5 py-0.5 rounded border"
                                                              :class="highlightDiffs ? attrStatusClass(attr.status) : 'text-gray-700 bg-gray-50 border-gray-200'"
                                                              x-text="attr.supplier_value || 'Not Specified'"></span>
                                                    </template>
                                                </div>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </template>

                            {{-- Commercial line for this item --}}
                            <tr class="bg-gray-50/40">
                                <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-gray-50/40 z-10">Unit Price / Line Total</td>
                                <td class="px-5 py-2.5 bg-indigo-50/30 text-gray-400">—</td>
                                <template x-for="q in data.summary" :key="q.quotation_id">
                                    <td class="px-5 py-2.5">
                                        <template x-if="offersFor(item, q.quotation_id).length === 0">
                                            <span class="text-xs font-semibold text-gray-400">Not Quoted</span>
                                        </template>
                                        <template x-for="offer in offersFor(item, q.quotation_id)" :key="offer.quotation_item_id">
                                            <div class="text-xs mb-1">
                                                <span x-text="money(offer.unit_price, commercialFor(q.quotation_id).currency_code)"></span>
                                                <span class="text-gray-400"> × </span><span x-text="offer.quantity"></span>
                                                <span class="text-gray-400"> = </span>
                                                <span class="font-medium" x-text="money(offer.line_total, commercialFor(q.quotation_id).currency_code)"></span>
                                            </div>
                                        </template>
                                    </td>
                                </template>
                            </tr>

                            {{-- Additional offered specifications (not requested by buyer) --}}
                            <tr>
                                <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10 align-top">Additional Offered Specifications</td>
                                <td class="px-5 py-2.5 bg-indigo-50/30 text-gray-400">—</td>
                                <template x-for="q in data.summary" :key="q.quotation_id">
                                    <td class="px-5 py-2.5 align-top">
                                        <template x-for="offer in offersFor(item, q.quotation_id)" :key="offer.quotation_item_id">
                                            <div class="mb-1">
                                                <template x-if="offer.additional_specifications.length === 0">
                                                    <span class="text-gray-300 text-xs">—</span>
                                                </template>
                                                <template x-for="spec in offer.additional_specifications" :key="spec.attribute_id">
                                                    <p class="text-xs text-gray-600"><span class="text-gray-400" x-text="spec.name + ':'"></span> <span x-text="spec.value"></span></p>
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
            </template>

            {{-- ══ 4. OPTIONAL ADD-ONS ══ --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Optional Add-Ons</h3>
                    <p class="text-xs text-gray-500">Additional products/services offered by suppliers that were not part of the RFQ requirement. These do not count toward RFQ item coverage.</p>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px] text-sm">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="px-5 py-2 text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50/60">&nbsp;</th>
                            <template x-for="q in data.summary" :key="q.quotation_id">
                                <th class="px-5 py-2 min-w-[200px] text-xs font-semibold text-gray-500 uppercase" x-text="q.supplier_name"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white">Add-on Items</td>
                            <template x-for="q in data.summary" :key="q.quotation_id">
                                <td class="px-5 py-2.5">
                                    <template x-if="addonsFor(q.quotation_id).items.length === 0"><span class="text-gray-300 text-xs">None</span></template>
                                    <template x-for="addon in addonsFor(q.quotation_id).items" :key="addon.quotation_item_id">
                                        <p class="text-xs text-gray-700" :class="addon.data_violation ? 'text-red-600 font-semibold' : ''">
                                            <span x-text="addon.item_name"></span> — <span x-text="money(addon.line_total, commercialFor(q.quotation_id).currency_code)"></span>
                                            <span x-show="addon.data_violation" class="block text-[10px]">⚠ DATA/BUSINESS RULE VIOLATION: flagged as both add-on and alternative</span>
                                        </p>
                                    </template>
                                </td>
                            </template>
                        </tr>
                        <tr class="font-medium">
                            <td class="px-5 py-2.5 text-gray-700 sticky left-0 bg-white">Optional Add-on Line Total</td>
                            <template x-for="q in data.summary" :key="q.quotation_id">
                                <td class="px-5 py-2.5" x-text="money(addonsFor(q.quotation_id).addon_line_total, commercialFor(q.quotation_id).currency_code)"></td>
                            </template>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            {{-- ══ 5. TERMS / CONDITIONS ══ --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6" x-data="{ open: {} }">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Terms &amp; Conditions</h3>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px] text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="[label, field] in [['Payment Terms','payment_terms'],['Warranty Terms','warranty_terms'],['Support Terms','support_terms'],['Proposal / Notes','proposal']]" :key="field">
                            <tr>
                                <td class="px-5 py-2.5 font-medium text-gray-700 sticky left-0 bg-white z-10 w-40">
                                    <span x-text="label"></span>
                                </td>
                                <template x-for="q in data.summary" :key="q.quotation_id">
                                    <td class="px-5 py-2.5 align-top max-w-xs">
                                        <template x-if="!commercialFor(q.quotation_id)[field]">
                                            <span class="text-gray-300">—</span>
                                        </template>
                                        <template x-if="commercialFor(q.quotation_id)[field]">
                                            <div>
                                                <p class="text-gray-600 whitespace-pre-line" :class="!open[q.quotation_id + field] ? 'line-clamp-3' : ''" x-text="commercialFor(q.quotation_id)[field]"></p>
                                                <button type="button" class="text-xs font-medium mt-1" style="color:var(--theme-primary)" @click="open[q.quotation_id + field] = !open[q.quotation_id + field]" x-text="open[q.quotation_id + field] ? 'Show less' : 'Show more'"></button>
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

            {{-- ══ 6. ACTIONS ══ --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-10">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px] text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-5 py-3 sticky left-0 bg-white w-40">&nbsp;</td>
                            <template x-for="q in data.summary" :key="q.quotation_id">
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <a :href="'/buyer/quotations/' + q.quotation_id" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">View / Manage</a>
                                        <form method="POST" action="{{ route('buyer.messages.start') }}">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="recipient_account_id" :value="q.supplier_account_id">
                                            <input type="hidden" name="context_type" value="quotation">
                                            <input type="hidden" name="context_id" :value="q.quotation_id">
                                            <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Message Supplier</button>
                                        </form>
                                        <button type="button" @click="remove(q.quotation_id)" class="text-xs font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Remove</button>
                                    </div>
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

    @include('backend.buyer.procurement.quotations.partials._compare-store')

@endsection
