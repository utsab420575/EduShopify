{{--
    One RFQ-item response card. `item` and `index` come from the parent
    x-for="(item, index) in items" scope in _form.blade.php. rfqItemsById is
    the server-built read-only lookup of the buyer's requirement for
    item.rfq_item_id (name/category/qty/unit/attributes).

    Two independent disclosure levels: `_collapsed` toggles the whole card
    down to a one-line header (accordion, for scanning many items), and
    `_advancedOpen` — only relevant while expanded — toggles Offer Type /
    listing search / specs comparison, so pricing an item never requires
    touching either unless the supplier wants to.
--}}
<div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0">
    <div class="flex items-center gap-2 cursor-pointer select-none" @click="item._collapsed = !item._collapsed">
        <button type="button" @click.stop="item._collapsed = !item._collapsed" class="w-6 h-6 rounded-md flex items-center justify-center text-gray-400 hover:bg-gray-100 shrink-0">
            <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="!item._collapsed ? 'rotate-180' : ''"></i>
        </button>
        <span class="text-xs font-semibold text-gray-500 shrink-0">Item <span x-text="index + 1"></span></span>
        <span class="text-sm font-medium text-gray-800 truncate" x-text="item.item_name || 'Untitled item'"></span>
        <span x-show="item._collapsed" x-cloak class="ml-auto shrink-0 flex items-center gap-1.5">
            <span x-show="item.unit_price" x-cloak class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full px-2.5 py-1" x-text="formatMoney(lineTotal(item))"></span>
            <span x-show="!item.unit_price" x-cloak class="text-[11px] text-gray-400 italic">Not priced yet</span>
        </span>
        <button type="button" @click.stop="removeItem(index)" class="text-red-500 hover:text-red-700 text-xs shrink-0" :class="item._collapsed ? '' : 'ml-auto'">
            <i class="fa-solid fa-trash"></i> Remove
        </button>
    </div>

    <input type="hidden" :name="'items['+index+'][id]'" :value="item.id ?? ''">
    <input type="hidden" :name="'items['+index+'][rfq_item_id]'" :value="item.rfq_item_id ?? ''">
    <input type="hidden" :name="'items['+index+'][is_alternative]'" :value="item.is_alternative ? 1 : 0">
    <input type="hidden" :name="'items['+index+'][is_optional_addon]'" value="0">

    <div x-show="!item._collapsed" x-cloak class="mt-3">

    {{-- Buyer Requested (read-only) --}}
    <div x-show="item.rfq_item_id && rfqItemsById[item.rfq_item_id]" x-cloak class="bg-slate-50/90 border border-slate-200 rounded-xl p-3.5 sm:p-4 mb-4 shadow-2xs">
        {{-- Header Status & Badges --}}
        <div class="flex items-center justify-between gap-3 mb-3 pb-2.5 border-b border-slate-200/80">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-200 text-slate-700">
                    <i class="fa-solid fa-file-contract text-slate-500 text-[10px]"></i> Buyer Requested
                </span>
                <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full"
                      :class="rfqItemsById[item.rfq_item_id]?.is_requirement ? 'bg-amber-100 text-amber-900 border border-amber-300' : (rfqItemsById[item.rfq_item_id]?.is_marketplace ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-gray-300')"
                      x-text="rfqItemsById[item.rfq_item_id]?.is_requirement ? 'Requirement (Quotation Only)' : (rfqItemsById[item.rfq_item_id]?.is_marketplace ? 'Marketplace Product' : 'Custom Product')">
                </span>
            </div>
            <div class="inline-flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-md border border-slate-200 shadow-2xs">
                <span class="text-[11px] text-slate-400 font-medium">Quantity:</span>
                <span class="text-xs font-bold text-slate-900">
                    <span x-text="rfqItemsById[item.rfq_item_id]?.quantity"></span>
                    <span class="text-slate-600 font-semibold" x-text="rfqItemsById[item.rfq_item_id]?.unit"></span>
                </span>
            </div>
        </div>

        {{-- Main Item Header --}}
        <div class="flex items-start gap-3 mb-3">
            <template x-if="rfqItemsById[item.rfq_item_id]?.listing_image_url">
                <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 shrink-0 bg-white shadow-2xs">
                    <img :src="rfqItemsById[item.rfq_item_id]?.listing_image_url" :alt="rfqItemsById[item.rfq_item_id]?.item_name" class="w-full h-full object-cover">
                </div>
            </template>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Requirement / Item Name</p>
                <h4 class="text-sm sm:text-base font-bold text-gray-900 leading-snug" x-text="rfqItemsById[item.rfq_item_id]?.item_name"></h4>
            </div>
        </div>

        {{-- Read-Only Details Grid (Categories, Target Price, Estimated Budget) --}}
        <div class="mb-3 p-3 bg-white rounded-lg border border-slate-200/80 space-y-2.5">
            {{-- Categories Section --}}
            <div>
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                    <span x-text="rfqItemsById[item.rfq_item_id]?.category_names?.length > 1 ? 'Suggested Categories (' + rfqItemsById[item.rfq_item_id]?.category_names?.length + ')' : 'Category'"></span>
                </span>
                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="(catName, cIdx) in (rfqItemsById[item.rfq_item_id]?.category_names?.length ? rfqItemsById[item.rfq_item_id]?.category_names : [rfqItemsById[item.rfq_item_id]?.category_name || 'General Category'])" :key="cIdx">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-800 border border-indigo-200/80 shadow-2xs">
                            <i class="fa-solid fa-folder-tree text-indigo-500 text-[10px]"></i>
                            <span x-text="catName"></span>
                        </span>
                    </template>
                </div>
            </div>

            {{-- Price & Estimated Budget Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2 border-t border-slate-100">
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Target / Est. Unit Price</span>
                    <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-tag text-emerald-600 text-[11px] shrink-0"></i>
                        <template x-if="rfqItemsById[item.rfq_item_id]?.estimated_unit_price">
                            <span>
                                {{ $rfq->currency_code ?? 'USD' }} <span x-text="rfqItemsById[item.rfq_item_id]?.estimated_unit_price"></span>
                                <span class="text-[10px] text-slate-400 font-normal">/ <span x-text="rfqItemsById[item.rfq_item_id]?.unit || 'unit'"></span></span>
                            </span>
                        </template>
                        <template x-if="!rfqItemsById[item.rfq_item_id]?.estimated_unit_price">
                            <span class="text-slate-400 font-normal italic text-xs">Not specified</span>
                        </template>
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Estimated Total Budget</span>
                    <span class="text-xs font-semibold text-indigo-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-coins text-amber-500 text-[11px] shrink-0"></i>
                        <template x-if="rfqItemsById[item.rfq_item_id]?.estimated_unit_price && rfqItemsById[item.rfq_item_id]?.quantity">
                            <span x-text="formatMoney(parseFloat(String(rfqItemsById[item.rfq_item_id]?.estimated_unit_price).replace(/,/g, '')) * parseFloat(rfqItemsById[item.rfq_item_id]?.quantity || 1))"></span>
                        </template>
                        <template x-if="!rfqItemsById[item.rfq_item_id]?.estimated_unit_price">
                            <span class="text-slate-400 font-normal italic text-xs">Open for bidding</span>
                        </template>
                    </span>
                </div>
            </div>
        </div>

        {{-- Description / Buyer's Notes --}}
        <template x-if="rfqItemsById[item.rfq_item_id]?.description">
            <div class="mb-3">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                    <i class="fa-solid fa-align-left text-slate-400 mr-1"></i> Description / Specifications:
                </span>
                <p class="text-xs text-slate-700 leading-relaxed bg-white p-2.5 rounded-lg border border-slate-200/80 whitespace-pre-line"
                   x-text="rfqItemsById[item.rfq_item_id]?.description"></p>
            </div>
        </template>

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

        {{-- Buyer's Reference Attachments --}}
        <template x-if="rfqItemsById[item.rfq_item_id]?.attachments && rfqItemsById[item.rfq_item_id]?.attachments.length > 0">
            <div class="mt-2.5 pt-2.5 border-t border-slate-200/60">
                <p class="text-[10px] font-bold text-amber-900 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                    <i class="fa-solid fa-paperclip text-amber-600 text-[11px]"></i>
                    Buyer's Reference Files &amp; Drawings:
                </p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="att in rfqItemsById[item.rfq_item_id]?.attachments" :key="att.id">
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

    {{-- Quick Quote: the only fields a supplier truly needs to price this item --}}
    <div class="mb-2">
        <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                Product Name <span class="text-red-500">*</span>
            </label>
            <span class="text-[11px] text-gray-400">Specify the brand, model, or product name you are offering</span>
        </div>
        <input type="text" 
               required
               :name="'items['+index+'][item_name]'" 
               x-model="item.item_name" 
               placeholder="e.g., Dell OptiPlex 7090 Desktop / Core i7 Custom PC"
               class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-900 bg-white placeholder-gray-400">
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
            {{-- Requirement ("Quotation Only") items: the buyer already fixed
                 the quantity — read-only so the supplier only decides price. --}}
            <template x-if="rfqItemsById[item.rfq_item_id]?.is_requirement">
                <div>
                    <div class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-700 font-medium" x-text="item.quantity"></div>
                    <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                </div>
            </template>
            <template x-if="!rfqItemsById[item.rfq_item_id]?.is_requirement">
                <input type="number" step="0.001" min="0.001" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </template>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
            <template x-if="rfqItemsById[item.rfq_item_id]?.is_requirement">
                <div>
                    <div class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-700 font-medium" x-text="rfqItemsById[item.rfq_item_id]?.unit || '—'"></div>
                    <input type="hidden" :name="'items['+index+'][unit_id]'" :value="item.unit_id ?? ''">
                </div>
            </template>
            <template x-if="!rfqItemsById[item.rfq_item_id]?.is_requirement">
                <select :name="'items['+index+'][unit_id]'" x-model="item.unit_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-2 py-2 bg-white">
                    <option value="">Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->symbol ?: $unit->name }}</option>
                    @endforeach
                </select>
            </template>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" required :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold">
            <p x-show="rfqItemsById[item.rfq_item_id]?.is_requirement && rfqItemsById[item.rfq_item_id]?.estimated_unit_price" x-cloak class="text-[10px] text-indigo-600 mt-1">
                Buyer's estimate: <span x-text="rfqItemsById[item.rfq_item_id]?.estimated_unit_price"></span>
            </p>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Lead Time (Days)</label>
            <input type="number" min="0" :name="'items['+index+'][lead_time_days]'" x-model.number="item.lead_time_days" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="mt-2 text-right text-xs text-gray-500">
        Line total: <span class="font-bold text-indigo-700" x-text="formatMoney(lineTotal(item))"></span>
    </div>

    <button type="button" @click="item._advancedOpen = !item._advancedOpen" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 mt-3">
        <i class="fa-solid text-[10px]" :class="item._advancedOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        <span x-text="item._advancedOpen ? 'Hide advanced options' : 'Advanced: listing, specs &amp; offer type'"></span>
    </button>

    <div x-show="item._advancedOpen" x-cloak class="mt-3 pt-3 border-t border-gray-100">

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

    {{-- Auto-matched listing suggestion — a suggestion only, never applied without the supplier's confirmation --}}
    <div x-show="item._suggestedListing" x-cloak class="mb-3 flex items-center flex-wrap gap-2 text-xs bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
        <i class="fa-solid fa-wand-magic-sparkles text-emerald-600"></i>
        <span class="text-emerald-800">Matches your listing: <span class="font-semibold" x-text="item._suggestedListing?.name"></span></span>
        <button type="button" @click="useSuggestedListing(item)" class="ml-auto font-semibold text-emerald-700 hover:text-emerald-900 underline">Use it</button>
        <button type="button" @click="dismissSuggestedListing(item)" class="text-gray-500 hover:text-gray-700">Dismiss</button>
    </div>

    {{-- Existing listing search (shown for existing + alternative offer types) --}}
    <div x-show="getOfferType(item) !== 'custom'" x-cloak class="relative mb-3">
        <label class="block text-xs font-medium text-gray-700 mb-1.5">Select From Your Listings</label>
        <div class="flex items-center gap-2">
            <input type="text" x-model="item._listingQuery" @input.debounce.400ms="searchListingsForItem(item)"
                   placeholder="Type at least 2 characters to search your listings..."
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
        {{-- No results hint (search ran but returned nothing) --}}
        <p x-show="item._listingQuery.length >= 2 && item._listingResults.length === 0 && !item.offered_listing_id"
           x-cloak class="text-[11px] text-amber-700 mt-1.5 flex items-center gap-1 bg-amber-50 border border-amber-200 rounded-md px-2.5 py-1.5">
            <i class="fa-solid fa-circle-info text-amber-500 shrink-0"></i>
            <span>No listings matched your search. You can still create a custom offer, or <a href="{{ route('supplier.catalog.listings.create') }}" target="_blank" class="underline font-semibold hover:text-amber-900">add products to your catalog</a>.</span>
        </p>
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

    <div class="mt-3">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description / Notes</label>
        <textarea :name="'items['+index+'][description]'" x-model="item.description" rows="2" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"></textarea>
    </div>

    <div class="grid grid-cols-2 gap-3 mt-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Tax Rate %</label>
            <input type="number" step="0.01" min="0" max="100" :name="'items['+index+'][tax_rate]'" x-model.number="item.tax_rate" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Discount</label>
            <input type="number" step="0.01" min="0" :name="'items['+index+'][discount_amount]'" x-model.number="item.discount_amount" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
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

    </div>{{-- /Advanced --}}

    </div>{{-- /!_collapsed --}}
</div>
