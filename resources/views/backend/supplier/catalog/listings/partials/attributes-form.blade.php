{{-- Dynamic Category Attributes Component --}}
@props([
    'initialCategoryId' => null,
    'initialValues' => [],
    'endpointUrl' => route('supplier.catalog.listings.category.attributes', ':id'),
])

<div x-data="categoryAttributesManager({
    initialCategoryId: {{ json_encode($initialCategoryId) }},
    initialValues: {{ json_encode($initialValues) }},
    endpointUrl: '{{ $endpointUrl }}'
})"
     x-init="init()"
     class="space-y-6">

    {{-- Loading Indicator --}}
    <div x-show="isLoading" x-cloak class="p-6 bg-white rounded-xl border border-gray-200 text-center">
        <div class="inline-flex items-center gap-2 text-sm text-indigo-600 font-medium">
            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading category specifications...</span>
        </div>
    </div>

    {{-- Empty State: No category chosen --}}
    <div x-show="!isLoading && (!selectedCategoryId || groups.length === 0)" x-cloak>
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-8 text-center">
            <div class="w-12 h-12 bg-white rounded-full border border-gray-200 flex items-center justify-center mx-auto mb-3 text-gray-400">
                <i class="fa-solid fa-layer-group text-lg"></i>
            </div>
            <h4 class="text-sm font-semibold text-gray-900 mb-1" x-text="selectedCategoryId ? 'No custom specifications for this category' : 'Select a Category First'"></h4>
            <p class="text-xs text-gray-500 max-w-md mx-auto" x-text="selectedCategoryId ? 'This category does not have assigned attributes yet. You can continue creating your listing.' : 'Choose a primary category above to load and fill product specifications, technical details, and variant options.'"></p>
        </div>
    </div>

    {{-- Grouped Attribute Cards --}}
    <template x-if="!isLoading && groups.length > 0">
        <div class="space-y-6">
            <template x-for="(group, groupIndex) in groups" :key="group.group_id">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    {{-- Group Header --}}
                    <div class="bg-gray-50/80 px-5 py-3.5 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-xs text-indigo-500"></i>
                            <h3 class="text-sm font-semibold text-gray-900" x-text="group.group_name"></h3>
                        </div>
                        <span class="text-[11px] font-medium text-gray-400" x-text="group.attributes.length + ' fields'"></span>
                    </div>

                    {{-- Group Attributes Grid --}}
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="attr in group.attributes" :key="attr.id">
                            <div :class="attr.input_type === 'textarea' ? 'md:col-span-2' : ''" class="space-y-1.5">
                                {{-- Attribute Label & Badges --}}
                                <div class="flex items-center justify-between text-xs">
                                    <label :for="'attr_' + attr.id" class="font-medium text-gray-700 flex items-center gap-1">
                                        <span x-text="attr.name"></span>
                                        <span x-show="attr.is_required" class="text-red-500 font-bold">*</span>
                                    </label>
                                    <div class="flex items-center gap-1.5">
                                        <span x-show="attr.is_variant" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            Variant
                                        </span>
                                        <span x-show="attr.is_filterable" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            Filter
                                        </span>
                                        <span x-show="attr.unit_symbol" class="text-[11px] text-gray-400" x-text="'(' + attr.unit_symbol + ')'"></span>
                                    </div>
                                </div>

                                {{-- Input Types --}}
                                <div>
                                    {{-- TEXT --}}
                                    <template x-if="attr.input_type === 'text'">
                                        <div class="relative rounded-lg">
                                            <input type="text"
                                                   :id="'attr_' + attr.id"
                                                   :name="'attributes[' + attr.id + '][value_text]'"
                                                   :placeholder="attr.placeholder || ('Enter ' + attr.name.toLowerCase())"
                                                   x-model="getVal(attr.id).value_text"
                                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </template>

                                    {{-- TEXTAREA --}}
                                    <template x-if="attr.input_type === 'textarea'">
                                        <textarea :id="'attr_' + attr.id"
                                                  :name="'attributes[' + attr.id + '][value_text]'"
                                                  :placeholder="attr.placeholder || ('Enter ' + attr.name.toLowerCase())"
                                                  rows="3"
                                                  x-model="getVal(attr.id).value_text"
                                                  class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                    </template>

                                    {{-- NUMBER --}}
                                    <template x-if="attr.input_type === 'number'">
                                        <div class="relative rounded-lg flex">
                                            <input type="number"
                                                   step="any"
                                                   :id="'attr_' + attr.id"
                                                   :name="'attributes[' + attr.id + '][value_number]'"
                                                   :placeholder="attr.placeholder || '0.00'"
                                                   x-model="getVal(attr.id).value_number"
                                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                                   :class="attr.unit_symbol ? 'rounded-r-none' : ''">
                                            <span x-show="attr.unit_symbol" class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-xs font-medium" x-text="attr.unit_symbol"></span>
                                        </div>
                                    </template>

                                    {{-- SELECT --}}
                                    <template x-if="attr.input_type === 'select'">
                                        <select :id="'attr_' + attr.id"
                                                :name="'attributes[' + attr.id + '][attribute_value_id]'"
                                                x-model="getVal(attr.id).attribute_value_id"
                                                class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select option</option>
                                            <template x-for="opt in attr.values" :key="opt.id">
                                                <option :value="opt.id" x-text="opt.value" :selected="getVal(attr.id).attribute_value_id == opt.id"></option>
                                            </template>
                                        </select>
                                    </template>

                                    {{-- MULTI SELECT / CHECKBOXES --}}
                                    <template x-if="attr.input_type === 'multi_select'">
                                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 max-h-40 overflow-y-auto space-y-1.5">
                                            <template x-if="attr.values.length > 0">
                                                <div>
                                                    <template x-for="opt in attr.values" :key="opt.id">
                                                        <label class="inline-flex items-center gap-2 mr-3 mb-1 text-xs text-gray-700 cursor-pointer bg-white px-2.5 py-1 rounded-md border border-gray-200 hover:border-gray-300">
                                                            <input type="checkbox"
                                                                   :name="'attributes[' + attr.id + '][value_json][]'"
                                                                   :value="opt.value"
                                                                   :checked="isMultiSelected(attr.id, opt.value)"
                                                                   @change="toggleMultiSelect(attr.id, opt.value)"
                                                                   class="rounded text-indigo-600 focus:ring-indigo-500">
                                                            <span x-text="opt.value"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="attr.values.length === 0">
                                                <input type="text"
                                                       :name="'attributes[' + attr.id + '][value_text]'"
                                                       placeholder="Enter comma separated values..."
                                                       x-model="getVal(attr.id).value_text"
                                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                            </template>
                                        </div>
                                    </template>

                                    {{-- BOOLEAN --}}
                                    <template x-if="attr.input_type === 'boolean'">
                                        <div class="flex items-center gap-4 py-1">
                                            <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                                <input type="radio"
                                                       :name="'attributes[' + attr.id + '][value_boolean]'"
                                                       value="1"
                                                       :checked="getVal(attr.id).value_boolean === true || getVal(attr.id).value_boolean === 1 || getVal(attr.id).value_boolean === '1'"
                                                       @change="getVal(attr.id).value_boolean = 1"
                                                       class="text-indigo-600 focus:ring-indigo-500">
                                                <span>Yes</span>
                                            </label>
                                            <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                                <input type="radio"
                                                       :name="'attributes[' + attr.id + '][value_boolean]'"
                                                       value="0"
                                                       :checked="getVal(attr.id).value_boolean === false || getVal(attr.id).value_boolean === 0 || getVal(attr.id).value_boolean === '0'"
                                                       @change="getVal(attr.id).value_boolean = 0"
                                                       class="text-indigo-600 focus:ring-indigo-500">
                                                <span>No</span>
                                            </label>
                                        </div>
                                    </template>

                                    {{-- DATE --}}
                                    <template x-if="attr.input_type === 'date'">
                                        <input type="date"
                                               :id="'attr_' + attr.id"
                                               :name="'attributes[' + attr.id + '][value_date]'"
                                               x-model="getVal(attr.id).value_date"
                                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    </template>

                                    {{-- COLOR --}}
                                    <template x-if="attr.input_type === 'color'">
                                        <div class="space-y-2">
                                            <template x-if="attr.values.length > 0">
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="opt in attr.values" :key="opt.id">
                                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs cursor-pointer transition-all"
                                                               :class="getVal(attr.id).attribute_value_id == opt.id ? 'border-indigo-600 bg-indigo-50/50 ring-1 ring-indigo-500 font-semibold' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                                            <input type="radio"
                                                                   :name="'attributes[' + attr.id + '][attribute_value_id]'"
                                                                   :value="opt.id"
                                                                   x-model="getVal(attr.id).attribute_value_id"
                                                                   class="sr-only">
                                                            <span x-show="opt.color_hex" class="w-3.5 h-3.5 rounded-full border border-gray-300" :style="'background-color:' + opt.color_hex"></span>
                                                            <span x-text="opt.value"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </template>
                                            <div class="flex items-center gap-2">
                                                <input type="text"
                                                       :name="'attributes[' + attr.id + '][value_text]'"
                                                       placeholder="Custom color or hex (e.g. #0055FF)"
                                                       x-model="getVal(attr.id).value_text"
                                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                            </div>
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

    {{-- Category Change Confirmation Modal --}}
    <div x-show="discardModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="discardModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500/75 transition-opacity"
                 @click="cancelCategoryChange()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="discardModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10 text-amber-600">
                            <i class="fa-solid fa-triangle-exclamation text-base"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-base font-semibold text-gray-900" id="modal-title">
                                Category Change Warning
                            </h3>
                            <div class="mt-2">
                                <p class="text-xs text-gray-500 mb-3">
                                    You are switching category. The new category does not support the following specifications you entered:
                                </p>
                                <div class="bg-amber-50 rounded-lg p-3 border border-amber-200 max-h-36 overflow-y-auto">
                                    <ul class="list-disc list-inside text-xs text-amber-800 space-y-1 font-medium">
                                        <template x-for="item in discardList" :key="item">
                                            <li x-text="item"></li>
                                        </template>
                                    </ul>
                                </div>
                                <p class="text-xs text-gray-500 mt-3">
                                    Matching specifications will be preserved. Do you want to proceed and discard invalid specifications?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                    <button type="button"
                            @click="confirmCategoryChange()"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-xs px-4 py-2 bg-amber-600 text-xs font-semibold text-white hover:bg-amber-700 focus:outline-none sm:w-auto">
                        Yes, Change Category
                    </button>
                    <button type="button"
                            @click="cancelCategoryChange()"
                            class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-xs px-4 py-2 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function categoryAttributesManager(config) {
    return {
        selectedCategoryId: config.initialCategoryId || null,
        previousCategoryId: config.initialCategoryId || null,
        pendingCategoryId: null,
        enteredValues: config.initialValues || {},
        groups: [],
        rawAttributes: [],
        isLoading: false,
        discardModalOpen: false,
        discardList: [],
        endpointUrl: config.endpointUrl,

        init() {
            // Listen for category changes from parent select element
            const catSelect = document.querySelector('select[name="main_category_id"]');
            if (catSelect) {
                this.selectedCategoryId = catSelect.value;
                this.previousCategoryId = catSelect.value;

                catSelect.addEventListener('change', (e) => {
                    this.handleCategorySelectChange(e.target.value);
                });
            }

            if (this.selectedCategoryId) {
                this.fetchAttributes(this.selectedCategoryId, true);
            }
        },

        getVal(attrId) {
            if (!this.enteredValues[attrId]) {
                this.enteredValues[attrId] = {
                    attribute_value_id: null,
                    value_text: null,
                    value_number: null,
                    value_boolean: null,
                    value_date: null,
                    value_json: []
                };
            } else {
                const item = this.enteredValues[attrId];
                if ((item.value_text === null || item.value_text === '' || item.value_text === undefined) && Array.isArray(item.value_json) && item.value_json.length > 0) {
                    item.value_text = item.value_json.join(', ');
                }
                if ((!item.value_json || item.value_json.length === 0) && item.value_text) {
                    item.value_json = item.value_text.split(',').map(s => s.trim()).filter(Boolean);
                }
            }
            return this.enteredValues[attrId];
        },

        isMultiSelected(attrId, value) {
            const val = this.getVal(attrId);
            if (Array.isArray(val.value_json)) {
                return val.value_json.includes(value);
            }
            if (typeof val.value_text === 'string' && val.value_text) {
                return val.value_text.split(',').map(s => s.trim()).includes(value);
            }
            return false;
        },

        toggleMultiSelect(attrId, value) {
            const val = this.getVal(attrId);
            if (!Array.isArray(val.value_json)) {
                val.value_json = val.value_text ? val.value_text.split(',').map(s => s.trim()).filter(Boolean) : [];
            }
            const idx = val.value_json.indexOf(value);
            if (idx > -1) {
                val.value_json.splice(idx, 1);
            } else {
                val.value_json.push(value);
            }
            val.value_text = val.value_json.join(', ');
        },

        async handleCategorySelectChange(newCatId) {
            if (!newCatId) {
                this.selectedCategoryId = null;
                this.previousCategoryId = null;
                this.groups = [];
                this.rawAttributes = [];
                return;
            }

            // Check if user has entered any values in currently active attributes
            const enteredAttrIds = Object.keys(this.enteredValues).filter(id => {
                const v = this.enteredValues[id];
                return (v.value_text && v.value_text.trim() !== '') ||
                       (v.value_number !== null && v.value_number !== '') ||
                       (v.value_boolean !== null) ||
                       (v.value_date) ||
                       (Array.isArray(v.value_json) && v.value_json.length > 0) ||
                       (v.attribute_value_id);
            });

            if (enteredAttrIds.length === 0 || !this.previousCategoryId) {
                this.previousCategoryId = newCatId;
                this.selectedCategoryId = newCatId;
                await this.fetchAttributes(newCatId);
                return;
            }

            // Fetch new attributes to compare
            this.isLoading = true;
            try {
                const url = this.endpointUrl.replace(':id', newCatId);
                const res = await fetch(url);
                const data = await res.json();

                const newAttrIds = [];
                (data.groups || []).forEach(g => {
                    (g.attributes || []).forEach(a => newAttrIds.push(a.id));
                });

                // Find discard list
                const unmatchingAttrIds = enteredAttrIds.filter(id => !newAttrIds.includes(parseInt(id)));
                
                if (unmatchingAttrIds.length > 0) {
                    this.discardList = [];
                    unmatchingAttrIds.forEach(id => {
                        const existingAttr = this.rawAttributes.find(a => a.id === parseInt(id));
                        this.discardList.push(existingAttr ? existingAttr.name : ('Attribute #' + id));
                    });

                    this.pendingCategoryId = newCatId;
                    this.discardModalOpen = true;
                    this.isLoading = false;
                } else {
                    // No loss, apply directly
                    this.previousCategoryId = newCatId;
                    this.selectedCategoryId = newCatId;
                    this.groups = data.groups || [];
                    this.rawAttributes = [];
                    this.groups.forEach(g => this.rawAttributes.push(...g.attributes));
                    this.isLoading = false;
                }
            } catch (err) {
                console.error('Failed to compare category attributes:', err);
                this.isLoading = false;
            }
        },

        confirmCategoryChange() {
            this.discardModalOpen = false;
            const newCatId = this.pendingCategoryId;
            this.previousCategoryId = newCatId;
            this.selectedCategoryId = newCatId;
            this.pendingCategoryId = null;

            // Fetch and apply
            this.fetchAttributes(newCatId);
        },

        cancelCategoryChange() {
            this.discardModalOpen = false;
            this.pendingCategoryId = null;
            // Revert select in DOM
            const catSelect = document.querySelector('select[name="main_category_id"]');
            if (catSelect) {
                catSelect.value = this.previousCategoryId;
            }
        },

        async fetchAttributes(categoryId, isInitial = false) {
            if (!categoryId) return;
            this.isLoading = true;

            try {
                const url = this.endpointUrl.replace(':id', categoryId);
                const res = await fetch(url);
                const data = await res.json();

                this.groups = data.groups || [];
                this.rawAttributes = [];
                this.groups.forEach(g => this.rawAttributes.push(...g.attributes));
            } catch (err) {
                console.error('Failed to fetch category attributes:', err);
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>
