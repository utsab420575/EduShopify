{{--
    Single Offer Card: Represents one offer (either Primary or Alternative)
    under a Product Response (quotation_item).
    In context: `item` (the parent product response), `offer` (this offer),
    `offerIndex` (0-indexed position in item.offers).
--}}
<div class="border rounded-xl mb-3 last:mb-0 transition-all shadow-xs"
     :class="offer.is_primary ? 'border-indigo-300 bg-white ring-1 ring-indigo-200/50' : 'border-amber-200 bg-amber-50/20'">

    {{-- Hidden form bindings for this offer --}}
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][id]'" :value="offer.id ?? ''">
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][offer_method]'" :value="offer.offer_method ?? 'marketplace'">
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][marketplace_product_id]'" :value="offer.marketplace_product_id ?? ''">
    {{-- x-if (not x-show) paired with the variant <select> below: both bind
         the same field name, so only one may ever be in the DOM at once —
         otherwise a native form submit (Save Draft/Submit) would send two
         values for offered_variant_id and silently drop one. --}}
    <template x-if="!offer._variants || offer._variants.length === 0">
        <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][offered_variant_id]'" :value="offer.offered_variant_id ?? ''">
    </template>
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][is_primary]'" :value="offer.is_primary ? 1 : 0">
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][is_selected]'" :value="offer.is_selected ? 1 : 0">
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][sort_order]'" :value="offer.sort_order ?? offerIndex">
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][category_id]'" :value="offer.category_id ?? ''">
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][specifications]'" :value="JSON.stringify(getOfferSpecsPayload(offer))">

    {{-- Offer Header --}}
    <div class="p-3.5 flex items-center justify-between gap-3 cursor-pointer select-none border-b"
         :class="offer.is_primary ? 'border-indigo-100 bg-indigo-50/40' : 'border-amber-100 bg-amber-50/50'"
         @click="offer._collapsed = !offer._collapsed">

        <div class="flex items-center gap-2.5 min-w-0">
            <button type="button" @click.stop="offer._collapsed = !offer._collapsed"
                    class="w-6 h-6 rounded-md flex items-center justify-center text-gray-400 hover:bg-white/80 shrink-0">
                <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="!offer._collapsed ? 'rotate-180' : ''"></i>
            </button>

            {{-- Primary vs Alternative Badge --}}
            <template x-if="offer.is_primary">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-600 text-white shadow-2xs shrink-0">
                    <i class="fa-solid fa-star text-[9px] text-amber-300"></i> Primary Offer
                </span>
            </template>
            <template x-if="!offer.is_primary">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300 shrink-0">
                    <i class="fa-solid fa-code-fork text-[9px]"></i> Alternative <span x-text="offerIndex"></span>
                </span>
            </template>

            {{-- Method Pill --}}
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-700 shrink-0">
                <i class="fa-solid text-[10px] text-gray-500 mr-1"
                   :class="{
                       'fa-store text-emerald-600': offer.offer_method === 'marketplace',
                       'fa-pen-ruler text-indigo-600': offer.offer_method === 'custom',
                       'fa-copy text-amber-600': offer.offer_method === 'copy_spec',
                       'fa-file-lines text-red-600': offer.offer_method === 'document'
                   }"></i>
                <span x-text="methodLabel(offer.offer_method)"></span>
            </span>

            {{-- Product Name Title --}}
            <span class="text-sm font-bold text-gray-900 truncate" x-text="offer.product_name || 'Untitled Offer'"></span>
        </div>

        {{-- Right: Total & Actions --}}
        <div class="flex items-center gap-2.5 shrink-0" @click.stop>
            <div class="text-right">
                <template x-if="offer.unit_price">
                    <div class="flex items-baseline gap-1 justify-end">
                        <span class="text-xs font-bold"
                              :class="offer.is_primary ? 'text-indigo-700' : 'text-amber-800'"
                              x-text="formatMoney(offerTotal(offer))"></span>
                        <span class="text-[10px] text-gray-400" x-show="offer.quantity > 1" x-text="'(' + offer.quantity + ' @ ' + formatMoney(offer.unit_price) + ')'"></span>
                    </div>
                </template>
                <template x-if="!offer.unit_price">
                    <span class="text-[11px] text-gray-400 italic">Not priced</span>
                </template>
                <template x-if="!offer.is_primary">
                    <span class="block text-[9px] text-amber-700 font-medium italic">Alternative (not in total)</span>
                </template>
            </div>

            {{-- Set Primary Button --}}
            <button type="button" x-show="!offer.is_primary"
                    @click="makePrimaryOffer(item, offer)"
                    title="Make this offer the primary response for this item"
                    class="text-xs font-semibold px-2.5 py-1 rounded-md bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-50 shadow-2xs transition-colors flex items-center gap-1">
                <i class="fa-regular fa-star text-[10px]"></i>
                <span>Use as Primary</span>
            </button>

            {{-- Remove Offer Button --}}
            <button type="button" @click="removeOffer(item, offerIndex)"
                    title="Remove this offer"
                    class="text-red-500 hover:text-red-700 p-1.5 rounded hover:bg-red-50 transition-colors">
                <i class="fa-solid fa-trash-can text-xs"></i>
            </button>
        </div>
    </div>

    {{-- Offer Body (when expanded) --}}
    <div x-show="!offer._collapsed" x-cloak class="p-4 space-y-4">

        {{-- Marketplace Linked Product Banner --}}
        <template x-if="offer.offer_method === 'marketplace' && offer.marketplace_product_id">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-start gap-3">
                <template x-if="offer._image_url">
                    <img :src="offer._image_url" :alt="offer.product_name" class="w-12 h-12 rounded-lg object-cover border border-slate-200 bg-white shrink-0">
                </template>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap text-[11px] mb-0.5">
                        <span class="font-bold text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[10px]"></i> Catalog Product
                        </span>
                        <template x-if="offer._brand_name">
                            <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 font-semibold" x-text="offer._brand_name"></span>
                        </template>
                        <template x-if="offer._category_name">
                            <span class="text-slate-500" x-text="'Category: ' + offer._category_name"></span>
                        </template>
                    </div>
                    <p class="text-xs text-slate-600 line-clamp-1" x-text="offer.description || 'Pre-filled from your approved marketplace listing.'"></p>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <template x-if="offer._slug">
                        <a :href="'/v2/product/' + offer._slug" target="_blank" rel="noopener noreferrer"
                           class="text-xs font-semibold px-2 py-1 rounded-md bg-white border border-slate-200 text-indigo-600 hover:text-indigo-800 hover:border-indigo-300 shadow-2xs flex items-center gap-1 transition-colors"
                           title="Open product details page in new tab">
                            <span>Details</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                        </a>
                    </template>
                    <button type="button" @click="openMarketplaceSelector(item)"
                            class="text-xs font-semibold text-gray-500 hover:text-gray-800 underline">
                        Change
                    </button>
                </div>
            </div>
        </template>

        {{-- Variant Picker (if marketplace listing has variants) --}}
        <template x-if="offer._variants && offer._variants.length > 0">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Product Variant</label>
                <select :name="'items['+item._localKey+'][offers]['+offer._localKey+'][offered_variant_id]'"
                        x-model="offer.offered_variant_id"
                        class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">Standard / Default Variant</option>
                    <template x-for="v in offer._variants" :key="v.id">
                        <option :value="v.id" x-text="v.label"></option>
                    </template>
                </select>
            </div>
        </template>

        {{-- Product Name & Quantity Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">
                    Product / Offer Name <span class="text-red-500">*</span>
                </label>
                <input type="text" required
                       :name="'items['+item._localKey+'][offers]['+offer._localKey+'][product_name]'"
                       x-model="offer.product_name"
                       @input="syncItemWithPrimaryOffer(item)"
                       placeholder="e.g. Dell OptiPlex 7090 Desktop PC"
                       class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-900 bg-white">
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                <input type="number" step="0.001" min="0.001" required
                       :name="'items['+item._localKey+'][offers]['+offer._localKey+'][quantity]'"
                       x-model.number="offer.quantity"
                       @input="syncItemWithPrimaryOffer(item)"
                       class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-bold text-gray-900 bg-white text-center">
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Unit</label>
                <select :name="'items['+item._localKey+'][offers]['+offer._localKey+'][unit_id]'"
                        x-model="offer.unit_id"
                        @change="syncItemWithPrimaryOffer(item)"
                        class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-900">
                    <option value="">Select unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->symbol ?: $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Pricing & Terms Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/80">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-indigo-900 mb-1">
                    Unit Price <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" step="0.01" min="0" required
                           :name="'items['+item._localKey+'][offers]['+offer._localKey+'][unit_price]'"
                           x-model.number="offer.unit_price"
                           @input="syncItemWithPrimaryOffer(item)"
                           placeholder="0.00"
                           class="focus-accent w-full px-3 py-2 border border-indigo-200 rounded-lg text-sm font-bold text-indigo-900 bg-white">
                </div>
                <p x-show="rfqItemsById[item.rfq_item_id]?.estimated_unit_price" x-cloak class="text-[10px] text-indigo-600 mt-1">
                    Est: <span x-text="rfqItemsById[item.rfq_item_id]?.estimated_unit_price"></span>
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Delivery Time (Days)</label>
                <input type="number" min="0"
                       :name="'items['+item._localKey+'][offers]['+offer._localKey+'][delivery_time]'"
                       x-model.number="offer.delivery_time"
                       @input="syncItemWithPrimaryOffer(item)"
                       placeholder="e.g. 14"
                       class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tax Rate %</label>
                <input type="number" step="0.01" min="0" max="100"
                       :name="'items['+item._localKey+'][offers]['+offer._localKey+'][tax_rate]'"
                       x-model.number="offer.tax_rate"
                       @input="syncItemWithPrimaryOffer(item)"
                       placeholder="0.00"
                       class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Discount Amount</label>
                <input type="number" step="0.01" min="0"
                       :name="'items['+item._localKey+'][offers]['+offer._localKey+'][discount]'"
                       x-model.number="offer.discount"
                       @input="syncItemWithPrimaryOffer(item)"
                       placeholder="0.00"
                       class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
        </div>

        {{-- Description & Notes --}}
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Description / Offer Notes</label>
            <textarea :name="'items['+item._localKey+'][offers]['+offer._localKey+'][description]'"
                      x-model="offer.description"
                      rows="2"
                      placeholder="Specify warranty, brand highlights, model details, or fulfillment conditions..."
                      class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white resize-none"></textarea>
        </div>

        {{-- Document Upload (if Document offer method) --}}
        <template x-if="offer.offer_method === 'document'">
            <div class="p-3.5 bg-red-50/40 border border-red-200 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-red-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-file-arrow-up text-red-600"></i> Quotation Document Attached
                    </span>
                    <span class="text-[10px] text-gray-400">PDF, Word, Excel up to 10MB</span>
                </div>
                <div class="flex flex-wrap gap-2 mb-1">
                    <template x-for="doc in (offer._documents || [])" :key="doc.id">
                        <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-md bg-white border border-gray-200 text-gray-800 shadow-2xs">
                            <i class="fa-solid fa-file-pdf text-red-500 text-xs"></i>
                            <a :href="doc.url" target="_blank" class="font-medium hover:underline truncate max-w-[200px]" x-text="doc.name"></a>
                            <span class="text-gray-400 text-[10px]" x-text="'(' + doc.size + ')'"></span>
                        </span>
                    </template>
                </div>
            </div>
        </template>

        {{-- ══════════════════════════════════════════════════════════════════════
             SPECIFICATIONS EDITOR (Custom Specs + Category Attributes)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="pt-3 border-t border-gray-200 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-indigo-600 text-xs"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-800">Specifications &amp; Criteria</span>
                    <span class="text-[11px] font-normal text-gray-400"
                          x-text="'(' + (offer._custom_specs?.length || 0) + ' custom attributes)'"></span>
                </div>
                <button type="button" @click="addCustomSpec(offer)"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors shadow-2xs">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Add Specification</span>
                </button>
            </div>

            {{-- Custom Specification Key-Value Rows --}}
            <template x-if="offer._custom_specs && offer._custom_specs.length > 0">
                <div class="space-y-2 bg-slate-50/60 p-3 rounded-xl border border-slate-200">
                    <template x-for="(spec, sIdx) in offer._custom_specs" :key="sIdx">
                        <div class="flex items-center gap-2">
                            <div class="w-5/12">
                                <input type="text"
                                       x-model="spec.name"
                                       placeholder="Specification Name (e.g. RAM, Processor)"
                                       class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                            </div>
                            <div class="flex-1">
                                <input type="text"
                                       x-model="spec.value"
                                       placeholder="Offered Value (e.g. 16GB DDR4, Intel Core i7)"
                                       class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                            </div>
                            <button type="button" @click="removeCustomSpec(offer, sIdx)"
                                    title="Delete specification"
                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors shrink-0">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!offer._custom_specs || offer._custom_specs.length === 0">
                <div class="py-2.5 px-3 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-center">
                    <p class="text-xs text-gray-500 flex items-center justify-center gap-1.5">
                        <i class="fa-regular fa-lightbulb text-amber-500"></i>
                        <span>Click <strong>"Add Specification"</strong> to detail technical attributes (RAM, storage, dimensions, etc.).</span>
                    </p>
                </div>
            </template>
        </div>

    </div>
</div>
