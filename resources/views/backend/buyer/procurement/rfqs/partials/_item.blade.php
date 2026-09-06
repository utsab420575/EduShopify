{{--
    One RFQ item card — 4/8 split layout redesign supporting 3 scenarios:
    1. Initial marketplace item (comes from ?listing=12): Left panel is pure read-only listing details with NO inputs or search box; Right panel has quantity + collapsed specs with custom attribute add/delete.
    2. Additional marketplace item (buyer clicked 'Add from Marketplace'): Left panel has marketplace search/filter + editable fields; Right panel has quantity + open-by-default specs with custom attribute add/delete.
    3. Custom item (buyer clicked 'Add Custom Item'): Left panel has Type, Category, Item Name, Description (NO marketplace search box); Right panel has quantity + open-by-default specs with "Add Specification" button.
--}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4 last:mb-0"
     x-data="{ editingLeft: false }">

    {{-- Card header bar --}}
    <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center gap-2.5">
            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[11px] font-bold"
                  x-text="index + 1"></span>
            <span class="text-xs font-semibold text-gray-700">Item <span x-text="index + 1"></span></span>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border flex items-center gap-1"
                  :class="item._mode === 'custom'
                      ? 'bg-gray-100 text-gray-700 border-gray-200'
                      : 'bg-emerald-50 text-emerald-700 border-emerald-200'">
                <i class="fa-solid text-[9px]" :class="item._mode === 'custom' ? 'fa-pen-to-square text-gray-400' : 'fa-store text-emerald-600'"></i>
                <span x-text="sourceTypeLabel(item)"></span>
            </span>
        </div>
        <button type="button" x-show="items.length > 1" @click="removeItem(index)" title="Remove this item"
                class="inline-flex items-center gap-1 text-xs font-medium text-red-500 hover:text-red-700 transition-colors">
            <i class="fa-solid fa-trash-can text-[11px]"></i> Remove
        </button>
    </div>

    {{-- Hidden ID inputs --}}
    <input type="hidden" :name="'items['+index+'][id]'" :value="item.id ?? ''">

    {{-- Body: 4 / 8 split --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">

        {{-- ══════════════════════════════════════════════════════════════════════
             LEFT PANEL (col-4)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-4 bg-gray-50/60 p-5 flex flex-col gap-4">

            {{-- ──────────────────────────────────────────────────────────────────
                 CASE 1: INITIAL MARKETPLACE ITEM
                 Only showing listing values in clean text / badges / image.
                 NO input fields and NO filter/search field!
                 ────────────────────────────────────────────────────────────────── --}}
            <template x-if="item._mode === 'initial_marketplace'">
                <div class="space-y-4">
                    {{-- Hidden form inputs so form submission includes all values --}}
                    <input type="hidden" :name="'items['+index+'][listing_id]'" :value="item.listing_id ?? ''">
                    <input type="hidden" :name="'items['+index+'][item_type]'" :value="item.item_type ?? 'product'">
                    <input type="hidden" :name="'items['+index+'][category_id]'" :value="item.category_id ?? ''">
                    <input type="hidden" :name="'items['+index+'][item_name]'" :value="item.item_name ?? ''">
                    <input type="hidden" :name="'items['+index+'][description]'" :value="item.description ?? ''">

                    {{-- Marketplace Linked Pill --}}
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                            <i class="fa-solid fa-store text-emerald-600"></i>
                            Marketplace Product
                        </span>
                        <span class="text-[10px] text-gray-400 font-mono" x-text="'ID #' + (item.listing_id || '')"></span>
                    </div>

                    {{-- Product Image --}}
                    <template x-if="item.listing_image_url">
                        <div class="w-full h-44 bg-gray-100 rounded-xl overflow-hidden border border-gray-200 shadow-sm relative group">
                            <img :src="item.listing_image_url" :alt="item.item_name" class="w-full h-full object-cover">
                        </div>
                    </template>
                    <template x-if="!item.listing_image_url">
                        <div class="w-full h-28 bg-gradient-to-br from-indigo-50/60 to-gray-100 rounded-xl flex flex-col items-center justify-center border border-gray-200 text-gray-400 gap-1">
                            <i class="fa-solid fa-box-open text-2xl text-indigo-300"></i>
                            <span class="text-[11px] text-gray-400">Marketplace Item</span>
                        </div>
                    </template>

                    {{-- Title --}}
                    <div>
                        <h3 class="text-base font-bold text-gray-900 leading-snug" x-text="item.item_name"></h3>
                    </div>

                    {{-- Meta Badges (Category + Type) --}}
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold flex items-center gap-1.5">
                            <i class="fa-solid fa-folder text-[10px] text-indigo-400"></i>
                            <span x-text="item.category_name || getCategoryName(item.category_id) || 'General'"></span>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-white text-gray-700 border border-gray-200 font-medium capitalize flex items-center gap-1.5">
                            <i class="fa-solid fa-tag text-[10px] text-gray-400"></i>
                            <span x-text="item.item_type"></span>
                        </span>
                    </div>

                    {{-- Description --}}
                    <div x-show="item.description" class="bg-white p-3 rounded-lg border border-gray-200 text-xs text-gray-600 leading-relaxed">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Product Description</p>
                        <p class="whitespace-pre-line" x-text="item.description"></p>
                    </div>

                    {{-- Spec Summary (Read-only listing specifications) --}}
                    <div x-show="item._attrGroups && item._attrGroups.length > 0" class="space-y-2.5">
                        <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-list-check text-indigo-500"></i>
                            Listing Specifications
                        </div>
                        <template x-for="group in item._attrGroups" :key="group.group_id">
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-2xs">
                                <div class="px-3 py-1.5 bg-gray-100/90 border-b border-gray-200 font-semibold text-gray-700 text-[11px]" x-text="group.group_name"></div>
                                <div class="divide-y divide-gray-100">
                                    <template x-for="attr in group.attributes" :key="attr.id">
                                        <div class="flex items-start justify-between gap-2 px-3 py-1.5">
                                            <span class="text-gray-500 text-[11px] shrink-0 w-2/5 leading-snug" x-text="attr.name"></span>
                                            <span class="font-medium text-gray-800 text-[11px] text-right leading-snug" x-text="getAttrDisplayValue(item, attr) || '—'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Loading indicator --}}
                    <div x-show="item._attrLoading" class="py-3 text-center text-xs text-gray-400">
                        <i class="fa-solid fa-circle-notch fa-spin text-indigo-500 mr-1"></i> Loading specifications...
                    </div>
                </div>
            </template>


            {{-- ──────────────────────────────────────────────────────────────────
                 CASE 2: ADDITIONAL MARKETPLACE ITEM (buyer clicked 'Add from Marketplace')
                 Shows marketplace search/filter options + editable input fields
                 ────────────────────────────────────────────────────────────────── --}}
            <template x-if="item._mode === 'marketplace'">
                <div class="space-y-3.5">
                    <input type="hidden" :name="'items['+index+'][listing_id]'" :value="item.listing_id ?? ''">

                    {{-- Search / filter options to link from marketplace --}}
                    <div class="relative">
                        <label class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
                            <i class="fa-solid fa-store text-indigo-500 mr-1"></i>
                            Search Marketplace Product
                        </label>
                        <div class="flex items-center gap-2">
                            <div class="relative w-full">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400 text-xs">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text"
                                       x-model="item._listingQuery"
                                       @input.debounce.400ms="searchListingsForItem(item)"
                                       placeholder="Type product name to search..."
                                       class="focus-accent w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">
                            </div>
                            <button type="button" x-show="item.listing_id" @click="clearListingForItem(item)"
                                    class="text-xs text-gray-400 hover:text-gray-700 whitespace-nowrap px-2 shrink-0" title="Unlink listing">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        {{-- Search Results Dropdown --}}
                        <div x-show="item._listingResults && item._listingResults.length > 0" x-cloak
                             class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                            <template x-for="l in item._listingResults" :key="l.id">
                                <button type="button" @click="selectListingForItem(item, l)"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-indigo-50 flex items-center justify-between gap-2 transition-colors border-b border-gray-100 last:border-0">
                                    <span x-text="l.name" class="font-medium text-gray-800 truncate"></span>
                                    <span class="text-gray-400 shrink-0 text-[11px]" x-text="l.category_name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Linked confirmation banner --}}
                    <div x-show="item.listing_id" class="p-2.5 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center gap-2.5">
                        <template x-if="item.listing_image_url">
                            <img :src="item.listing_image_url" class="w-9 h-9 rounded object-cover shrink-0 border border-indigo-200">
                        </template>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold text-indigo-900 truncate" x-text="item.item_name"></p>
                            <p class="text-[10px] text-indigo-600">Pre-filled from marketplace listing</p>
                        </div>
                        <button type="button" @click="clearListingForItem(item)" class="text-xs text-indigo-400 hover:text-indigo-700 shrink-0" title="Clear link">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Editable Product identity fields --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Type</label>
                            <select :name="'items['+index+'][item_type]'" x-model="item.item_type"
                                    class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Category</label>
                            <select :name="'items['+index+'][category_id]'" x-model="item.category_id"
                                    @change="onItemCategoryChange(item)"
                                    class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                Item Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" :name="'items['+index+'][item_name]'" x-model="item.item_name"
                                   placeholder="e.g. Science Lab Microscope"
                                   class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description / Notes</label>
                            <textarea :name="'items['+index+'][description]'" x-model="item.description"
                                      rows="2" placeholder="Any specific requirements or context..."
                                      class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </template>


            {{-- ──────────────────────────────────────────────────────────────────
                 CASE 3: CUSTOM ITEM (buyer clicked 'Add Custom Item')
                 Inputs for: Type, Category, Item Name, Description (NO marketplace search)
                 ────────────────────────────────────────────────────────────────── --}}
            <template x-if="item._mode === 'custom' || (!item._mode && !item.listing_id)">
                <div class="space-y-3.5">
                    <input type="hidden" :name="'items['+index+'][listing_id]'" value="">

                    {{-- Custom Requirement Badge --}}
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-gray-700">
                        <i class="fa-solid fa-pen-to-square text-indigo-500"></i>
                        <span>Custom Requirement</span>
                    </div>

                    {{-- Form Fields --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Type</label>
                            <select :name="'items['+index+'][item_type]'" x-model="item.item_type"
                                    class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                <option value="product">Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                Category
                                <span class="font-normal normal-case text-gray-400">(optional)</span>
                            </label>
                            <select :name="'items['+index+'][category_id]'" x-model="item.category_id"
                                    @change="onItemCategoryChange(item)"
                                    class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">
                                Item Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" :name="'items['+index+'][item_name]'" x-model="item.item_name"
                                   placeholder="e.g. Custom Science Kits, Lab Desks..."
                                   class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Description / Notes</label>
                            <textarea :name="'items['+index+'][description]'" x-model="item.description"
                                      rows="3" placeholder="Describe your exact requirements, dimensions, brand preference, or purpose..."
                                      class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </template>

        </div>


        {{-- ══════════════════════════════════════════════════════════════════════
             RIGHT PANEL (col-8)
             Quantity block + Specifications (standard category specs + custom attributes)
             ══════════════════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-8 p-5 flex flex-col gap-5">

            {{-- Quantity block — most important buyer input --}}
            <div class="bg-indigo-50/70 rounded-xl border border-indigo-100 p-5">
                <h3 class="text-sm font-bold text-indigo-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-hashtag text-indigo-500"></i>
                    How many do you need?
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-indigo-800 mb-1.5">
                            Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.001" min="0.001"
                               :name="'items['+index+'][quantity]'"
                               x-model="item.quantity"
                               placeholder="1"
                               class="focus-accent w-full px-3 py-2.5 border border-indigo-200 rounded-lg text-sm font-bold text-indigo-900 bg-white text-center">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-indigo-800 mb-1.5">Unit</label>
                        <select :name="'items['+index+'][unit_id]'" x-model="item.unit_id"
                                class="focus-accent w-full text-sm rounded-lg border border-indigo-200 px-3 py-2.5 bg-white text-indigo-900">
                            <option value="">Select unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}@if($unit->symbol) ({{ $unit->symbol }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-indigo-800 mb-1.5">Est. Unit Price</label>
                        <input type="number" step="0.01" min="0"
                               :name="'items['+index+'][estimated_unit_price]'"
                               x-model="item.estimated_unit_price"
                               placeholder="0.00"
                               class="focus-accent w-full px-3 py-2.5 border border-indigo-200 rounded-lg text-sm bg-white text-indigo-900">
                    </div>
                </div>
            </div>

            {{-- Specifications section --}}
            <div>
                {{-- Collapsible toggle bar --}}
                <button type="button"
                        @click="item._specsOpen = !item._specsOpen"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl border transition-colors"
                        :class="item._specsOpen
                            ? 'bg-gray-100 border-gray-300 text-gray-800'
                            : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'">
                    <span class="flex items-center gap-2 text-sm font-semibold">
                        <i class="fa-solid fa-sliders text-indigo-500 text-xs"></i>
                        <span>Specifications</span>
                        <span class="text-[11px] font-normal text-gray-400"
                              x-show="!item._specsOpen">(click to expand)</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span x-show="item._attrGroups && item._attrGroups.length > 0" class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700"
                              x-text="item._attrGroups.reduce((t, g) => t + g.attributes.length, 0) + ' standard'"></span>
                        <span x-show="item.custom_attributes && item.custom_attributes.length > 0" class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"
                              x-text="item.custom_attributes.length + ' custom'"></span>
                        <i class="fa-solid text-gray-400 text-xs transition-transform duration-200"
                           :class="item._specsOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </span>
                </button>

                {{-- Expanded specifications body --}}
                <div x-show="item._specsOpen" x-cloak class="mt-3">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

                        {{-- Alert banner for initial marketplace item --}}
                        <template x-if="item._mode === 'initial_marketplace'">
                            <div class="px-4 py-2.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-amber-500 text-xs"></i>
                                <span class="text-[11px] text-amber-800">These specifications are pre-filled from the product listing. You can adjust values below or add custom attributes.</span>
                            </div>
                        </template>

                        <div class="p-4 space-y-4">
                            {{-- Standard Category-Defined Attributes --}}
                            <div x-show="item.category_id">
                                @include('backend.buyer.procurement.rfqs.partials._item-attributes')
                            </div>

                            {{-- Custom Specifications / Custom Attributes Section --}}
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-layer-group text-indigo-500 text-xs"></i>
                                        <span class="text-xs font-bold text-gray-800"
                                              x-text="item._mode === 'custom' ? 'Custom Specifications' : 'Additional / Custom Attributes'"></span>
                                        <span class="text-[10px] text-gray-400 font-medium"
                                              x-show="item.custom_attributes && item.custom_attributes.length > 0"
                                              x-text="'(' + item.custom_attributes.length + ' added)'"></span>
                                    </div>
                                    <button type="button" @click="addCustomAttribute(item)"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                        <span x-text="item._mode === 'custom' ? 'Add Specification' : 'Add Custom Attribute'"></span>
                                    </button>
                                </div>

                                {{-- Empty state --}}
                                <div x-show="!item.custom_attributes || item.custom_attributes.length === 0"
                                     class="py-3 px-4 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-center">
                                    <p class="text-xs text-gray-500 flex items-center justify-center gap-1.5">
                                        <i class="fa-regular fa-lightbulb text-amber-500"></i>
                                        <span x-text="item._mode === 'custom' ? 'No specifications added yet. Click &quot;Add Specification&quot; to define your required attributes.' : 'Need extra specifications not listed above? Click &quot;Add Custom Attribute&quot;.'"></span>
                                    </p>
                                </div>

                                {{-- Custom Attribute Rows --}}
                                <div x-show="item.custom_attributes && item.custom_attributes.length > 0" class="space-y-2">
                                    <template x-for="(attr, cIdx) in item.custom_attributes" :key="cIdx">
                                        <div class="flex items-center gap-2 bg-gray-50/80 p-2 rounded-lg border border-gray-200">
                                            <div class="w-2/5">
                                                <input type="text"
                                                       :name="'items['+index+'][custom_attributes]['+cIdx+'][name]'"
                                                       x-model="attr.name"
                                                       placeholder="Attribute name (e.g. Color, RAM)"
                                                       class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                                            </div>
                                            <div class="flex-1">
                                                <input type="text"
                                                       :name="'items['+index+'][custom_attributes]['+cIdx+'][value]'"
                                                       x-model="attr.value"
                                                       placeholder="Specification value (e.g. Black, 16GB)"
                                                       class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-1.5 bg-white">
                                            </div>
                                            <button type="button" @click="removeCustomAttribute(item, cIdx)" title="Delete this specification"
                                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors shrink-0">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
