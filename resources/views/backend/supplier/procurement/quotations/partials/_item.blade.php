{{--
    Product Response Container (quotation_items layer).
    In context: `item` (QuotationItem model / Alpine state).
    Each Product Response responds to an RFQ requirement and can contain
    one or more offers (Primary + Alternatives) stored in quotation_item_offers.
--}}
<div :id="'product-response-' + item._localKey"
     class="bg-slate-50/50 border border-slate-200 rounded-xl p-4 mb-4 last:mb-0 shadow-2xs transition-shadow duration-700"
     {{-- Briefly highlighted after returning from the marketplace product
          selector (restoreFromMarketplaceSelection() in _form.blade.php),
          which also scrolls this element into view — so it's obvious which
          item just received the new offer on a multi-item RFQ, without
          touching any other item's expand/collapse state. --}}
     :class="item._justUpdated ? 'ring-2 ring-indigo-400 ring-offset-2' : ''">

    {{-- Parent Product Response hidden fields (only emitted if at least one offer exists) --}}
    <template x-if="item.offers && item.offers.length > 0">
        <div>
            <input type="hidden" :name="'items['+item._localKey+'][id]'" :value="item.id ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][rfq_item_id]'" :value="item.rfq_item_id ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][item_name]'" :value="item.item_name ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][quantity]'" :value="item.quantity ?? '1'">
            <input type="hidden" :name="'items['+item._localKey+'][unit_id]'" :value="item.unit_id ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][custom_unit]'" :value="item.custom_unit ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][unit_price]'" :value="item.unit_price ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][tax_rate]'" :value="item.tax_rate ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][discount_amount]'" :value="item.discount_amount ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][lead_time_days]'" :value="item.lead_time_days ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][description]'" :value="item.description ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][offered_listing_id]'" :value="item.offered_listing_id ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][offered_variant_id]'" :value="item.offered_variant_id ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][response_method]'" :value="item._responseMethod ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][client_ref]'" :value="item._localKey ?? ''">
            <input type="hidden" :name="'items['+item._localKey+'][is_alternative]'" value="0">
            <input type="hidden" :name="'items['+item._localKey+'][is_optional_addon]'" value="0">
        </div>
    </template>

    {{-- Product Response Header (if extra item or multiple responses) --}}
    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200/70"
         x-show="!item.rfq_item_id || itemsForRfq(item.rfq_item_id).length > 1" x-cloak>
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                <i class="fa-solid fa-box"></i>
            </span>
            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">
                Product Response
                <span x-show="!item.rfq_item_id" class="text-indigo-600">(Additional Item)</span>
            </span>
        </div>
        <button type="button" @click="removeProduct(item)"
                class="text-xs font-semibold text-red-500 hover:text-red-700 flex items-center gap-1">
            <i class="fa-solid fa-trash-can"></i> Remove Line
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         STATE 1: EMPTY / NO OFFERS YET
         Supplier chooses how they wish to respond to this requirement.
         ══════════════════════════════════════════════════════════════════════ --}}
    <template x-if="!item.offers || item.offers.length === 0">
        <div class="bg-white border-2 border-dashed border-slate-200 rounded-xl p-5 text-center space-y-4">
            <div>
                <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2 text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-800">Choose How to Respond</h4>
                <p class="text-xs text-gray-500 max-w-md mx-auto mt-0.5">
                    Select an approved catalog product, build a custom offer, or copy the buyer's specifications directly.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-3xl mx-auto">
                {{-- 1. Marketplace Product --}}
                <button type="button" @click="openMarketplaceSelector(item)"
                        class="p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-400 text-left transition-all group shadow-2xs hover:shadow-sm">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-store"></i>
                        </span>
                        <span class="text-xs font-bold text-indigo-950">Marketplace Product</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-tight">
                        Pick matching items from the marketplace. Supports selecting multiple offers.
                    </p>
                </button>

                {{-- 2. Custom Offer — "Copy buyer specifications" is a
                     checkbox INSIDE this offer's Category & Specifications
                     section (checked by default), not a separate response
                     method; see addCustomOffer()/copyBuyerSpecsIntoOffer()
                     in _form.blade.php. --}}
                <button type="button" @click="addCustomOffer(item)"
                        class="p-3.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-400 text-left transition-all group shadow-2xs hover:shadow-sm">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-slate-700 text-white flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-pen-ruler"></i>
                        </span>
                        <span class="text-xs font-bold text-gray-900">Custom Offer</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-tight">
                        Define specifications and pricing — starts pre-filled from the buyer's own category and attributes, fully editable.
                    </p>
                </button>

                {{-- 3. Document Quotation --}}
                <button type="button" @click="addDocumentOffer(item)"
                        class="p-3.5 rounded-xl border border-red-200 bg-red-50/30 hover:bg-red-50 hover:border-red-400 text-left transition-all group shadow-2xs hover:shadow-sm">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-red-600 text-white flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-file-arrow-up"></i>
                        </span>
                        <span class="text-xs font-bold text-red-950">Upload Document</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-tight">
                        Attach a formal PDF quotation or brochure for this requirement.
                    </p>
                </button>
            </div>
        </div>
    </template>

    {{-- ══════════════════════════════════════════════════════════════════════
         STATE 2: OFFERS EXIST
         Render the Primary Offer and Alternative Offers.
         ══════════════════════════════════════════════════════════════════════ --}}
    <template x-if="item.offers && item.offers.length > 0">
        <div>
            <div class="space-y-3">
                <template x-for="(offer, offerIndex) in item.offers" :key="offer._localKey">
                    @include('backend.supplier.procurement.quotations.partials._offer-card')
                </template>
            </div>

            {{-- Alternative Offers Actions --}}
            <div class="mt-3.5 pt-3 border-t border-slate-200 flex items-center justify-between flex-wrap gap-2">
                <div x-show="allowAlternativeProducts" class="flex items-center gap-2 flex-wrap" x-data="{ addMenuOpen: false }">
                    <div class="relative">
                        <button type="button" @click="addMenuOpen = !addMenuOpen" @click.outside="addMenuOpen = false"
                                class="inline-flex items-center gap-2 text-xs font-bold px-3.5 py-2 rounded-lg bg-amber-50 text-amber-900 border border-amber-300 hover:bg-amber-100 transition-colors shadow-2xs">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Add Alternative Product</span>
                            <i class="fa-solid fa-chevron-down text-[9px]"></i>
                        </button>

                        {{-- Alternative Options Dropdown --}}
                        <div x-show="addMenuOpen" x-cloak
                             class="absolute left-0 mt-1 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-30 divide-y divide-gray-100">
                            <div class="py-1">
                                <button type="button" @click="addMenuOpen = false; openMarketplaceSelector(item)"
                                        class="w-full px-3.5 py-2 text-left text-xs font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center gap-2 transition-colors">
                                    <i class="fa-solid fa-store text-emerald-600 text-xs w-4"></i>
                                    <span>From Catalog / Marketplace</span>
                                </button>
                                <button type="button" @click="addMenuOpen = false; addCustomOffer(item, false)"
                                        class="w-full px-3.5 py-2 text-left text-xs font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center gap-2 transition-colors">
                                    <i class="fa-solid fa-pen-ruler text-slate-600 text-xs w-4"></i>
                                    <span>Custom Alternative</span>
                                </button>
                            </div>
                            <div class="py-1">
                                <button type="button" @click="addMenuOpen = false; addDocumentOffer(item, false)"
                                        class="w-full px-3.5 py-2 text-left text-xs font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center gap-2 transition-colors">
                                    <i class="fa-solid fa-file-arrow-up text-red-600 text-xs w-4"></i>
                                    <span>Quotation Document</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <span class="text-[11px] text-gray-400">
                        Offers give the buyer optional alternatives to pick from.
                    </span>
                </div>

                <div x-show="!allowAlternativeProducts" class="text-[11px] text-gray-400 italic">
                    <i class="fa-solid fa-ban mr-1 text-gray-300"></i> Alternative products are not permitted by the buyer on this RFQ.
                </div>
            </div>
        </div>
    </template>

</div>
