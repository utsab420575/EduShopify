{{--
    Dynamic category-attribute inputs for one offer (Marketplace, Custom, or
    Copy-Spec) — adapted from buyer/procurement/rfqs/partials/_item-attributes.blade.php,
    scoped to `offer` instead of `item`. Included inside _offer-card.blade.php's
    x-for scope, so `item`/`offer`/`offerIndex` are already in context.

    These inputs write into offer._attribute_values, which the hidden
    [attribute_values] field on this offer card (built from
    offer._attribute_values directly) submits separately from
    [specifications] — synced server-side into quotation_item_attribute_values
    via QuotationService::syncOfferAttributeValues(), one relational row per
    attribute per offer, NOT flattened into quotation_item_offers.specifications
    (that JSON stays free-form-only, see getOfferSpecsPayload in
    _form.blade.php). No :name attributes needed here as a result — the
    hidden [attribute_values] field is what actually submits this data.
--}}
<div x-show="offer._attrLoading" class="py-4 text-center text-xs text-gray-500">
    <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 mr-1"></i> Loading specifications…
</div>

<template x-if="!offer._attrLoading && offer._attrGroups && offer._attrGroups.length > 0">
    <div class="space-y-3">
        <template x-for="group in offer._attrGroups" :key="group.group_id">
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-3 py-1.5 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-gray-700" x-text="group.group_name"></span>
                    <span class="text-[10px] text-gray-400" x-text="group.attributes.length + ' fields'"></span>
                </div>
                <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-2.5">
                    <template x-for="attr in group.attributes" :key="attr.id">
                        <div :class="attr.input_type === 'textarea' ? 'md:col-span-2' : ''" class="space-y-1">
                            <label class="text-[11px] font-medium text-gray-600 flex items-center gap-1">
                                <span x-text="attr.name"></span>
                                <span x-show="attr.is_required" class="text-red-500 font-bold">*</span>
                                <span x-show="attr.unit_symbol" class="text-[10px] text-gray-400" x-text="'(' + attr.unit_symbol + ')'"></span>
                            </label>

                            {{-- TEXT --}}
                            <template x-if="attr.input_type === 'text'">
                                <input type="text"
                                       :placeholder="attr.placeholder || ('Enter ' + attr.name.toLowerCase())"
                                       x-model="getOfferAttrVal(offer, attr.id).value_text"
                                       class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                            </template>

                            {{-- TEXTAREA --}}
                            <template x-if="attr.input_type === 'textarea'">
                                <textarea :placeholder="attr.placeholder || ('Enter ' + attr.name.toLowerCase())"
                                          rows="2"
                                          x-model="getOfferAttrVal(offer, attr.id).value_text"
                                          class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white"></textarea>
                            </template>

                            {{-- NUMBER --}}
                            <template x-if="attr.input_type === 'number'">
                                <input type="number" step="any"
                                       :placeholder="attr.placeholder || '0'"
                                       x-model="getOfferAttrVal(offer, attr.id).value_number"
                                       class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                            </template>

                            {{-- SELECT --}}
                            <template x-if="attr.input_type === 'select'">
                                <div class="space-y-1">
                                    <select x-model="getOfferAttrVal(offer, attr.id).attribute_value_id"
                                            x-init="$nextTick(() => { $el.value = getOfferAttrVal(offer, attr.id).attribute_value_id ?? '' })"
                                            class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                                        <option value="">Select option</option>
                                        <template x-for="opt in attr.values" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.value"></option>
                                        </template>
                                        <template x-if="attr.allow_custom_value">
                                            <option value="__other__">Other</option>
                                        </template>
                                    </select>
                                    <input type="text" x-show="isOfferOtherSelected(offer, attr.id)"
                                           placeholder="Please specify..."
                                           x-model="getOfferAttrVal(offer, attr.id).custom_value"
                                           class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                                </div>
                            </template>

                            {{-- MULTI SELECT / CHECKBOXES --}}
                            <template x-if="attr.input_type === 'multi_select'">
                                <div class="p-2 bg-white rounded-lg border border-gray-200 max-h-28 overflow-y-auto space-y-1">
                                    <template x-if="attr.values.length > 0">
                                        <div>
                                            <template x-for="opt in attr.values" :key="opt.id">
                                                <label class="inline-flex items-center gap-1.5 mr-2 mb-1 text-[11px] text-gray-700 cursor-pointer bg-gray-50 px-2 py-1 rounded-md border border-gray-200">
                                                    <input type="checkbox"
                                                           :value="opt.value"
                                                           :checked="isOfferMultiSelected(offer, attr.id, opt.value)"
                                                           @change="toggleOfferMultiSelect(offer, attr.id, opt.value)"
                                                           class="rounded text-indigo-600 focus:ring-indigo-500">
                                                    <span x-text="opt.value"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="attr.allow_custom_value">
                                        <input type="text"
                                               placeholder="Other (please specify)..."
                                               x-model="getOfferAttrVal(offer, attr.id).custom_value"
                                               class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-2 py-1 bg-white mt-1">
                                    </template>
                                </div>
                            </template>

                            {{-- BOOLEAN --}}
                            <template x-if="attr.input_type === 'boolean'">
                                <div class="flex items-center gap-3 py-1 px-1">
                                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                        <input type="radio" :name="'offer_attr_' + offer._localKey + '_' + attr.id" value="1"
                                               :checked="getOfferAttrVal(offer, attr.id).value_boolean == 1"
                                               @change="getOfferAttrVal(offer, attr.id).value_boolean = 1">
                                        <span>Yes</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                        <input type="radio" :name="'offer_attr_' + offer._localKey + '_' + attr.id" value="0"
                                               :checked="getOfferAttrVal(offer, attr.id).value_boolean == 0"
                                               @change="getOfferAttrVal(offer, attr.id).value_boolean = 0">
                                        <span>No</span>
                                    </label>
                                </div>
                            </template>

                            {{-- DATE --}}
                            <template x-if="attr.input_type === 'date'">
                                <input type="text"
                                       x-model="getOfferAttrVal(offer, attr.id).value_date"
                                       x-init="typeof flatpickr !== 'undefined' && flatpickr($el, getOfferAttrVal(offer, attr.id).value_date ? { dateFormat: 'Y-m-d', defaultDate: getOfferAttrVal(offer, attr.id).value_date } : { dateFormat: 'Y-m-d' })"
                                       autocomplete="off" placeholder="Select date"
                                       class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                            </template>

                            {{-- COLOR --}}
                            <template x-if="attr.input_type === 'color'">
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap gap-1.5 p-1">
                                        <template x-for="opt in attr.values" :key="opt.id">
                                            <label class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border text-[11px] cursor-pointer"
                                                   :class="getOfferAttrVal(offer, attr.id).attribute_value_id == opt.id ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-500 font-semibold' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                                <input type="radio" :name="'offer_attr_' + offer._localKey + '_' + attr.id" :value="opt.id"
                                                       x-model="getOfferAttrVal(offer, attr.id).attribute_value_id" class="sr-only">
                                                <span x-show="opt.color_hex" class="w-3 h-3 rounded-full border border-gray-300" :style="'background-color:' + opt.color_hex"></span>
                                                <span x-text="opt.value"></span>
                                            </label>
                                        </template>
                                        <template x-if="attr.allow_custom_value">
                                            <label class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border text-[11px] cursor-pointer"
                                                   :class="isOfferOtherSelected(offer, attr.id) ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-500 font-semibold' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                                <input type="radio" :name="'offer_attr_' + offer._localKey + '_' + attr.id" value="__other__"
                                                       x-model="getOfferAttrVal(offer, attr.id).attribute_value_id" class="sr-only">
                                                <span>Other</span>
                                            </label>
                                        </template>
                                    </div>
                                    <input type="text" x-show="isOfferOtherSelected(offer, attr.id)"
                                           placeholder="Custom color or hex..."
                                           x-model="getOfferAttrVal(offer, attr.id).custom_value"
                                           class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</template>

<p x-show="!offer._attrLoading && offer.category_id && (!offer._attrGroups || offer._attrGroups.length === 0)" class="text-xs text-gray-400">
    No standard specifications configured for this category — add custom specifications below.
</p>
