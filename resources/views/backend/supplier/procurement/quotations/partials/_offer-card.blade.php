{{--
    Single Offer Card: Represents one offer (either Primary or Alternative)
    under a Product Response (quotation_item).
    In context: `item` (the parent product response), `offer` (this offer),
    `offerIndex` (0-indexed position in item.offers).
--}}
<div class="border rounded-xl mb-3 last:mb-0 transition-all shadow-xs"
     :class="offer.is_primary ? 'border-indigo-300 bg-white ring-1 ring-indigo-200/50' : 'border-amber-200 bg-amber-50/20'"
     {{-- Custom/Copy-Spec offers autosave themselves: category, attribute
          values, price, quantity and specs all flow through this one
          watcher (see scheduleCustomOfferAutosave in _form.blade.php)
          instead of needing an @input/@change hook on every individual
          field below. offer_method never changes in place on an existing
          offer (no "switch method" control — only add/remove), so checking
          it once at mount is enough. The watcher's baseline is captured
          AFTER "Copy buyer specifications"' default-on auto-fill already ran
          (see addCustomOffer in _form.blade.php), so it only fires — and
          only then sets _hasUserEdited, which isValidOffer() on the backend
          uses to allow a still-unpriced offer through — on a genuine change
          the supplier makes afterward, never on the auto-copy itself. --}}
     x-init="(offer.offer_method === 'custom' || offer.offer_method === 'copy_spec') && $watch(
         () => JSON.stringify([offer.category_id, offer.unit_price, offer.quantity, offer._attribute_values, offer._custom_specs]),
         () => { offer._hasUserEdited = true; scheduleCustomOfferAutosave(); }
     )">

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
    {{-- Without this, a native Save Draft/Submit form post (as opposed to
         autosave, which builds this field directly in its JS payload) would
         send no attribute_values at all for this offer — and syncOfferAttributeValues()
         treats a missing/empty value as "clear them", wiping out whatever was
         saved. JSON-stringified same as specifications above, since a hidden
         input can only carry a string — QuotationService::syncItemOffers()
         decodes it back on that path. --}}
    <input type="hidden" :name="'items['+item._localKey+'][offers]['+offer._localKey+'][attribute_values]'" :value="JSON.stringify(offer._attribute_values || {})">

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
                    <button type="button" @click="openMarketplaceSelector(item, offer)"
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
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200/80">
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

            {{-- Per-offer, not one flat quotation-level value — the sidebar's
                 Shipping line sums each item's PRIMARY offer's shipping_charge
                 (see totalShipping() in _form.blade.php). --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Shipping Charge</label>
                <input type="number" step="0.01" min="0"
                       :name="'items['+item._localKey+'][offers]['+offer._localKey+'][shipping_charge]'"
                       x-model.number="offer.shipping_charge"
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

        {{-- Document Upload (if Document offer method) — each offer gets its
             own files, uploaded to quotation_item_offers' own 'document'
             media collection via QuotationItemOfferDocumentController. A
             brand-new offer autosaves itself first (see uploadOfferDocument
             in _form.blade.php) so there's always a real id to attach to. --}}
        <template x-if="offer.offer_method === 'document'">
            <div class="p-3.5 bg-red-50/40 border border-red-200 rounded-xl space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-1.5">
                    <span class="text-xs font-bold text-red-900 flex items-center gap-1.5 uppercase tracking-wide">
                        <i class="fa-solid fa-file-arrow-up text-red-600"></i> Document Quotation Files
                    </span>
                    <span class="text-[10px] text-gray-400">PDF, DOC, DOCX, XLS, XLSX, CSV — max 10MB per file</span>
                </div>

                <label class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-white border border-red-300 text-red-700 hover:bg-red-50 cursor-pointer transition-colors shadow-2xs"
                       :class="offer._uploadPreparing ? 'opacity-60 pointer-events-none' : ''">
                    <i class="fa-solid" :class="offer._uploadPreparing ? 'fa-circle-notch fa-spin' : 'fa-plus'"></i>
                    <span x-text="offer._uploadPreparing ? 'Uploading…' : 'Upload Document'"></span>
                    <input type="file" class="hidden" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv"
                           @change="handleOfferDocumentSelect(item, offer, $event.target.files); $event.target.value = ''">
                </label>

                <template x-if="offer._documents && offer._documents.length > 0">
                    <div class="space-y-1.5">
                        <template x-for="doc in offer._documents" :key="doc.id">
                            <div class="flex items-center justify-between gap-2 bg-white rounded-lg border border-gray-200 px-3 py-2 shadow-2xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i class="fa-solid fa-file-lines text-red-500 text-sm shrink-0"></i>
                                    <div class="min-w-0">
                                        <a :href="doc.url" target="_blank" rel="noopener noreferrer"
                                           class="block text-xs font-medium text-gray-800 hover:text-indigo-600 hover:underline truncate" x-text="doc.name"></a>
                                        <span class="text-[10px] text-gray-400" x-text="doc.size"></span>
                                    </div>
                                </div>
                                <button type="button" @click="deleteOfferDocument(item, offer, doc.id)"
                                        class="text-red-500 hover:text-red-700 text-xs font-semibold shrink-0">
                                    Remove
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!offer._documents || offer._documents.length === 0">
                    <p class="text-xs text-gray-400 italic">No files uploaded yet.</p>
                </template>
            </div>
        </template>

        {{-- MARKETPLACE OFFER: Product Specifications — the listing's own
             structured category attributes (_attrGroups, loaded at selection
             time from the listing's category, alongside the raw values in
             _attribute_values), editable per offer here and persisted
             relationally via quotation_item_attribute_values through the
             same syncOfferAttributeValues() path Custom/Copy-Spec offers
             already use — never duplicated into the specifications JSON
             below, which stays free-form-only (see getOfferSpecsPayload in
             _form.blade.php). No category picker here, unlike Custom Offer:
             a marketplace offer's category is fixed to the listing's own. --}}
        <template x-if="offer.offer_method === 'marketplace' && offer._attrGroups && offer._attrGroups.length > 0">
            <div class="pt-3 border-t border-gray-200 space-y-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-emerald-600 text-xs"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-800">Product Specifications</span>
                </div>
                @include('backend.supplier.procurement.quotations.partials._offer-attributes')
            </div>
        </template>

        {{-- ══════════════════════════════════════════════════════════════════════
             CUSTOM / COPY-SPEC OFFER: BUYER REQUIREMENT (read-only) vs
             SUPPLIER OFFER SPECIFICATION (editable) — two independent sides.
             Shown for 'copy_spec' too, since that offer method's whole point
             is a pre-filled copy of the buyer's category/attributes that the
             supplier then edits — it needs the same editable structured
             fields 'custom' gets, not just the free-form rows below. Right
             side uses the same category tree / attribute-loading logic as
             the buyer's own "Add Custom Product" flow (categoryOptions +
             categories/{id}/attributes), scoped to this offer, and written
             into quotation_item_offers.specifications (see
             getOfferSpecsPayload in _form.blade.php) since offers have no
             relational attribute-value table of their own — buyer data
             (rfq_items/its attribute-value tables) is never touched or mixed
             in, only read for display and as the optional copy source below.
             ══════════════════════════════════════════════════════════════════════ --}}
        <template x-if="offer.offer_method === 'custom' || offer.offer_method === 'copy_spec'">
            <div class="pt-3 border-t border-gray-200 grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- LEFT: Buyer Requirement — read-only, straight from the
                     same rfqItemsById lookup _rfq-item-panel.blade.php uses. --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-800">Buyer Requirement</span>
                        <span class="text-[10px] font-normal text-gray-400">(read-only)</span>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs space-y-2.5">
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Buyer Category</span>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="(catName, cIdx) in (rfqItemsById[item.rfq_item_id]?.category_names?.length ? rfqItemsById[item.rfq_item_id]?.category_names : [rfqItemsById[item.rfq_item_id]?.category_name || 'General Category'])" :key="cIdx">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded bg-indigo-50 text-indigo-800 border border-indigo-200" x-text="catName"></span>
                                </template>
                            </div>
                        </div>

                        <template x-if="rfqItemsById[item.rfq_item_id]?.attributes && rfqItemsById[item.rfq_item_id]?.attributes.length > 0">
                            <div class="pt-2 border-t border-slate-200/70">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Buyer Attributes</span>
                                <div class="space-y-1">
                                    <template x-for="(attr, aIdx) in rfqItemsById[item.rfq_item_id]?.attributes" :key="aIdx">
                                        <div class="flex items-center justify-between gap-2 bg-white rounded px-2 py-1 border border-slate-100">
                                            <span class="text-gray-500" x-text="attr.name"></span>
                                            <span class="font-semibold text-gray-900 text-right" x-text="attr.value"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="rfqItemsById[item.rfq_item_id]?.specs && rfqItemsById[item.rfq_item_id]?.specs.length > 0">
                            <div class="pt-2 border-t border-slate-200/70">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Buyer Specifications</span>
                                <div class="space-y-1">
                                    <template x-for="(spec, sIdx) in rfqItemsById[item.rfq_item_id]?.specs" :key="sIdx">
                                        <div class="flex items-center justify-between gap-2 bg-white rounded px-2 py-1 border border-slate-100">
                                            <span class="text-gray-500" x-text="spec.name"></span>
                                            <span class="font-semibold text-gray-900 text-right" x-text="spec.value"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="(!rfqItemsById[item.rfq_item_id]?.attributes || rfqItemsById[item.rfq_item_id]?.attributes.length === 0) && (!rfqItemsById[item.rfq_item_id]?.specs || rfqItemsById[item.rfq_item_id]?.specs.length === 0)">
                            <p class="text-gray-400 italic pt-1">Buyer did not provide structured specifications for this item.</p>
                        </template>
                    </div>
                </div>

                {{-- RIGHT: Supplier Offer Specification — independent category
                     + attributes, with an optional one-click copy from the
                     buyer's side above. --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-pen-ruler text-indigo-600 text-xs"></i>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-800">Supplier Offer Specification</span>
                    </div>

                    <label class="flex items-center gap-2 text-[11px] font-medium text-indigo-900 bg-indigo-50 border border-indigo-200 rounded-lg px-3 py-2 cursor-pointer">
                        <input type="checkbox"
                               x-model="offer._copyBuyerSpecs"
                               @change="toggleCopyBuyerSpecs(offer, item)"
                               class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Copy buyer specifications to my offer</span>
                    </label>

                    {{-- Searchable category picker — same hierarchy-path tree the
                         buyer's "Add Custom Product" category picker uses. --}}
                    <div class="relative" @click.away="offer._categoryPickerOpen = false">
                        {{-- Selected-state chip --}}
                        <div x-show="offer.category_id && !offer._categoryPickerOpen" x-cloak
                             @click="reopenCategoryPickerForOffer(offer, $event)"
                             class="flex items-center justify-between gap-2 w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white cursor-pointer hover:border-indigo-300 transition-colors">
                            <span class="flex items-center gap-1.5 min-w-0">
                                <i class="fa-solid fa-folder text-indigo-400 text-[11px] shrink-0"></i>
                                <span class="truncate font-medium text-gray-800" x-text="getCategoryNodePath(offer.category_id) || offer._category_name"></span>
                            </span>
                            <button type="button" @click.stop="clearCategoryForOffer(offer)" class="text-gray-400 hover:text-red-500 shrink-0" title="Clear category">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        {{-- Search box + results dropdown --}}
                        <div x-show="!offer.category_id || offer._categoryPickerOpen" class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400 text-xs">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text"
                                   x-model="offer._categorySearch"
                                   @focus="offer._categoryPickerOpen = true"
                                   placeholder="Select your category (e.g. Computer > Laptop)..."
                                   class="focus-accent w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">

                            <div x-show="offer._categoryPickerOpen" x-cloak
                                 class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto overscroll-contain p-1 [scrollbar-gutter:stable]">
                                <div class="space-y-0.5">
                                    <template x-for="node in filteredCategoryNodesForOffer(offer).slice(0, 50)" :key="node.id">
                                        <button type="button" @click="selectCategoryNodeForOffer(offer, node)"
                                                class="w-full flex items-center justify-between gap-2 py-2.5 pr-3 text-left text-xs hover:bg-indigo-50 rounded-md transition-colors"
                                                :class="offer.category_id == node.id ? 'bg-indigo-50/70' : ''"
                                                :style="'padding-left:' + (10 + node.depth * 14) + 'px'"
                                                :data-node-id="node.id"
                                                :title="node.path">
                                            <span class="flex items-center gap-2 min-w-0">
                                                <i class="fa-solid text-[10px] shrink-0" :class="node.depth > 0 ? 'fa-turn-up fa-rotate-90 text-gray-300' : 'fa-folder text-indigo-400'"></i>
                                                <span class="min-w-0">
                                                    <span class="block truncate font-medium text-gray-800" x-text="node.name"></span>
                                                    <span x-show="node.depth > 0" class="block truncate text-[10px] text-gray-400" x-text="node.path"></span>
                                                </span>
                                            </span>
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md shrink-0"
                                                  :class="node.attributes_count > 0 ? 'text-indigo-700 bg-indigo-50 border border-indigo-100' : 'text-gray-400 bg-gray-50 border border-gray-200'"
                                                  x-text="node.attributes_count + ' specs'"></span>
                                        </button>
                                    </template>
                                </div>
                                <p x-show="filteredCategoryNodesForOffer(offer).length === 0" class="text-xs text-gray-400 text-center py-4">
                                    No categories match "<span x-text="offer._categorySearch"></span>".
                                </p>
                                <p x-show="filteredCategoryNodesForOffer(offer).length > 50" class="text-[10px] text-gray-400 text-center py-1.5 mt-1 border-t border-gray-100">
                                    Showing first 50 — keep typing to narrow it down.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamically loaded attribute fields for the selected category --}}
                    @include('backend.supplier.procurement.quotations.partials._offer-attributes')

                    {{-- Additional free-form specifications, part of the
                         supplier's own side for Custom offers. --}}
                    <div class="pt-2 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-gray-600">
                                Additional Specifications
                                <span class="font-normal text-gray-400" x-text="'(' + (offer._custom_specs?.length || 0) + ')'"></span>
                            </span>
                            <button type="button" @click="addCustomSpec(offer)"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">
                                <i class="fa-solid fa-plus text-[9px]"></i>
                                <span>Add</span>
                            </button>
                        </div>
                        <template x-if="offer._custom_specs && offer._custom_specs.length > 0">
                            <div class="space-y-1.5">
                                <template x-for="(spec, sIdx) in offer._custom_specs" :key="sIdx">
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" x-model="spec.name" placeholder="Name" class="focus-accent w-2/5 text-xs rounded-lg border border-gray-300 px-2.5 py-1.5 bg-white">
                                        <input type="text" x-model="spec.value" placeholder="Value" class="focus-accent flex-1 text-xs rounded-lg border border-gray-300 px-2.5 py-1.5 bg-white">
                                        <button type="button" @click="removeCustomSpec(offer, sIdx)" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg shrink-0">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- ══════════════════════════════════════════════════════════════════════
             ADDITIONAL SPECIFICATIONS (free-form custom rows — every OTHER
             offer method; Custom/Copy-Spec offers get their own copy above,
             alongside their category attributes on the right)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="pt-3 border-t border-gray-200 space-y-3" x-show="offer.offer_method !== 'custom' && offer.offer_method !== 'copy_spec'">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-sliders text-indigo-600 text-xs"></i>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-800">Specifications &amp; Criteria</span>
                <span class="text-[11px] font-normal text-gray-400"
                      x-text="'(' + (offer._custom_specs?.length || 0) + ' custom attributes)'"></span>
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

            <button type="button" @click="addCustomSpec(offer)"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors shadow-2xs">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Add Specification</span>
            </button>
        </div>

    </div>
</div>
