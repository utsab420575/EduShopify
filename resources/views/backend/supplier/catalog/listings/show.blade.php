@extends('backend.layouts.supplier')

@section('title', $listing->name)
@section('breadcrumb', 'Catalog / Listing Details')

@section('body')

    <x-backend.page-header title="{{ $listing->name }}" subtitle="Listing ID: {{ $listing->listing_number }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if($listing->approval_status === 'draft' || $listing->approval_status === 'rejected')
                    <form method="POST" action="{{ route('supplier.catalog.listings.submit', $listing) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-xs font-semibold px-3 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Submit for Approval
                        </button>
                    </form>
                @endif
                <a href="{{ route('supplier.catalog.listings.edit', $listing) }}" class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Main Details --}}
        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="Overview">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Type</span>
                        <span class="font-semibold text-gray-800 uppercase">{{ $listing->listing_type }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Category</span>
                        <span class="font-semibold text-gray-800">{{ $listing->mainCategory?->name ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Price</span>
                        <span class="font-semibold text-indigo-700 text-sm">{{ $listing->base_price ? $listing->currency_code . ' ' . number_format($listing->base_price, 2) : 'RFQ' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <x-backend.status-badge :status="$listing->approval_status" />
                    </div>
                </div>

                @if($listing->short_description)
                    <p class="text-sm text-gray-600 mb-3 font-medium">{{ $listing->short_description }}</p>
                @endif
                <div class="text-sm text-gray-700 whitespace-pre-line">{{ $listing->description }}</div>
            </x-backend.form-card>

            {{-- Specifications & Attributes --}}
            @if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty())
                <x-backend.form-card title="Specifications & Technical Attributes">
                    <div class="space-y-6">
                        @foreach($groupedSpecifications as $group)
                            <div>
                                <div class="flex items-center gap-2 pb-2 mb-3 border-b border-gray-100">
                                    <i class="fa-solid fa-sliders text-xs text-indigo-500"></i>
                                    <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">{{ $group['group_name'] }}</h4>
                                </div>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-xs">
                                    @foreach($group['items'] as $item)
                                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                                            <dt class="text-gray-500 flex items-center gap-1.5">
                                                <span>{{ $item->attribute?->name }}</span>
                                                @if($item->attribute?->unit)
                                                    <span class="text-gray-400 font-normal">({{ $item->attribute->unit->symbol ?? $item->attribute->unit->name }})</span>
                                                @endif
                                            </dt>
                                            <dd class="font-semibold text-gray-800 flex items-center gap-1.5">
                                                @if($item->attribute?->input_type === 'color' && $item->attributeValue?->color_hex)
                                                    <span class="w-3 h-3 rounded-full border border-gray-300 inline-block" style="background-color: {{ $item->attributeValue->color_hex }}"></span>
                                                @endif
                                                <span>{{ $item->formattedValue() }}</span>
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif

            {{-- Variants (if product) --}}
            @if($listing->isProduct())
                <div x-data="variantManager({
                    listingId: {{ $listing->id }},
                    currency: '{{ $listing->currency_code }}',
                    basePrice: {{ (float) ($listing->base_price ?? 0) }},
                    skuPrefix: '{{ $listing->sku ? $listing->sku . '-' : 'VAR-' }}',
                    variantAttributes: {{ Js::from($variantEligibleAttributes ?? []) }},
                    existingVariants: {{ Js::from($listing->variants->map(fn($v) => [
                        'id' => $v->id,
                        'name' => $v->name,
                        'sku' => $v->sku,
                        'price' => (float)$v->price,
                        'compare_at_price' => $v->compare_at_price ? (float)$v->compare_at_price : null,
                        'stock_quantity' => (float)$v->stock_quantity,
                        'stock_status' => $v->stock_status,
                        'is_active' => (bool)$v->is_active,
                        'currency_code' => $v->currency_code,
                        'attributes' => $v->variantAttributes->map(fn($va) => [
                            'attribute_id' => $va->attribute_id,
                            'name' => $va->attribute?->name,
                            'value' => $va->resolvedValue(),
                        ]),
                    ])) }}
                })" class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-boxes-stacked text-indigo-600"></i>
                            <h3 class="text-sm font-semibold text-gray-900">Product Variants</h3>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700" x-text="variants.length + ' variant(s)'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="variantAttributes.length > 0">
                                <button type="button" @click="openGeneratorModal()" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 text-indigo-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Generate Combinations
                                </button>
                            </template>
                            <button type="button" @click="openCreateModal()" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                                <i class="fa-solid fa-plus"></i> Add Variant
                            </button>
                        </div>
                    </div>

                    {{-- Variants Table --}}
                    <div class="p-0">
                        <template x-if="variants.length === 0">
                            <div class="p-8 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                    <i class="fa-solid fa-cubes text-xl"></i>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-1">No variants added yet</h4>
                                <p class="text-xs text-gray-500 max-w-sm mx-auto mb-4">
                                    Create specific variations with distinct sizes, colors, prices, and inventory counts.
                                </p>
                                <div class="flex items-center justify-center gap-2">
                                    <template x-if="variantAttributes.length > 0">
                                        <button type="button" @click="openGeneratorModal()" class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 text-xs font-semibold flex items-center gap-1.5">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i> Generate from Specifications
                                        </button>
                                    </template>
                                    <button type="button" @click="openCreateModal()" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                                        <i class="fa-solid fa-plus"></i> Add Custom Variant
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="variants.length > 0">
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-500 uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="px-5 py-3 font-semibold">Variant Details</th>
                                            <th class="px-3 py-3 font-semibold">SKU</th>
                                            <th class="px-3 py-3 font-semibold">Price</th>
                                            <th class="px-3 py-3 font-semibold">Stock</th>
                                            <th class="px-3 py-3 font-semibold">Status</th>
                                            <th class="px-5 py-3 font-semibold text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(v, index) in variants" :key="v.id">
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-5 py-3">
                                                    <p class="font-bold text-gray-900" x-text="v.name"></p>
                                                    <template x-if="v.attributes && v.attributes.length > 0">
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            <template x-for="attr in v.attributes" :key="attr.attribute_id">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">
                                                                    <span class="text-gray-400 mr-1" x-text="attr.name + ':'"></span>
                                                                    <strong x-text="attr.value"></strong>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </td>
                                                <td class="px-3 py-3 font-mono text-gray-600" x-text="v.sku || '-'"></td>
                                                <td class="px-3 py-3 font-bold text-indigo-700 text-sm">
                                                    <span x-text="v.currency_code"></span>
                                                    <span x-text="Number(v.price).toFixed(2)"></span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="font-semibold text-gray-800" x-text="v.stock_quantity"></span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                                          :class="v.stock_status === 'in_stock' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                                          x-text="v.stock_status ? v.stock_status.replace('_', ' ') : 'in stock'"></span>
                                                </td>
                                                <td class="px-5 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="button" @click="openEditModal(v)" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded transition-colors" title="Edit Variant">
                                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                        </button>
                                                        <form method="POST" :action="'{{ url('supplier/catalog/listings/' . $listing->id . '/variants') }}/' + v.id" onsubmit="return confirm('Are you sure you want to delete this variant?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete Variant">
                                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>

                    {{-- MODAL 1: Auto-Generate Combinations Matrix --}}
                    <div x-show="generatorModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="generatorModalOpen = false"></div>
                            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-bold text-gray-900">Auto-Generate Variant Combinations</h3>
                                                <p class="text-xs text-gray-500">Pick options from your specifications to generate combinations automatically.</p>
                                            </div>
                                        </div>
                                        <button type="button" @click="generatorModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>

                                    {{-- Options Selection --}}
                                    <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                                        <template x-for="attr in variantAttributes" :key="attr.id">
                                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-xs font-bold text-gray-800" x-text="attr.name"></span>
                                                    <span class="text-[10px] text-gray-400" x-text="(selectedGeneratorOptions[attr.id] || []).length + ' selected'"></span>
                                                </div>
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="opt in (attr.available_options.length > 0 ? attr.available_options : attr.all_options)" :key="opt.id">
                                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs cursor-pointer transition-all"
                                                               :class="isGeneratorOptionSelected(attr.id, opt.value) ? 'bg-indigo-600 text-white border-indigo-600 font-semibold shadow-xs' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'">
                                                            <input type="checkbox"
                                                                   :value="opt.value"
                                                                   :checked="isGeneratorOptionSelected(attr.id, opt.value)"
                                                                   @change="toggleGeneratorOption(attr.id, opt.id, opt.value)"
                                                                   class="sr-only">
                                                            <span x-text="opt.value"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Matrix Defaults --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-100 text-xs">
                                        <div>
                                            <label class="block font-medium text-gray-700 mb-1">Default Price (<span x-text="currency"></span>)</label>
                                            <input type="number" step="0.01" x-model="generatorDefaults.price" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-gray-700 mb-1">Default Stock Qty</label>
                                            <input type="number" x-model="generatorDefaults.stock" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-gray-700 mb-1">SKU Prefix</label>
                                            <input type="text" x-model="generatorDefaults.skuPrefix" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-mono">
                                        </div>
                                    </div>

                                    {{-- Calculation Summary --}}
                                    <div class="mt-4 p-3 bg-indigo-50/60 rounded-xl border border-indigo-100 flex items-center justify-between text-xs">
                                        <span class="text-indigo-900 font-medium">
                                            Ready to generate: <strong x-text="previewCombinations.length"></strong> variant(s)
                                        </span>
                                        <span class="text-indigo-600 text-[11px]" x-show="previewCombinations.length > 0">
                                            Duplicates will be skipped automatically
                                        </span>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('supplier.catalog.listings.variants.generate', $listing) }}">
                                    @csrf
                                    <template x-for="(comb, idx) in previewCombinations" :key="idx">
                                        <div>
                                            <input type="hidden" :name="'variants[' + idx + '][name]'" :value="comb.name">
                                            <input type="hidden" :name="'variants[' + idx + '][sku]'" :value="generatorDefaults.skuPrefix + (idx + 1)">
                                            <input type="hidden" :name="'variants[' + idx + '][price]'" :value="generatorDefaults.price">
                                            <input type="hidden" :name="'variants[' + idx + '][stock_quantity]'" :value="generatorDefaults.stock">
                                            <template x-for="(valId, attrId) in comb.attributes" :key="attrId">
                                                <input type="hidden" :name="'variants[' + idx + '][attributes][' + attrId + ']'" :value="valId">
                                            </template>
                                        </div>
                                    </template>

                                    <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                                        <button type="submit" :disabled="previewCombinations.length === 0" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5 disabled:opacity-50">
                                            <i class="fa-solid fa-check"></i> Generate &amp; Save Variants (<span x-text="previewCombinations.length"></span>)
                                        </button>
                                        <button type="button" @click="generatorModalOpen = false" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL 2: Create / Edit Single Variant Modal --}}
                    <div x-show="formModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="formModalOpen = false"></div>
                            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                
                                <form method="POST" :action="isEditing ? '{{ url('supplier/catalog/listings/' . $listing->id . '/variants') }}/' + currentVariant.id : '{{ route('supplier.catalog.listings.variants.store', $listing) }}'">
                                    @csrf
                                    <template x-if="isEditing">
                                        <input type="hidden" name="_method" value="PUT">
                                    </template>

                                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                                            <h3 class="text-sm font-bold text-gray-900" x-text="isEditing ? 'Edit Variant' : 'Add New Variant'"></h3>
                                            <button type="button" @click="formModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>

                                        {{-- If creating new, show Attribute Selectors --}}
                                        <template x-if="!isEditing && variantAttributes.length > 0">
                                            <div class="space-y-3 mb-4 pb-4 border-b border-gray-100">
                                                <p class="text-xs font-semibold text-gray-700">Select Variant Specifications:</p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <template x-for="attr in variantAttributes" :key="attr.id">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-600 mb-1" x-text="attr.name"></label>
                                                            <select :name="'attributes[' + attr.id + ']'"
                                                                    x-model="singleFormAttributes[attr.id]"
                                                                    @change="updateSingleFormVariantName()"
                                                                    class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                                                <option value="">Select option</option>
                                                                <template x-for="opt in (attr.available_options.length > 0 ? attr.available_options : attr.all_options)" :key="opt.id">
                                                                    <option :value="opt.id" x-text="opt.value"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-1">Variant Name / Title <span class="text-red-500">*</span></label>
                                                <input type="text" name="name" x-model="singleForm.name" required placeholder="e.g. 500ml / Blue" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Price (<span x-text="currency"></span>) <span class="text-red-500">*</span></label>
                                                    <input type="number" step="0.01" name="price" x-model="singleForm.price" required class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-semibold">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Compare At Price</label>
                                                    <input type="number" step="0.01" name="compare_at_price" x-model="singleForm.compare_at_price" placeholder="Optional" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">SKU / Model Code</label>
                                                    <input type="text" name="sku" x-model="singleForm.sku" placeholder="e.g. SPK-BLU-01" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-mono">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Stock Quantity</label>
                                                    <input type="number" name="stock_quantity" x-model="singleForm.stock_quantity" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Stock Status</label>
                                                    <select name="stock_status" x-model="singleForm.stock_status" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                                        <option value="in_stock">In Stock</option>
                                                        <option value="out_of_stock">Out of Stock</option>
                                                        <option value="on_backorder">On Backorder</option>
                                                        <option value="made_to_order">Made to Order</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-center pt-5">
                                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                                        <input type="checkbox" name="is_active" value="1" x-model="singleForm.is_active" class="rounded text-indigo-600 focus:ring-indigo-500">
                                                        <span class="text-xs font-semibold text-gray-700">Active</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                                        <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                                            <i class="fa-solid fa-check"></i>
                                            <span x-text="isEditing ? 'Save Changes' : 'Create Variant'"></span>
                                        </button>
                                        <button type="button" @click="formModalOpen = false" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Quantity Break / Tier Pricing --}}
            <div x-data="tierPriceManager({
                listingId: {{ $listing->id }},
                currency: '{{ $listing->currency_code }}',
                hasVariants: {{ $listing->variants->isNotEmpty() ? 'true' : 'false' }},
                variants: {{ Js::from($listing->variants->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'price' => (float)$v->price,
                    'tier_count' => $v->tierPrices->count(),
                ])) }},
                globalTiers: {{ Js::from($listing->allTierPrices->whereNull('listing_variant_id')->values()->map(fn($tp) => [
                    'id' => $tp->id,
                    'min_quantity' => (float)$tp->min_quantity,
                    'max_quantity' => $tp->max_quantity ? (float)$tp->max_quantity : null,
                    'unit_price' => (float)$tp->unit_price,
                    'currency_code' => $tp->currency_code,
                ])) }},
                variantTiers: {{ Js::from($listing->allTierPrices->whereNotNull('listing_variant_id')->groupBy('listing_variant_id')->map(fn($group) => $group->map(fn($tp) => [
                    'id' => $tp->id,
                    'listing_variant_id' => $tp->listing_variant_id,
                    'min_quantity' => (float)$tp->min_quantity,
                    'max_quantity' => $tp->max_quantity ? (float)$tp->max_quantity : null,
                    'unit_price' => (float)$tp->unit_price,
                    'currency_code' => $tp->currency_code,
                ])) ) }}
            })" class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <i class="fa-solid fa-layer-group text-indigo-600"></i>
                        <h3 class="text-sm font-semibold text-gray-900">Quantity Break / Tier Pricing</h3>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    {{-- Mode Selector (if product has variants) --}}
                    @if($listing->variants->isNotEmpty())
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                            <label class="block text-xs font-bold text-gray-700 mb-2">Choose Pricing Mode:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer transition-all bg-white"
                                       :class="mode === 'global' ? 'border-indigo-600 ring-1 ring-indigo-600 shadow-xs' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="pricing_mode" value="global" x-model="mode" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Same for all variants (Global)</p>
                                        <p class="text-[11px] text-gray-500">One shared volume discount table applies across all variants.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer transition-all bg-white"
                                       :class="mode === 'variant' ? 'border-indigo-600 ring-1 ring-indigo-600 shadow-xs' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="pricing_mode" value="variant" x-model="mode" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Different per variant</p>
                                        <p class="text-[11px] text-gray-500">Set independent bulk discount tiers for each variant.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- MODE 1: GLOBAL TIER PRICING --}}
                    <div x-show="mode === 'global'" class="space-y-4">
                        <form method="POST" action="{{ route('supplier.catalog.listings.tier-prices.store', $listing) }}" class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                            @csrf
                            <p class="text-xs font-semibold text-gray-700 mb-2.5">Add Global Volume Discount Tier</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-2.5">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Min Quantity <span class="text-red-500">*</span></label>
                                    <input type="number" name="min_quantity" placeholder="e.g. 50" required class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Max Quantity (optional)</label>
                                    <input type="number" name="max_quantity" placeholder="Leave empty for &infin;" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Unit Price (<span x-text="currency"></span>) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" name="unit_price" placeholder="Discounted unit price" required class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-semibold">
                                </div>
                            </div>
                            <button type="submit" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                                <i class="fa-solid fa-plus"></i> Add Global Tier Price
                            </button>
                        </form>

                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                                        <th class="px-4 py-2.5 font-semibold">Unit Price</th>
                                        <th class="px-4 py-2.5 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-if="globalTiers.length === 0">
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">
                                                No global tier prices defined yet.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="tp in globalTiers" :key="tp.id">
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-4 py-2.5 font-medium text-gray-800">
                                                <span x-text="Number(tp.min_quantity).toFixed(0)"></span> &ndash;
                                                <span x-text="tp.max_quantity ? Number(tp.max_quantity).toFixed(0) : '∞'"></span> units
                                            </td>
                                            <td class="px-4 py-2.5 font-bold text-indigo-700">
                                                <span x-text="tp.currency_code"></span>
                                                <span x-text="Number(tp.unit_price).toFixed(2)"></span>
                                            </td>
                                            <td class="px-4 py-2.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" @click="openEditTierModal(tp)" class="text-indigo-600 hover:text-indigo-800 text-[11px] font-semibold flex items-center gap-1">
                                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                                    </button>
                                                    <span class="text-gray-300">|</span>
                                                    <form method="POST" :action="'{{ url('supplier/catalog/listings/' . $listing->id . '/tier-prices') }}/' + tp.id" onsubmit="return confirm('Remove this tier price?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 text-[11px] font-medium">Remove</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- MODE 2: VARIANT-SPECIFIC TIER PRICING --}}
                    <div x-show="mode === 'variant'" class="space-y-4" x-cloak>
                        {{-- Variant Selector Tabs --}}
                        <div>
                            <p class="text-xs font-bold text-gray-700 mb-2">Select Variant to Manage:</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="v in variants" :key="v.id">
                                    <button type="button" @click="selectedVariantId = v.id"
                                            class="px-3 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all"
                                            :class="selectedVariantId == v.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'">
                                        <span x-text="v.name"></span>
                                        <span class="px-1.5 py-0.2 rounded text-[10px]"
                                              :class="selectedVariantId == v.id ? 'bg-indigo-700 text-white' : 'bg-gray-100 text-gray-600'"
                                              x-text="(variantTiers[v.id] || []).length + ' tiers'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Variant Specific Add Form & Actions --}}
                        <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 pb-2 border-b border-gray-200">
                                <div>
                                    <p class="text-xs font-bold text-gray-800">
                                        Tier Pricing for: <span class="text-indigo-600" x-text="selectedVariant ? selectedVariant.name : 'Select a variant'"></span>
                                    </p>
                                    <p class="text-[11px] text-gray-500">Base price: <strong x-text="currency + ' ' + (selectedVariant ? Number(selectedVariant.price).toFixed(2) : '0.00')"></strong></p>
                                </div>
                                <template x-if="globalTiers.length > 0 && selectedVariantId">
                                    <form method="POST" action="{{ route('supplier.catalog.listings.tier-prices.copy-global', $listing) }}">
                                        @csrf
                                        <input type="hidden" name="target_variant_id" :value="selectedVariantId">
                                        <button type="submit" class="px-2.5 py-1 rounded-md border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[11px] font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-copy"></i> Copy Global Tiers to This Variant
                                        </button>
                                    </form>
                                </template>
                            </div>

                            <form method="POST" action="{{ route('supplier.catalog.listings.tier-prices.store', $listing) }}">
                                @csrf
                                <input type="hidden" name="listing_variant_id" :value="selectedVariantId">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-2.5">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Min Quantity <span class="text-red-500">*</span></label>
                                        <input type="number" name="min_quantity" placeholder="e.g. 50" required class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Max Quantity (optional)</label>
                                        <input type="number" name="max_quantity" placeholder="Leave empty for &infin;" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-600 mb-1">Unit Price (<span x-text="currency"></span>) <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" name="unit_price" placeholder="Discounted unit price" required class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-semibold">
                                    </div>
                                </div>
                                <button type="submit" :disabled="!selectedVariantId" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5 disabled:opacity-50">
                                    <i class="fa-solid fa-plus"></i> Add Variant Tier Price
                                </button>
                            </form>
                        </div>

                        {{-- Variant Tier Table --}}
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                                        <th class="px-4 py-2.5 font-semibold">Variant Unit Price</th>
                                        <th class="px-4 py-2.5 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-if="currentVariantTiers.length === 0">
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-400">
                                                <p class="font-medium text-gray-600 mb-0.5">No custom tiers defined for this variant yet.</p>
                                                <p class="text-[11px]">This variant will automatically use the <strong>Global Tier Pricing</strong> table as fallback.</p>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="tp in currentVariantTiers" :key="tp.id">
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-4 py-2.5 font-medium text-gray-800">
                                                <span x-text="Number(tp.min_quantity).toFixed(0)"></span> &ndash;
                                                <span x-text="tp.max_quantity ? Number(tp.max_quantity).toFixed(0) : '∞'"></span> units
                                            </td>
                                            <td class="px-4 py-2.5 font-bold text-indigo-700">
                                                <span x-text="tp.currency_code"></span>
                                                <span x-text="Number(tp.unit_price).toFixed(2)"></span>
                                            </td>
                                            <td class="px-4 py-2.5 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" @click="openEditTierModal(tp)" class="text-indigo-600 hover:text-indigo-800 text-[11px] font-semibold flex items-center gap-1">
                                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                                    </button>
                                                    <span class="text-gray-300">|</span>
                                                    <form method="POST" :action="'{{ url('supplier/catalog/listings/' . $listing->id . '/tier-prices') }}/' + tp.id" onsubmit="return confirm('Remove this tier price?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 text-[11px] font-medium">Remove</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- EDIT TIER PRICE MODAL --}}
                <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="editModalOpen = false"></div>
                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                            <form method="POST" :action="'{{ url('supplier/catalog/listings/' . $listing->id . '/tier-prices') }}/' + editingTier.id">
                                @csrf @method('PUT')
                                <div class="bg-white px-6 pt-5 pb-4 sm:p-6">
                                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                                        <h3 class="text-sm font-bold text-gray-900">Edit Tier Price</h3>
                                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Min Quantity <span class="text-red-500">*</span></label>
                                            <input type="number" name="min_quantity" x-model="editingTier.min_quantity" required class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-medium">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Max Quantity (optional)</label>
                                            <input type="number" name="max_quantity" x-model="editingTier.max_quantity" placeholder="Leave empty for &infin;" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Unit Price (<span x-text="currency"></span>) <span class="text-red-500">*</span></label>
                                            <input type="number" step="0.01" name="unit_price" x-model="editingTier.unit_price" required class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-semibold">
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-1.5">
                                        <i class="fa-solid fa-check"></i> Save Changes
                                    </button>
                                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar Info --}}
        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Photos & Media">
                @php($gallery = $listing->getMedia('gallery'))
                @if($gallery->isNotEmpty())
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        @foreach($gallery as $media)
                            <div class="relative group">
                                <img src="{{ $media->getUrl() }}" alt="" class="w-full h-16 object-cover rounded-lg border border-gray-200">
                                <form method="POST" action="{{ route('supplier.catalog.listings.media.destroy', [$listing, $media]) }}" class="absolute top-0.5 right-0.5">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-5 h-5 rounded-full bg-white/90 text-red-600 text-[10px] flex items-center justify-center shadow">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('supplier.catalog.listings.media.store', $listing) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="image" required accept="image/*" class="w-full text-xs text-gray-500">
                    <button type="submit" class="btn-primary w-full text-xs font-medium py-2 rounded">
                        <i class="fa-solid fa-upload mr-1"></i> Upload Image
                    </button>
                </form>
            </x-backend.form-card>

            <x-backend.form-card title="Listing Information">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Created Date</span>
                        <span class="font-medium text-gray-800">{{ $listing->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-100">
                        <span class="text-gray-400">Published Date</span>
                        <span class="font-medium text-gray-800">{{ $listing->published_at ? $listing->published_at->format('d M Y') : 'Not published' }}</span>
                    </div>
                    @if($listing->rejection_reason)
                        <div class="p-2 bg-red-50 text-red-700 rounded-lg text-xs mt-2">
                            <strong>Rejection Note:</strong> {{ $listing->rejection_reason }}
                        </div>
                    @endif
                </div>
            </x-backend.form-card>
        </div>

    </div>

    <script>
    function variantManager(config) {
        return {
            listingId: config.listingId,
            currency: config.currency,
            basePrice: config.basePrice,
            skuPrefix: config.skuPrefix,
            variantAttributes: config.variantAttributes || [],
            variants: config.existingVariants || [],

            generatorModalOpen: false,
            formModalOpen: false,
            isEditing: false,
            currentVariant: null,

            // Auto generator state
            selectedGeneratorOptions: {},
            generatorDefaults: {
                price: config.basePrice || 0,
                stock: 10,
                skuPrefix: config.skuPrefix || 'VAR-',
            },

            // Single form state
            singleForm: {
                name: '',
                price: config.basePrice || 0,
                compare_at_price: '',
                sku: '',
                stock_quantity: 10,
                stock_status: 'in_stock',
                is_active: true,
            },
            singleFormAttributes: {},

            init() {
                // Pre-select all available options for generator
                this.variantAttributes.forEach(attr => {
                    const opts = attr.available_options.length > 0 ? attr.available_options : attr.all_options;
                    this.selectedGeneratorOptions[attr.id] = opts.map(o => ({ id: o.id, value: o.value }));
                });
            },

            openGeneratorModal() {
                this.generatorDefaults.price = this.basePrice;
                this.generatorDefaults.stock = 10;
                this.generatorDefaults.skuPrefix = this.skuPrefix;
                this.generatorModalOpen = true;
            },

            isGeneratorOptionSelected(attrId, value) {
                const list = this.selectedGeneratorOptions[attrId] || [];
                return list.some(item => item.value === value);
            },

            toggleGeneratorOption(attrId, optId, value) {
                if (!this.selectedGeneratorOptions[attrId]) {
                    this.selectedGeneratorOptions[attrId] = [];
                }
                const idx = this.selectedGeneratorOptions[attrId].findIndex(item => item.value === value);
                if (idx > -1) {
                    this.selectedGeneratorOptions[attrId].splice(idx, 1);
                } else {
                    this.selectedGeneratorOptions[attrId].push({ id: optId, value: value });
                }
            },

            get previewCombinations() {
                const activeAttrs = this.variantAttributes.filter(a => {
                    const selected = this.selectedGeneratorOptions[a.id] || [];
                    return selected.length > 0;
                });

                if (activeAttrs.length === 0) return [];

                const cartesian = (arrays) => {
                    return arrays.reduce((acc, curr) => {
                        const res = [];
                        acc.forEach(a => {
                            curr.forEach(b => {
                                res.push([...a, b]);
                            });
                        });
                        return res;
                    }, [[]]);
                };

                const optionArrays = activeAttrs.map(a => {
                    return (this.selectedGeneratorOptions[a.id] || []).map(opt => ({
                        attribute_id: a.id,
                        attribute_name: a.name,
                        option_id: opt.id,
                        option_value: opt.value,
                    }));
                });

                const combos = cartesian(optionArrays);

                return combos.map(combo => {
                    const name = combo.map(c => c.option_value).join(' / ');
                    const attrMap = {};
                    combo.forEach(c => {
                        attrMap[c.attribute_id] = c.option_id;
                    });
                    return {
                        name: name,
                        attributes: attrMap,
                    };
                });
            },

            openCreateModal() {
                this.isEditing = false;
                this.currentVariant = null;
                this.singleForm = {
                    name: '',
                    price: this.basePrice,
                    compare_at_price: '',
                    sku: this.skuPrefix + (this.variants.length + 1),
                    stock_quantity: 10,
                    stock_status: 'in_stock',
                    is_active: true,
                };
                this.singleFormAttributes = {};
                this.formModalOpen = true;
            },

            openEditModal(variant) {
                this.isEditing = true;
                this.currentVariant = variant;
                this.singleForm = {
                    name: variant.name,
                    price: variant.price,
                    compare_at_price: variant.compare_at_price || '',
                    sku: variant.sku || '',
                    stock_quantity: variant.stock_quantity,
                    stock_status: variant.stock_status || 'in_stock',
                    is_active: variant.is_active,
                };
                this.formModalOpen = true;
            },

            updateSingleFormVariantName() {
                const parts = [];
                this.variantAttributes.forEach(attr => {
                    const valId = this.singleFormAttributes[attr.id];
                    if (valId) {
                        const allOpts = attr.available_options.concat(attr.all_options);
                        const found = allOpts.find(o => o.id == valId);
                        if (found) {
                            parts.push(found.value);
                        } else if (typeof valId === 'string' && valId) {
                            parts.push(valId);
                        }
                    }
                });
                if (parts.length > 0) {
                    this.singleForm.name = parts.join(' / ');
                }
            }
        };
    }

    function tierPriceManager(config) {
        return {
            listingId: config.listingId,
            currency: config.currency,
            hasVariants: config.hasVariants,
            variants: config.variants || [],
            globalTiers: config.globalTiers || [],
            variantTiers: config.variantTiers || {},

            mode: (config.variants && config.variants.length > 0 && Object.keys(config.variantTiers || {}).length > 0) ? 'variant' : 'global',
            selectedVariantId: (config.variants && config.variants.length > 0) ? config.variants[0].id : null,

            editModalOpen: false,
            editingTier: {
                id: null,
                min_quantity: '',
                max_quantity: '',
                unit_price: '',
            },

            get selectedVariant() {
                if (!this.selectedVariantId) return null;
                return this.variants.find(v => v.id == this.selectedVariantId) || null;
            },

            get currentVariantTiers() {
                if (!this.selectedVariantId) return [];
                return this.variantTiers[this.selectedVariantId] || [];
            },

            openEditTierModal(tier) {
                this.editingTier = {
                    id: tier.id,
                    min_quantity: tier.min_quantity,
                    max_quantity: tier.max_quantity || '',
                    unit_price: tier.unit_price,
                };
                this.editModalOpen = true;
            }
        };
    }
    </script>

@endsection
