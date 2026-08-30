{{--
    Buyer-requested vs supplier-offered structured specifications for one
    quotation item, driven by the buyer's RFQ item category (rfqItemsById
    lookup, built server-side in _form.blade.php) — never by the supplier's
    own listing category, so both sides always compare against the same
    taxonomy (spec §38-39). Included inside _item.blade.php's x-for scope,
    so `item` and `index` are in context. Adapted from the buyer module's
    own _item-attributes.blade.php input-type switch.
--}}
<div x-show="item._attrLoading" class="py-6 text-center text-xs text-gray-500">
    <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 mr-1"></i> Loading specifications…
</div>

<template x-if="!item._attrLoading && item._attrGroups.length > 0">
    <div class="space-y-4 mt-3">
        <template x-for="group in item._attrGroups" :key="group.group_id">
            <div class="bg-gray-50/60 rounded-xl border border-gray-200 overflow-hidden">
                <div class="bg-gray-100/70 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-800" x-text="group.group_name"></span>
                    <span class="text-[10px] text-gray-400" x-text="group.attributes.length + ' fields'"></span>
                </div>
                <div class="divide-y divide-gray-100">
                    <template x-for="attr in group.attributes" :key="attr.id">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3 items-start">
                            <div>
                                <p class="text-[11px] font-medium text-gray-500 mb-1">
                                    <span x-text="attr.name"></span>
                                    <span x-show="attr.unit_symbol" class="text-gray-400" x-text="'(' + attr.unit_symbol + ')'"></span>
                                    — Buyer Requested
                                </p>
                                <p class="text-xs font-semibold text-gray-800 bg-white border border-gray-200 rounded-lg px-3 py-2"
                                   x-text="(rfqItemsById[item.rfq_item_id]?.attributes_by_id?.[attr.id]) ?? 'Not specified'"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-medium text-indigo-600 mb-1 flex items-center gap-1">
                                    <span x-text="attr.name"></span> — Your Offer
                                    <span x-show="attr.is_required" class="text-red-500 font-bold">*</span>
                                </p>

                                {{-- TEXT --}}
                                <template x-if="attr.input_type === 'text'">
                                    <input type="text"
                                           :name="'items[' + index + '][attribute_values][' + attr.id + '][value_text]'"
                                           :placeholder="attr.placeholder || ('Enter ' + attr.name.toLowerCase())"
                                           x-model="getAttrVal(item, attr.id).value_text"
                                           class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                </template>

                                {{-- TEXTAREA --}}
                                <template x-if="attr.input_type === 'textarea'">
                                    <textarea :name="'items[' + index + '][attribute_values][' + attr.id + '][value_text]'"
                                              :placeholder="attr.placeholder || ('Enter ' + attr.name.toLowerCase())"
                                              rows="2"
                                              x-model="getAttrVal(item, attr.id).value_text"
                                              class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white"></textarea>
                                </template>

                                {{-- NUMBER --}}
                                <template x-if="attr.input_type === 'number'">
                                    <input type="number" step="any"
                                           :name="'items[' + index + '][attribute_values][' + attr.id + '][value_number]'"
                                           :placeholder="attr.placeholder || '0'"
                                           x-model="getAttrVal(item, attr.id).value_number"
                                           class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                </template>

                                {{-- SELECT --}}
                                <template x-if="attr.input_type === 'select'">
                                    <div class="space-y-1">
                                        <select :name="'items[' + index + '][attribute_values][' + attr.id + '][attribute_value_id]'"
                                                x-model="getAttrVal(item, attr.id).attribute_value_id"
                                                class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                            <option value="">Select option</option>
                                            <template x-for="opt in attr.values" :key="opt.id">
                                                <option :value="opt.id" x-text="opt.value"></option>
                                            </template>
                                            <template x-if="attr.allow_custom_value">
                                                <option value="__other__">Other</option>
                                            </template>
                                        </select>
                                        <input type="text" x-show="isOtherSelected(item, attr.id)"
                                               :name="'items[' + index + '][attribute_values][' + attr.id + '][custom_value]'"
                                               placeholder="Please specify..."
                                               x-model="getAttrVal(item, attr.id).custom_value"
                                               class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    </div>
                                </template>

                                {{-- MULTI SELECT / CHECKBOXES --}}
                                <template x-if="attr.input_type === 'multi_select'">
                                    <div class="p-2.5 bg-white rounded-lg border border-gray-200 max-h-32 overflow-y-auto space-y-1.5">
                                        <template x-if="attr.values.length > 0">
                                            <div>
                                                <template x-for="opt in attr.values" :key="opt.id">
                                                    <label class="inline-flex items-center gap-1.5 mr-2 mb-1 text-[11px] text-gray-700 cursor-pointer bg-gray-50 px-2 py-1 rounded-md border border-gray-200">
                                                        <input type="checkbox"
                                                               :name="'items[' + index + '][attribute_values][' + attr.id + '][value_json][]'"
                                                               :value="opt.value"
                                                               :checked="isMultiSelected(item, attr.id, opt.value)"
                                                               @change="toggleMultiSelect(item, attr.id, opt.value)"
                                                               class="rounded text-indigo-600 focus:ring-indigo-500">
                                                        <span x-text="opt.value"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="attr.allow_custom_value">
                                            <input type="text"
                                                   :name="'items[' + index + '][attribute_values][' + attr.id + '][custom_value]'"
                                                   placeholder="Other (please specify)..."
                                                   x-model="getAttrVal(item, attr.id).custom_value"
                                                   class="w-full text-xs rounded-lg border border-gray-300 px-2 py-1.5 bg-white mt-1">
                                        </template>
                                    </div>
                                </template>

                                {{-- BOOLEAN --}}
                                <template x-if="attr.input_type === 'boolean'">
                                    <div class="flex items-center gap-3 py-1.5">
                                        <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                            <input type="radio" :name="'items[' + index + '][attribute_values][' + attr.id + '][value_boolean]'" value="1"
                                                   :checked="getAttrVal(item, attr.id).value_boolean == 1"
                                                   @change="getAttrVal(item, attr.id).value_boolean = 1">
                                            <span>Yes</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                            <input type="radio" :name="'items[' + index + '][attribute_values][' + attr.id + '][value_boolean]'" value="0"
                                                   :checked="getAttrVal(item, attr.id).value_boolean == 0"
                                                   @change="getAttrVal(item, attr.id).value_boolean = 0">
                                            <span>No</span>
                                        </label>
                                    </div>
                                </template>

                                {{-- DATE --}}
                                <template x-if="attr.input_type === 'date'">
                                    <input type="date"
                                           :name="'items[' + index + '][attribute_values][' + attr.id + '][value_date]'"
                                           x-model="getAttrVal(item, attr.id).value_date"
                                           class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                </template>

                                {{-- COLOR --}}
                                <template x-if="attr.input_type === 'color'">
                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="opt in attr.values" :key="opt.id">
                                                <label class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border text-[11px] cursor-pointer"
                                                       :class="getAttrVal(item, attr.id).attribute_value_id == opt.id ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-500 font-semibold' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                                    <input type="radio" :name="'items[' + index + '][attribute_values][' + attr.id + '][attribute_value_id]'" :value="opt.id"
                                                           x-model="getAttrVal(item, attr.id).attribute_value_id" class="sr-only">
                                                    <span x-show="opt.color_hex" class="w-3 h-3 rounded-full border border-gray-300" :style="'background-color:' + opt.color_hex"></span>
                                                    <span x-text="opt.value"></span>
                                                </label>
                                            </template>
                                            <template x-if="attr.allow_custom_value">
                                                <label class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border text-[11px] cursor-pointer"
                                                       :class="isOtherSelected(item, attr.id) ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-500 font-semibold' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                                    <input type="radio" :name="'items[' + index + '][attribute_values][' + attr.id + '][attribute_value_id]'" value="__other__"
                                                           x-model="getAttrVal(item, attr.id).attribute_value_id" class="sr-only">
                                                    <span>Other</span>
                                                </label>
                                            </template>
                                        </div>
                                        <input type="text" x-show="isOtherSelected(item, attr.id)"
                                               :name="'items[' + index + '][attribute_values][' + attr.id + '][custom_value]'"
                                               placeholder="Custom color or hex..."
                                               x-model="getAttrVal(item, attr.id).custom_value"
                                               class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</template>

<p x-show="!item._attrLoading && item.rfq_item_id && item._attrGroups.length === 0" class="text-xs text-gray-400 mt-2">
    No standard specifications were requested for this item.
</p>
