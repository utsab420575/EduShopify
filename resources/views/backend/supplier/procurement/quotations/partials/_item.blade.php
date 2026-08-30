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
    <div x-show="item.rfq_item_id && rfqItemsById[item.rfq_item_id]" x-cloak class="bg-gray-50 border border-gray-200 rounded-xl p-3 mb-3">
        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Buyer Requested</p>
        <p class="text-sm font-semibold text-gray-900" x-text="rfqItemsById[item.rfq_item_id]?.item_name"></p>
        <p class="text-xs text-gray-500 mt-0.5">
            <span x-text="rfqItemsById[item.rfq_item_id]?.category_name || 'No category'"></span>
            &middot; Qty <span x-text="rfqItemsById[item.rfq_item_id]?.quantity"></span> <span x-text="rfqItemsById[item.rfq_item_id]?.unit"></span>
        </p>
        <p x-show="rfqItemsById[item.rfq_item_id]?.description" class="text-xs text-gray-500 mt-1" x-text="rfqItemsById[item.rfq_item_id]?.description"></p>
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
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Specifications Comparison</label>
        @include('backend.supplier.procurement.quotations.partials._item-attributes')
    </div>
</div>
