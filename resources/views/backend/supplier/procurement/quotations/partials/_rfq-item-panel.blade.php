{{--
    "Buyer Requested" read-only panel for one buyer RFQ item — rendered once
    per RFQ item (not once per offer, since one RFQ item can now have
    several Product Responses, each with several Offers). Expects `$rfqItem`
    (an RfqItem model) from the parent @foreach in _form.blade.php;
    `rfqItemsById` is the same server-built Alpine lookup _item.blade.php
    used to read from, just addressed by the literal id here instead of a
    per-item `item.rfq_item_id`.
--}}
@php($rid = $rfqItem->id)
<div class="bg-slate-50/90 border border-slate-200 rounded-xl p-3.5 sm:p-4 mb-3 shadow-2xs">
    <div class="flex items-center justify-between gap-3 mb-3 pb-2.5 border-b border-slate-200/80">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-200 text-slate-700">
                <i class="fa-solid fa-file-contract text-slate-500 text-[10px]"></i> Buyer Requested
            </span>
            <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full"
                  :class="rfqItemsById[{{ $rid }}]?.is_requirement ? 'bg-amber-100 text-amber-900 border border-amber-300' : (rfqItemsById[{{ $rid }}]?.is_marketplace ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-300')"
                  x-text="rfqItemsById[{{ $rid }}]?.is_requirement ? 'Requirement (Quotation Only)' : (rfqItemsById[{{ $rid }}]?.is_marketplace ? 'Marketplace Product' : 'Custom Product')">
            </span>
        </div>
        <div class="inline-flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-md border border-slate-200 shadow-2xs">
            <span class="text-[11px] text-slate-400 font-medium">Quantity:</span>
            <span class="text-xs font-bold text-slate-900">
                <span x-text="rfqItemsById[{{ $rid }}]?.quantity"></span>
                <span class="text-slate-600 font-semibold" x-text="rfqItemsById[{{ $rid }}]?.unit"></span>
            </span>
        </div>
    </div>

    <div class="flex items-start gap-3 mb-3">
        <template x-if="rfqItemsById[{{ $rid }}]?.listing_image_url">
            <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 shrink-0 bg-white shadow-2xs">
                <img :src="rfqItemsById[{{ $rid }}]?.listing_image_url" :alt="rfqItemsById[{{ $rid }}]?.item_name" class="w-full h-full object-cover">
            </div>
        </template>
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Requirement / Item Name</p>
            <h4 class="text-sm sm:text-base font-bold text-gray-900 leading-snug" x-text="rfqItemsById[{{ $rid }}]?.item_name"></h4>
        </div>
    </div>

    <div class="mb-3 p-3 bg-white rounded-lg border border-slate-200/80 space-y-2.5">
        <div>
            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                <span x-text="rfqItemsById[{{ $rid }}]?.category_names?.length > 1 ? 'Suggested Categories (' + rfqItemsById[{{ $rid }}]?.category_names?.length + ')' : 'Category'"></span>
            </span>
            <div class="flex flex-wrap items-center gap-1.5">
                <template x-for="(catName, cIdx) in (rfqItemsById[{{ $rid }}]?.category_names?.length ? rfqItemsById[{{ $rid }}]?.category_names : [rfqItemsById[{{ $rid }}]?.category_name || 'General Category'])" :key="cIdx">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-800 border border-indigo-200/80 shadow-2xs">
                        <i class="fa-solid fa-folder-tree text-indigo-500 text-[10px]"></i>
                        <span x-text="catName"></span>
                    </span>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-slate-100">
            <div>
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Target / Est. Unit Price</span>
                <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1.5">
                    <i class="fa-solid fa-tag text-emerald-600 text-[11px] shrink-0"></i>
                    <template x-if="rfqItemsById[{{ $rid }}]?.estimated_unit_price">
                        <span>
                            {{ $rfq->currency_code ?? 'USD' }} <span x-text="rfqItemsById[{{ $rid }}]?.estimated_unit_price"></span>
                            <span class="text-[10px] text-slate-400 font-normal">/ <span x-text="rfqItemsById[{{ $rid }}]?.unit || 'unit'"></span></span>
                        </span>
                    </template>
                    <template x-if="!rfqItemsById[{{ $rid }}]?.estimated_unit_price">
                        <span class="text-slate-400 font-normal italic text-xs">Not specified</span>
                    </template>
                </span>
            </div>
            <div>
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Estimated Total Budget</span>
                <span class="text-xs font-semibold text-indigo-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-coins text-amber-500 text-[11px] shrink-0"></i>
                    <template x-if="rfqItemsById[{{ $rid }}]?.estimated_unit_price && rfqItemsById[{{ $rid }}]?.quantity">
                        <span x-text="formatMoney(parseFloat(String(rfqItemsById[{{ $rid }}]?.estimated_unit_price).replace(/,/g, '')) * parseFloat(rfqItemsById[{{ $rid }}]?.quantity || 1))"></span>
                    </template>
                    <template x-if="!rfqItemsById[{{ $rid }}]?.estimated_unit_price">
                        <span class="text-slate-400 font-normal italic text-xs">Open for bidding</span>
                    </template>
                </span>
            </div>
        </div>
    </div>

    <template x-if="rfqItemsById[{{ $rid }}]?.description">
        <div class="mb-3">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                <i class="fa-solid fa-align-left text-slate-400 mr-1"></i> Description / Specifications:
            </span>
            <p class="text-xs text-slate-700 leading-relaxed bg-white p-2.5 rounded-lg border border-slate-200/80 whitespace-pre-line"
               x-text="rfqItemsById[{{ $rid }}]?.description"></p>
        </div>
    </template>

    <template x-if="rfqItemsById[{{ $rid }}]?.attributes && rfqItemsById[{{ $rid }}]?.attributes.length > 0">
        <div class="mt-2.5 pt-2.5 border-t border-slate-200/60" x-data="{ expanded: false }">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                    <i class="fa-solid fa-list-check text-indigo-500 text-[11px]"></i>
                    <span x-text="rfqItemsById[{{ $rid }}]?.is_marketplace ? 'Product Specifications:' : 'Category Specifications:'"></span>
                    <span class="text-gray-400 font-normal" x-text="'(' + rfqItemsById[{{ $rid }}]?.attributes.length + ')'"></span>
                </p>
                <button type="button" x-show="rfqItemsById[{{ $rid }}]?.attributes.length > 6"
                        @click="expanded = !expanded"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                    <span x-text="expanded ? 'Show Less' : 'Show All (' + rfqItemsById[{{ $rid }}]?.attributes.length + ')'"></span>
                </button>
            </div>

            {{-- Compact view: top 6 pills --}}
            <template x-if="!expanded">
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="(attr, aIdx) in rfqItemsById[{{ $rid }}]?.attributes.slice(0, 6)" :key="aIdx">
                        <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-700 shadow-2xs">
                            <span class="text-gray-500 font-medium" x-text="attr.name + ':'"></span>
                            <span class="font-semibold text-gray-900" x-text="attr.value"></span>
                        </span>
                    </template>
                </div>
            </template>

            {{-- Expanded view: Group-wise detailed specification cards --}}
            <template x-if="expanded">
                <div class="space-y-3.5 mt-2">
                    <template x-for="(group, gIdx) in (rfqItemsById[{{ $rid }}]?.grouped_attributes && rfqItemsById[{{ $rid }}]?.grouped_attributes.length > 0 ? rfqItemsById[{{ $rid }}]?.grouped_attributes : [{ group_name: 'Key Features', attributes: rfqItemsById[{{ $rid }}]?.attributes }])" :key="gIdx">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                            <div class="bg-slate-50/80 px-4 py-2.5 border-b border-gray-200/80">
                                <h5 class="text-xs font-bold text-gray-800" x-text="group.group_name"></h5>
                            </div>
                            <div class="divide-y divide-gray-100 text-xs">
                                <template x-for="(attr, aIdx) in group.attributes" :key="aIdx">
                                    <div class="px-4 py-2.5 flex items-center justify-between gap-4 hover:bg-slate-50/30 transition">
                                        <span class="text-gray-600 font-normal" x-text="attr.name"></span>
                                        <span class="text-gray-900 font-semibold text-right" x-text="attr.value"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </template>

    <template x-if="rfqItemsById[{{ $rid }}]?.specs && rfqItemsById[{{ $rid }}]?.specs.length > 0">
        <div class="mt-2.5 pt-2.5 border-t border-slate-200/60">
            <p class="text-[10px] font-bold text-indigo-900 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-sliders text-indigo-500 text-[11px]"></i>
                Buyer's Custom Specifications:
            </p>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="(spec, sIdx) in rfqItemsById[{{ $rid }}]?.specs" :key="sIdx">
                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-md bg-indigo-50/90 border border-indigo-200 text-indigo-950 font-medium shadow-2xs">
                        <span class="text-indigo-600 font-semibold" x-text="(spec.name || 'Spec') + ':'"></span>
                        <span class="font-bold text-gray-900" x-text="spec.value || '—'"></span>
                    </span>
                </template>
            </div>
        </div>
    </template>

    <template x-if="rfqItemsById[{{ $rid }}]?.attachments && rfqItemsById[{{ $rid }}]?.attachments.length > 0">
        <div class="mt-2.5 pt-2.5 border-t border-slate-200/60">
            <p class="text-[10px] font-bold text-amber-900 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                <i class="fa-solid fa-paperclip text-amber-600 text-[11px]"></i>
                Buyer's Reference Files &amp; Drawings:
            </p>
            <div class="flex flex-wrap gap-2">
                <template x-for="att in rfqItemsById[{{ $rid }}]?.attachments" :key="att.id">
                    <a :href="att.url" target="_blank"
                       class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-md bg-white border border-gray-200 text-gray-700 hover:border-amber-400 hover:text-amber-950 shadow-2xs transition-colors">
                        <i class="fa-solid text-xs text-gray-400"
                           :class="att.is_image ? 'fa-file-image text-emerald-500' : 'fa-file-pdf text-red-500'"></i>
                        <span class="font-medium text-gray-800" x-text="att.name"></span>
                        <span class="text-[10px] text-gray-400 font-mono" x-text="'(' + att.size + ')'"></span>
                        <i class="fa-solid fa-download text-[9px] text-gray-400 ml-0.5"></i>
                    </a>
                </template>
            </div>
        </div>
    </template>
</div>

{{-- Quantity-allocation indicator — informational only (never blocks
     goNext()/submit): sums each Product Response group's quantity against
     the buyer's requested total, only meaningful for a free-text
     Requirement item where a real target quantity exists. --}}
<template x-if="rfqItemsById[{{ $rid }}]?.is_requirement">
    <div class="mb-3 flex items-center gap-2 text-xs px-3 py-2 rounded-lg border"
         :class="allocatedQty({{ $rid }}) === parseFloat(rfqItemsById[{{ $rid }}]?.quantity || 0) ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800'">
        <i class="fa-solid" :class="allocatedQty({{ $rid }}) === parseFloat(rfqItemsById[{{ $rid }}]?.quantity || 0) ? 'fa-circle-check' : 'fa-triangle-exclamation'"></i>
        <span>
            <span x-text="allocatedQty({{ $rid }})"></span> of <span x-text="rfqItemsById[{{ $rid }}]?.quantity"></span> requested quantity allocated across your product(s).
        </span>
    </div>
</template>
