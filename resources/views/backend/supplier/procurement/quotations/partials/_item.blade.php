{{--
    One RFQ-item response card. `item` and `index` come from the parent
    x-for="(item, index) in items" scope in _form.blade.php. rfqItemsById is
    the server-built read-only lookup of the buyer's requirement for
    item.rfq_item_id (name/category/qty/unit/attributes).
--}}
<div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0">
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-gray-500">Item <span x-text="index + 1"></span></span>
        <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-xs">
            <i class="fa-solid fa-trash"></i> Remove
        </button>
    </div>

    <input type="hidden" :name="'items['+index+'][id]'" :value="item.id ?? ''">
    <input type="hidden" :name="'items['+index+'][rfq_item_id]'" :value="item.rfq_item_id ?? ''">
    <input type="hidden" :name="'items['+index+'][is_alternative]'" :value="item.is_alternative ? 1 : 0">
    <input type="hidden" :name="'items['+index+'][is_optional_addon]'" value="0">

    {{-- Buyer Requested (read-only) --}}
    <div x-show="item.rfq_item_id && rfqItemsById[item.rfq_item_id]" x-cloak class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 mb-4 shadow-2xs">
        <div class="flex items-start justify-between gap-3 mb-2.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-200/80 text-slate-700">Buyer Requested</span>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                      :class="rfqItemsById[item.rfq_item_id]?.is_marketplace ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200'"
                      x-text="rfqItemsById[item.rfq_item_id]?.is_marketplace ? 'Marketplace Product' : 'Custom Requirement'">
                </span>
            </div>
            <div class="text-right shrink-0 bg-white px-2.5 py-1 rounded-md border border-slate-200/80 shadow-2xs">
                <span class="text-xs font-bold text-gray-900">
                    <span x-text="rfqItemsById[item.rfq_item_id]?.quantity"></span> 
                    <span class="text-gray-500 font-medium text-[11px]" x-text="rfqItemsById[item.rfq_item_id]?.unit"></span>
                </span>
            </div>
        </div>

        <div class="flex items-start gap-3">
            <template x-if="rfqItemsById[item.rfq_item_id]?.listing_image_url">
                <div class="w-12 h-12 rounded-lg overflow-hidden border border-gray-200 shrink-0 bg-white shadow-2xs">
                    <img :src="rfqItemsById[item.rfq_item_id]?.listing_image_url" :alt="rfqItemsById[item.rfq_item_id]?.item_name" class="w-full h-full object-cover">
                </div>
            </template>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-900 leading-snug" x-text="rfqItemsById[item.rfq_item_id]?.item_name"></p>
                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-gray-500 mt-0.5">
                    <span x-text="rfqItemsById[item.rfq_item_id]?.category_name || 'General Category'"></span>
                    <template x-if="rfqItemsById[item.rfq_item_id]?.estimated_unit_price">
                        <span class="text-indigo-600 font-semibold">
                            &middot; Target: {{ $rfq->currency_code ?? 'USD' }} <span x-text="rfqItemsById[item.rfq_item_id]?.estimated_unit_price"></span> / <span x-text="rfqItemsById[item.rfq_item_id]?.unit || 'unit'"></span>
                        </span>
                    </template>
                </div>
                <p x-show="rfqItemsById[item.rfq_item_id]?.description" class="text-xs text-gray-600 mt-1.5 leading-relaxed bg-white/70 p-2 rounded-lg border border-slate-200/60" x-text="rfqItemsById[item.rfq_item_id]?.description"></p>
            </div>
        </div>

        {{-- Standard Category Attributes / Listing Specifications --}}
        <template x-if="rfqItemsById[item.rfq_item_id]?.attributes && rfqItemsById[item.rfq_item_id]?.attributes.length > 0">
            <div class="mt-2.5 pt-2.5 border-t border-slate-200/60" x-data="{ expanded: false }">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                        <i class="fa-solid fa-list-check text-indigo-500 text-[11px]"></i>
                        <span x-text="rfqItemsById[item.rfq_item_id]?.is_marketplace ? 'Product Specifications:' : 'Category Specifications:'"></span>
                        <span class="text-gray-400 font-normal" x-text="'(' + rfqItemsById[item.rfq_item_id]?.attributes.length + ')'"></span>
                    </p>
                    <button type="button" x-show="rfqItemsById[item.rfq_item_id]?.attributes.length > 6"
                            @click="expanded = !expanded"
                            class="text-[10px] font-semibold text-indigo-600 hover:text-indigo-800">
                        <span x-text="expanded ? 'Show Less' : 'Show All (' + rfqItemsById[item.rfq_item_id]?.attributes.length + ')'"></span>
                    </button>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="(attr, aIdx) in (expanded ? rfqItemsById[item.rfq_item_id]?.attributes : rfqItemsById[item.rfq_item_id]?.attributes.slice(0, 6))" :key="aIdx">
                        <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-700 shadow-2xs">
                            <span class="text-gray-500 font-medium" x-text="attr.name + ':'"></span>
                            <span class="font-semibold text-gray-900" x-text="attr.value"></span>
                        </span>
                    </template>
                </div>
            </div>
        </template>

        {{-- Custom Specifications --}}
        <template x-if="rfqItemsById[item.rfq_item_id]?.specs && rfqItemsById[item.rfq_item_id]?.specs.length > 0">
            <div class="mt-2.5 pt-2.5 border-t border-slate-200/60">
                <p class="text-[10px] font-bold text-indigo-900 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                    <i class="fa-solid fa-sliders text-indigo-500 text-[11px]"></i>
                    Buyer's Custom Specifications:
                </p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="(spec, sIdx) in rfqItemsById[item.rfq_item_id]?.specs" :key="sIdx">
                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-md bg-indigo-50/90 border border-indigo-200 text-indigo-950 font-medium shadow-2xs">
                            <span class="text-indigo-600 font-semibold" x-text="(spec.name || 'Spec') + ':'"></span>
                            <span class="font-bold text-gray-900" x-text="spec.value || '—'"></span>
                        </span>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Offer Type --}}
    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-700 mb-1.5">Offer Type</label>
        <div class="flex flex-wrap gap-2">
            <label class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border cursor-pointer"
                   :class="getOfferType(item) === 'existing' ? 'border-indigo-500 bg-indigo-50/50 text-indigo-700 font-semibold' : 'border-gray-200 text-gray-600'">
                <input type="radio" class="sr-only" :checked="getOfferType(item) === 'existing'" @change="setOfferType(item, 'existing')">
                Use Existing Listing
            </label>
            <label class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border cursor-pointer"
                   :class="getOfferType(item) === 'custom' ? 'border-indigo-500 bg-indigo-50/50 text-indigo-700 font-semibold' : 'border-gray-200 text-gray-600'">
                <input type="radio" class="sr-only" :checked="getOfferType(item) === 'custom'" @change="setOfferType(item, 'custom')">
                Create Custom Offer
            </label>
            <template x-if="allowAlternativeProducts">
                <label class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border cursor-pointer"
                       :class="getOfferType(item) === 'alternative' ? 'border-amber-500 bg-amber-50/60 text-amber-700 font-semibold' : 'border-gray-200 text-gray-600'">
                    <input type="radio" class="sr-only" :checked="getOfferType(item) === 'alternative'" @change="setOfferType(item, 'alternative')">
                    Offer Alternative Product
                </label>
            </template>
        </div>
    </div>

    {{-- Existing listing search (shown for existing + alternative offer types) --}}
    <div x-show="getOfferType(item) !== 'custom'" x-cloak class="relative mb-3">
        <label class="block text-xs font-medium text-gray-700 mb-1.5">Select From Your Listings</label>
        <div class="flex items-center gap-2">
            <input type="text" x-model="item._listingQuery" @input.debounce.400ms="searchListingsForItem(item)"
                   placeholder="Search your marketplace listings..."
                   class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            <button type="button" x-show="item.offered_listing_id" @click="clearListingForItem(item)" class="text-xs text-gray-500 hover:text-gray-700 whitespace-nowrap px-2">
                Clear
            </button>
        </div>
        <div x-show="item._listingResults.length > 0" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
            <template x-for="l in item._listingResults" :key="l.id">
                <button type="button" @click="selectListingForItem(item, l)" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center justify-between gap-2">
                    <span x-text="l.name"></span>
                    <span class="text-[11px] text-gray-400" x-text="l.category_name"></span>
                </button>
            </template>
        </div>
        <p x-show="item.offered_listing_id" class="text-[11px] text-emerald-700 mt-1">
            <i class="fa-solid fa-circle-check mr-1"></i> Linked to your listing — you can still adjust price/specs below for this quotation only.
        </p>
        <input type="hidden" :name="'items['+index+'][offered_listing_id]'" :value="item.offered_listing_id ?? ''">

        <div x-show="item.offered_listing_id && item._variants.length > 0" x-cloak class="mt-2">
            <label class="block text-xs font-medium text-gray-700 mb-1">Variant</label>
            <select :name="'items['+index+'][offered_variant_id]'" x-model="item.offered_variant_id" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                <option value="">No specific variant</option>
                <template x-for="v in item._variants" :key="v.id">
                    <option :value="v.id" x-text="v.label"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Your Item Name</label>
            <input type="text" :name="'items['+index+'][item_name]'" x-model="item.item_name" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Lead Time (Days)</label>
            <input type="number" min="0" :name="'items['+index+'][lead_time_days]'" x-model.number="item.lead_time_days" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="mt-3">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description / Notes</label>
        <textarea :name="'items['+index+'][description]'" x-model="item.description" rows="2" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"></textarea>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
            <input type="number" step="0.001" min="0.001" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
            <select :name="'items['+index+'][unit_id]'" x-model="item.unit_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-2 py-2 bg-white">
                <option value="">Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->symbol ?: $unit->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" required :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Tax Rate %</label>
            <input type="number" step="0.01" min="0" max="100" :name="'items['+index+'][tax_rate]'" x-model.number="item.tax_rate" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Discount</label>
            <input type="number" step="0.01" min="0" :name="'items['+index+'][discount_amount]'" x-model.number="item.discount_amount" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="mt-2 text-right text-xs text-gray-500">
        Line total: <span class="font-bold text-indigo-700" x-text="formatMoney(lineTotal(item))"></span>
    </div>

    <div x-show="item.rfq_item_id && rfqItemsById[item.rfq_item_id]" x-cloak class="mt-3">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-1.5">
            <label class="block text-sm font-medium text-gray-700">Specifications Comparison</label>
            <label class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700 cursor-pointer select-none">
                <input type="checkbox" @change="$event.target.checked && applyCopyBuyerRequirements(item)" class="w-3.5 h-3.5 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                Copy buyer's requirements into your offer
            </label>
        </div>
        @include('backend.supplier.procurement.quotations.partials._item-attributes')
    </div>
</div>
