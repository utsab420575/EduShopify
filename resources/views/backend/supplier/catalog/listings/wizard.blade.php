@php
    $isEdit = isset($listing) && $listing !== null;
    $currentStep = request('step') ? (int)request('step') : ($isEdit ? min(max($listing->setup_step, 1), 4) : 1);
    $mediaList = $isEdit ? $listing->getMedia('gallery')->map(fn ($m) => [
        'id'         => $m->id,
        'url'        => $m->getUrl(),
        'file_name'  => $m->file_name,
        'size'       => $m->human_readable_size,
        'is_primary' => $m->id === $listing->primary_image_media_id,
    ])->values()->toArray() : [];

    $tierPricesList = $isEdit ? $listing->globalTierPrices->map(fn ($t) => [
        'min_quantity' => (float)$t->min_quantity,
        'max_quantity' => $t->max_quantity ? (float)$t->max_quantity : null,
        'unit_price'   => (float)$t->unit_price,
    ])->values()->toArray() : [];

    $variantsList = $isEdit && $listing->variants ? $listing->variants->map(function ($v) {
        $primaryMedia = $v->images->firstWhere('pivot.is_primary', true) ?? $v->images->first();
        return [
            'id'                     => $v->id,
            'name'                   => $v->name,
            'sku'                    => $v->sku,
            'price'                  => (float)$v->price,
            'compare_at_price'       => $v->compare_at_price ? (float)$v->compare_at_price : null,
            'stock_quantity'         => (float)$v->stock_quantity,
            'stock_status'           => $v->stock_status ?? 'in_stock',
            'primary_image_media_id' => $primaryMedia?->id,
            'image_media_ids'        => $v->images->pluck('id')->values()->toArray(),
            'tier_prices'            => $v->tierPrices->map(fn ($tp) => [
                'min_quantity' => (float)$tp->min_quantity,
                'max_quantity' => $tp->max_quantity ? (float)$tp->max_quantity : null,
                'unit_price'   => (float)$tp->unit_price,
            ])->values()->toArray(),
            'attributes'             => $v->variantAttributes->pluck('attribute_value_id', 'attribute_id')->toArray(),
            'attribute_chips'        => $v->variantAttributes->map(fn ($va) => [
                'name'  => $va->attribute?->name,
                'value' => $va->attributeValue?->value ?? $va->custom_value,
            ])->values()->toArray(),
        ];
    })->values()->toArray() : [];
@endphp

<div x-data="productListingWizard({
    isEdit: {{ json_encode($isEdit) }},
    listingId: {{ json_encode($isEdit ? $listing->id : null) }},
    currentStep: {{ $currentStep }},
    maxCompletedStep: {{ json_encode($isEdit ? $listing->setup_step : 1) }},
    listingType: {{ json_encode($isEdit ? ($listing->listing_type_id ?? '') : old('listing_type_id', '')) }},
    primaryImageId: {{ json_encode($isEdit ? $listing->primary_image_media_id : null) }},
    mediaItems: {{ json_encode($mediaList) }},
    pricingType: {{ json_encode($isEdit ? ($listing->pricing_type_id ?? '') : old('pricing_type_id', '')) }},
    hasTierPricing: {{ json_encode($isEdit ? $listing->globalTierPrices->isNotEmpty() : false) }},
    tierPrices: {{ json_encode($tierPricesList) }},
    variants: {{ json_encode($variantsList) }},
    categoryNodes: {{ json_encode($categoryOptions) }},
    selectedCategoryId: {{ json_encode($isEdit ? $listing->main_category_id : null) }},
    selectedCategoryPath: {{ json_encode($isEdit && $listing->mainCategory ? $listing->mainCategory->getBreadcrumbPath() : '') }},
    step1Url: '{{ route('supplier.catalog.listings.wizard.step1') }}',
    step2Url: '{{ $isEdit ? route('supplier.catalog.listings.wizard.step2', $listing) : '' }}',
    step3Url: '{{ $isEdit ? route('supplier.catalog.listings.wizard.step3', $listing) : '' }}',
    step4Url: '{{ $isEdit ? route('supplier.catalog.listings.wizard.step4', $listing) : '' }}',
    previewUrl: '{{ $isEdit ? route('supplier.catalog.listings.show', $listing) : '' }}',
    setCoverUrl: '{{ $isEdit ? route('supplier.catalog.listings.media.primary', $listing) : '' }}',
    uploadMediaUrl: '{{ $isEdit ? route('supplier.catalog.listings.media.store', $listing) : '' }}',
    lastSavedTime: {{ json_encode($isEdit && $listing->last_autosaved_at ? $listing->last_autosaved_at->toISOString() : null) }},
    currencyCode: {{ json_encode($isEdit ? ($listing->currency_code ?: 'USD') : 'USD') }},
    unitId: {{ json_encode($isEdit ? ($listing->unit_id ? (string)$listing->unit_id : '') : '') }}
})"
x-init="init()"
class="space-y-6">

    {{-- Compact Tab-Style Stepper (matches the Admin Category Builder pattern) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-200">
        <nav class="flex gap-6 overflow-x-auto -mb-px">
            <template x-for="step in stepDefs" :key="step.num">
                <button type="button" @click="goToStep(step.num)"
                        class="py-3 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="currentStep === step.num
                            ? 'text-indigo-600 border-indigo-600'
                            : (maxCompletedStep >= step.num || step.num === 1 ? 'text-gray-500 border-transparent hover:text-gray-700' : 'text-gray-300 border-transparent cursor-not-allowed')">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0"
                          :class="maxCompletedStep > step.num ? 'bg-emerald-500 text-white' : (currentStep === step.num ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400')">
                        <i class="fa-solid fa-check" x-show="maxCompletedStep > step.num" x-cloak></i>
                        <span x-show="maxCompletedStep <= step.num" x-text="step.num"></span>
                    </span>
                    <span x-text="step.label"></span>
                </button>
            </template>
        </nav>

        {{-- Draft/save status — deliberately separate from the stepper itself --}}
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-50 border border-gray-200 mb-2 shrink-0">
            <span class="w-2 h-2 rounded-full" :class="isSaving ? 'bg-amber-400 animate-ping' : (lastSavedTime ? 'bg-emerald-500' : 'bg-gray-300')"></span>
            <span x-text="isSaving ? 'Saving changes...' : (lastSavedTime ? 'Draft saved at ' + formatSavedTime(lastSavedTime) : 'Draft not saved yet')"></span>
        </div>
    </div>

    {{-- Error Banner --}}
    <div x-show="errorMessage" x-cloak class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
        <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-red-800">Please correct the following:</h4>
            <p class="text-xs text-red-700 mt-0.5" x-text="errorMessage"></p>
        </div>
        <button type="button" @click="errorMessage = ''" class="text-red-400 hover:text-red-600"><i class="fa-solid fa-xmark"></i></button>
    </div>

    {{-- Service listings pre-date the "Coming Soon" gate on new creation — this notice keeps existing suppliers oriented while editing them. --}}
    <div x-show="listingType == '{{ $listingTypes->firstWhere('code','service')?->id ?? '' }}' && currentStep > 1" x-cloak class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-center gap-2.5 text-xs text-amber-800">
        <i class="fa-solid fa-circle-info text-amber-600"></i>
        <span>The dedicated service-listing workflow is still being finalized — some fields below are tailored for physical products.</span>
    </div>

    {{-- STEP 1: BASICS & MEDIA --}}
    <div x-show="currentStep === 1" x-cloak>
        <form @submit.prevent="submitStep1" id="step1Form" class="space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                {{-- Left Column: Basics (8/12) --}}
                <div class="xl:col-span-8 space-y-6 flex flex-col">
                    <x-backend.form-card title="Listing Type & Basic Details" class="flex flex-col xl:max-h-[70vh]">
                        <div class="space-y-5 flex-1 min-h-0 overflow-y-auto pr-1">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2">What type of offering are you adding? <span class="text-red-500">*</span></label>
                                {{-- Listing Type Radios — loaded from listing_types table --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @if(isset($listingTypes) && $listingTypes->isNotEmpty())
                                        @foreach($listingTypes as $lt)
                                        @php
                                            $isService = $lt->code === 'service';
                                            $isProductType = $lt->code === 'product';
                                        @endphp
                                        <label class="flex items-start gap-3 p-3.5 border-2 rounded-xl transition-all"
                                               :class="[listingType == '{{ $lt->id }}' ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-500' : 'border-gray-200 bg-white hover:bg-gray-50',
                                                        {{ $isService ? "!wasServiceOnLoad ? 'opacity-70 cursor-not-allowed' : 'cursor-pointer'" : "'cursor-pointer'" }}].join(' ')">
                                            <input type="radio" name="listing_type_id" value="{{ $lt->id }}"
                                                   x-model="listingType"
                                                   {{ $isService ? ':disabled="!wasServiceOnLoad"' : '' }}
                                                   class="mt-1" style="accent-color:var(--theme-primary)">
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                                    {{ $lt->name }}
                                                    @if($isService)<span x-show="!wasServiceOnLoad" class="text-[10px] font-bold uppercase tracking-wide text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">Coming Soon</span>@endif
                                                </p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $lt->description }}</p>
                                            </div>
                                        </label>
                                        @endforeach
                                    @else
                                        <p class="text-xs text-gray-400 col-span-2">No listing types configured. Ask your admin to add them.</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Title / Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="e.g. Digital Microscope Pro 4K with LED Stage"
                                       value="{{ $isEdit ? $listing->name : old('name') }}"
                                       class="focus-accent w-full px-3.5 py-2.5 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400"
                                       :class="fieldErrors['name'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                                <p x-show="fieldErrors['name']" x-text="fieldErrors['name'] ? fieldErrors['name'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand / Manufacturer (optional)</label>
                                    <select name="brand_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus-accent"
                                            :class="fieldErrors['brand_id'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                                        <option value="">None / Unbranded</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" @selected($isEdit && $listing->brand_id == $brand->id)>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    <p x-show="fieldErrors['brand_id']" x-text="fieldErrors['brand_id'] ? fieldErrors['brand_id'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU / Model Number</label>
                                    <input type="text" name="sku" placeholder="e.g. MIC-4K-2026"
                                           value="{{ $isEdit ? $listing->sku : old('sku') }}"
                                           class="focus-accent w-full px-3.5 py-2.5 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400"
                                           :class="fieldErrors['sku'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                                    <p x-show="fieldErrors['sku']" x-text="fieldErrors['sku'] ? fieldErrors['sku'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                                    <p x-show="!fieldErrors['sku']" class="text-[11px] text-gray-400 mt-1">Unique catalog code within your supplier account.</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Short Summary</label>
                                <textarea name="short_description" rows="2" placeholder="1-2 sentences highlighting core features and suitability."
                                          class="focus-accent w-full px-3.5 py-2 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400"
                                          :class="fieldErrors['short_description'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">{{ $isEdit ? $listing->short_description : old('short_description') }}</textarea>
                                <p x-show="fieldErrors['short_description']" x-text="fieldErrors['short_description'] ? fieldErrors['short_description'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Technical & Detailed Description</label>
                                <textarea name="description" rows="4" placeholder="Full technical overview, package contents, usage guidelines..."
                                          class="focus-accent w-full px-3.5 py-2 border rounded-lg text-sm text-gray-900 placeholder:text-gray-400"
                                          :class="fieldErrors['description'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">{{ $isEdit ? $listing->description : old('description') }}</textarea>
                                <p x-show="fieldErrors['description']" x-text="fieldErrors['description'] ? fieldErrors['description'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                            </div>
                        </div>
                    </x-backend.form-card>
                </div>

                {{-- Right Column: Media Gallery (4/12) --}}
                <div class="xl:col-span-4 space-y-6 flex flex-col">
                    <x-backend.form-card title="Product Photos & Gallery" class="flex flex-col xl:max-h-[70vh]">
                        <p class="text-xs text-gray-500 mb-3 shrink-0">Upload multiple images (cover photo, side angles, close-ups, packaging). PNG, JPG, WebP up to 10MB each.</p>

                        {{-- Drag and drop upload box --}}
                        <div class="border-2 border-dashed border-indigo-200 bg-indigo-50/20 rounded-2xl p-6 text-center hover:border-indigo-400 hover:bg-indigo-50/40 transition-all cursor-pointer relative shrink-0"
                             :class="(fieldErrors['images'] || fieldErrors['images.0']) ? 'border-red-400 bg-red-50/30' : ''"
                             @dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50/60')"
                             @dragleave.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50/60')"
                             @drop.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50/60'); handleFileDrop($event)">
                            <div class="w-12 h-12 bg-white rounded-full border border-indigo-100 flex items-center justify-center mx-auto mb-2.5 text-indigo-600 shadow-2xs">
                                <i class="fa-solid fa-images text-lg"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-800">Drag & drop multiple product images</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">or click to browse from your device</p>
                            <input type="file" id="imageInput" name="images[]" multiple accept="image/*" @change="handleFileSelect($event)"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </div>
                        <p x-show="fieldErrors['images'] || fieldErrors['images.0']" x-text="fieldErrors['images'] ? fieldErrors['images'][0] : (fieldErrors['images.0'] ? fieldErrors['images.0'][0] : '')" class="text-xs text-red-600 mt-1 font-semibold"></p>

                        {{-- Preview Count and Instructions --}}
                        <div class="flex items-center justify-between text-xs pt-2 shrink-0">
                            <span class="font-bold text-gray-700" x-text="(mediaItems.length + pendingFiles.length) + ' photo(s) selected'"></span>
                            <span class="text-[11px] text-gray-400">Click ★ to set main cover photo</span>
                        </div>

                        {{-- Image Cards Grid — internally scrollable, independent of the left panel --}}
                        <div class="mt-2 space-y-2.5 flex-1 min-h-0 overflow-y-auto pr-1">
                            {{-- Already Uploaded Media --}}
                            <template x-for="(media, index) in mediaItems" :key="'uploaded_' + media.id">
                                <div class="flex items-center gap-3 p-2.5 rounded-xl border bg-white shadow-2xs transition-all"
                                     :class="media.is_primary ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-500' : 'border-gray-200 hover:border-gray-300'">
                                    <img :src="media.url" class="w-12 h-12 rounded-lg object-cover border border-gray-200 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-900 truncate" x-text="media.file_name"></p>
                                        <span x-show="media.is_primary" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 mt-0.5">
                                            <i class="fa-solid fa-star text-amber-500"></i> Main Cover Photo
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" x-show="!media.is_primary" @click="setAsCover(media.id)" title="Set as Main Cover"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-indigo-600 text-xs transition">
                                            <i class="fa-regular fa-star"></i>
                                        </button>
                                        <button type="button" @click="deleteMedia(media.id, index)" title="Delete image"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-600 text-xs transition">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            {{-- Pending Local Files (Newly selected before save) --}}
                            <template x-for="(file, fIdx) in pendingFiles" :key="'pending_' + fIdx">
                                <div class="flex items-center gap-3 p-2.5 rounded-xl border border-dashed border-indigo-300 bg-indigo-50/20 shadow-2xs">
                                    <img :src="file.previewUrl" class="w-12 h-12 rounded-lg object-cover border border-indigo-200 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-900 truncate" x-text="file.name"></p>
                                        <span class="text-[10px] text-gray-400" x-text="file.formattedSize"></span>
                                        <span x-show="file.isCover" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 block">
                                            <i class="fa-solid fa-star text-amber-500"></i> Selected as Cover
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="setPendingCover(fIdx)" title="Set as Cover"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-xs"
                                                :class="file.isCover ? 'text-amber-500 bg-amber-50' : 'text-gray-400 hover:text-indigo-600'">
                                            <i :class="file.isCover ? 'fa-solid fa-star' : 'fa-regular fa-star'"></i>
                                        </button>
                                        <button type="button" @click="removePendingFile(fIdx)" title="Remove"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-600 text-xs">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </x-backend.form-card>
                </div>
            </div>

            {{-- Step 1 Sticky Action Bar --}}
            <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                <span class="text-xs text-gray-400">Step 1 of 4</span>
                <div class="flex items-center gap-3">
                    <button type="button" @click="saveDraftStep1()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Draft
                    </button>
                    <button type="submit" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                        <span>Save & Continue to Specs</span> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- STEP 2: CATEGORY & SPECIFICATIONS --}}
    <div x-show="currentStep === 2" x-cloak>
        <form @submit.prevent="submitStep2" id="step2Form" class="space-y-6">
            {{-- Hidden input for main_category_id --}}
            <input type="hidden" name="main_category_id" :value="selectedCategoryId">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:items-stretch">
                {{-- LEFT COLUMN: Searchable Category Tree (5/12) — the category selector --}}
                <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs flex flex-col lg:max-h-[70vh]">
                    <div class="flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Categories</h3>
                            <p class="text-[11px] text-gray-500">Pick a primary category for this listing</p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600" x-text="categoryNodes.length + ' total'"></span>
                    </div>

                    {{-- Category Search Input --}}
                    <div class="relative mt-4 shrink-0">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="categorySearch" @input="catPage = 1" placeholder="Search categories..."
                               class="w-full text-xs rounded-xl border border-gray-300 pl-8 pr-3 py-2 bg-gray-50/50 focus-accent transition">
                    </div>

                    {{-- Tree List — internally scrollable, independent of the right panel and the sticky footer --}}
                    <div class="space-y-1 flex-1 min-h-0 overflow-y-auto pr-1 mt-3">
                        <template x-for="node in pagedCategoryNodes" :key="node.id">
                            <button type="button" @click="selectCategoryNode(node)"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-left text-xs border transition-all"
                                    :class="selectedCategoryId == node.id ? 'border-indigo-500 bg-indigo-50/60 shadow-2xs ring-1 ring-indigo-500' : 'border-transparent hover:bg-gray-50'"
                                    :style="'padding-left:' + (12 + node.depth * 16) + 'px'"
                                    :title="node.path">
                                <span class="flex items-center gap-2 min-w-0">
                                    <i class="fa-solid text-[10px]"
                                       :class="node.depth > 0 ? 'fa-turn-up fa-rotate-90 text-gray-300' : 'fa-folder text-indigo-400 text-xs'"></i>
                                    <span class="min-w-0">
                                        <span class="block truncate" :class="selectedCategoryId == node.id ? 'font-bold text-indigo-900' : 'font-medium text-gray-800'" x-text="node.name"></span>
                                        <span x-show="node.depth > 0" class="block truncate text-[10px] text-gray-400" x-text="node.path"></span>
                                    </span>
                                </span>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span x-show="!node.has_children" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md text-emerald-700 bg-emerald-50 border border-emerald-100">Selectable</span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md"
                                          :class="node.attributes_count > 0 ? 'text-indigo-700 bg-indigo-50 border border-indigo-100' : 'text-gray-400 bg-gray-50 border border-gray-200'"
                                          x-text="node.attributes_count + ' specs'"></span>
                                    <i x-show="selectedCategoryId == node.id" class="fa-solid fa-check text-xs text-indigo-600"></i>
                                </div>
                            </button>
                        </template>

                        <p x-show="filteredCategoryNodes.length === 0" class="text-xs text-gray-400 text-center py-8">
                            No categories match "<span x-text="categorySearch"></span>".
                        </p>
                    </div>

                    {{-- Category Tree Pagination (design.md §18.1 client-side pagination pattern) --}}
                    <div x-show="filteredCategoryNodes.length > 0" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-3 mt-1 border-t border-gray-100 text-xs shrink-0">
                        <p class="text-gray-500">
                            Showing <span class="font-medium text-gray-700" x-text="catPageStart"></span>
                            to <span class="font-medium text-gray-700" x-text="catPageEnd"></span>
                            of <span class="font-medium text-gray-700" x-text="filteredCategoryNodes.length"></span> entries
                        </p>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="catPage = Math.max(1, catPage - 1)" :disabled="catPage === 1"
                                    class="text-xs font-medium px-2.5 py-1 rounded-lg border"
                                    :class="catPage === 1 ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
                                Previous
                            </button>
                            <template x-for="p in Array.from({ length: totalCatPages }, (_, i) => i + 1)" :key="p">
                                <button type="button" @click="catPage = p"
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg"
                                        :class="p === catPage ? 'text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50'"
                                        :style="p === catPage ? 'background:var(--theme-primary)' : ''">
                                    <span x-text="p"></span>
                                </button>
                            </template>
                            <button type="button" @click="catPage = Math.min(totalCatPages, catPage + 1)" :disabled="catPage === totalCatPages"
                                    class="text-xs font-medium px-2.5 py-1 rounded-lg border"
                                    :class="catPage === totalCatPages ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Dynamic Technical Specifications & Attributes (7/12) --}}
                <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200 p-5 shadow-2xs flex flex-col lg:max-h-[70vh]">
                    {{-- Selected Category Header --}}
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Technical Specifications</h4>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <span x-show="selectedCategoryPath" x-text="'Category: ' + selectedCategoryPath"></span>
                                <span x-show="!selectedCategoryPath">Select a category on the left to load its specification fields</span>
                            </p>
                            <p x-show="fieldErrors['main_category_id']" x-text="fieldErrors['main_category_id'] ? fieldErrors['main_category_id'][0] : ''" class="text-xs text-red-600 font-semibold mt-1"></p>
                            <p x-show="fieldErrors['attributes']" x-text="fieldErrors['attributes'] ? fieldErrors['attributes'][0] : ''" class="text-xs text-red-600 font-semibold mt-1"></p>
                        </div>
                        <span x-show="selectedCategoryId" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                            <i class="fa-solid fa-circle-check"></i> Category Active
                        </span>
                    </div>

                    {{-- Dynamic Attributes Form Component — internally scrollable, independent of the left panel --}}
                    <div class="flex-1 min-h-0 overflow-y-auto pr-1 pt-4">
                        @include('backend.supplier.catalog.listings.partials.attributes-form', [
                            'initialCategoryId' => $isEdit ? $listing->main_category_id : null,
                            'initialValues' => $isEdit && isset($existingValues) ? $existingValues : [],
                        ])
                    </div>
                </div>
            </div>

            {{-- Step 2 Sticky Action Bar --}}
            <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                <button type="button" @click="goToStep(1)" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Previous Step
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" @click="saveDraftStep2()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Draft
                    </button>
                    <button type="submit" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                        <span>Save & Continue to Pricing</span> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- STEP 3: PRICING, MOQ & INVENTORY --}}
    <div x-show="currentStep === 3" x-cloak>
        <form @submit.prevent="submitStep3" id="step3Form" class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-4 sm:p-5 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Pricing & Commercial Terms</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Define your wholesale pricing model, currency, and volume tiers.</p>
                </div>

                {{-- Pricing Model Radios — loaded from pricing_types table --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    @if(isset($pricingTypes) && $pricingTypes->isNotEmpty())
                        @foreach($pricingTypes as $pt)
                        <label class="p-2.5 border rounded-lg cursor-pointer transition-all flex items-start gap-2"
                               :class="pricingType == '{{ $pt->id }}' ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-500' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" name="pricing_type_id" value="{{ $pt->id }}" x-model="pricingType" class="mt-0.5" style="accent-color:var(--theme-primary)">
                            <span>
                                <span class="block text-xs font-bold text-gray-900">{{ $pt->name }}</span>
                                <span class="block text-[11px] text-gray-500 mt-0.5">{{ $pt->description }}</span>
                            </span>
                        </label>
                        @endforeach
                    @else
                        <p class="text-xs text-gray-400 col-span-3">No pricing types configured. Ask your admin to add them.</p>
                    @endif
                </div>

                {{-- Price & Currency Fields --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-gray-100">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Currency <span class="text-red-500">*</span></label>
                        <select name="currency_code" required x-model="currencyCode"
                                class="focus-accent w-full px-3 py-1.5 border rounded-lg text-sm text-gray-900 bg-white"
                                :class="fieldErrors['currency_code'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                            @if(isset($currencies) && count($currencies))
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr->code }}">{{ $curr->code }} - {{ $curr->name }} ({{ $curr->symbol }})</option>
                                @endforeach
                            @else
                                <option value="USD">USD - US Dollar ($)</option>
                                <option value="BDT">BDT - Bangladeshi Taka (৳)</option>
                                <option value="EUR">EUR - Euro (€)</option>
                                <option value="GBP">GBP - British Pound (£)</option>
                            @endif
                            <option value="__other__">+ Other (Custom Currency)...</option>
                        </select>
                        <p x-show="fieldErrors['currency_code']" x-text="fieldErrors['currency_code'] ? fieldErrors['currency_code'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>

                        {{-- Custom Currency Inputs --}}
                        <div x-show="currencyCode === '__other__'" x-cloak class="mt-2.5 p-2.5 rounded-lg bg-indigo-50/60 border border-indigo-200 space-y-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-indigo-900">Custom Currency Code <span class="text-red-500">*</span></label>
                                <input type="text" name="custom_currency_code" maxlength="3" placeholder="e.g. QAR, KWD" x-model="customCurrencyCode"
                                       class="w-full text-xs uppercase rounded-md border border-indigo-300 px-2.5 py-1 bg-white font-mono"
                                       :class="fieldErrors['custom_currency_code'] ? 'border-red-500 ring-1 ring-red-500' : ''">
                                <p x-show="fieldErrors['custom_currency_code']" x-text="fieldErrors['custom_currency_code'] ? fieldErrors['custom_currency_code'][0] : ''" class="text-[10px] text-red-600 font-semibold mt-0.5"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] text-gray-600">Name (Optional)</label>
                                    <input type="text" name="custom_currency_name" placeholder="e.g. Qatari Riyal" x-model="customCurrencyName"
                                           class="w-full text-xs rounded-md border border-gray-300 px-2 py-1 bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-600">Symbol (Optional)</label>
                                    <input type="text" name="custom_currency_symbol" placeholder="e.g. ر.ق" x-model="customCurrencySymbol"
                                           class="w-full text-xs rounded-md border border-gray-300 px-2 py-1 bg-white">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Base Unit Price</label>
                        <input type="number" step="0.01" name="base_price" placeholder="0.00" value="{{ $isEdit ? $listing->base_price : '' }}"
                               class="focus-accent w-full px-3 py-1.5 border rounded-lg text-sm text-gray-900"
                                :class="fieldErrors['base_price'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                        <p x-show="fieldErrors['base_price']" x-text="fieldErrors['base_price'] ? fieldErrors['base_price'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Compare Price (Strike-through)</label>
                        <input type="number" step="0.01" name="compare_at_price" placeholder="0.00" value="{{ $isEdit ? $listing->compare_at_price : '' }}"
                               class="focus-accent w-full px-3 py-1.5 border rounded-lg text-sm text-gray-900"
                                :class="fieldErrors['compare_at_price'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                        <p x-show="fieldErrors['compare_at_price']" x-text="fieldErrors['compare_at_price'] ? fieldErrors['compare_at_price'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Minimum Order Quantity (MOQ)</label>
                        <input type="number" name="min_order_quantity" min="1" value="{{ $isEdit ? $listing->min_order_quantity : '1' }}"
                               class="focus-accent w-full px-3 py-1.5 border rounded-lg text-sm text-gray-900"
                               :class="fieldErrors['min_order_quantity'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                        <p x-show="fieldErrors['min_order_quantity']" x-text="fieldErrors['min_order_quantity'] ? fieldErrors['min_order_quantity'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Packaging / Pricing Unit</label>
                        <select name="unit_id" x-model="unitId"
                                class="w-full text-sm rounded-lg border border-gray-300 px-3 py-1.5 bg-white focus-accent"
                                :class="fieldErrors['unit_id'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                            <option value="">Standard Piece / Item</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                            @endforeach
                            <option value="__other__">+ Other (Specify Custom Unit)...</option>
                        </select>
                        <p x-show="fieldErrors['unit_id']" x-text="fieldErrors['unit_id'] ? fieldErrors['unit_id'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>

                        {{-- Custom Unit Inputs --}}
                        <div x-show="unitId === '__other__'" x-cloak class="mt-2.5 p-2.5 rounded-lg bg-indigo-50/60 border border-indigo-200 space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-semibold text-indigo-900">Custom Unit Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="custom_unit_name" placeholder="e.g. Carton, Bundle" x-model="customUnitName"
                                           class="w-full text-xs rounded-md border border-indigo-300 px-2.5 py-1 bg-white"
                                           :class="fieldErrors['custom_unit_name'] ? 'border-red-500 ring-1 ring-red-500' : ''">
                                    <p x-show="fieldErrors['custom_unit_name']" x-text="fieldErrors['custom_unit_name'] ? fieldErrors['custom_unit_name'][0] : ''" class="text-[10px] text-red-600 font-semibold mt-0.5"></p>
                                </div>
                                <div>
                                    <label class="block text-[11px] text-gray-700">Symbol (Optional)</label>
                                    <input type="text" name="custom_unit_symbol" placeholder="e.g. ctn, bdl" x-model="customUnitSymbol"
                                           class="w-full text-xs rounded-md border border-gray-300 px-2.5 py-1 bg-white">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Volume Tier Price Builder --}}
                <div class="pt-3 border-t border-gray-100 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            {{-- Compact toggle switch --}}
                            <span class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors"
                                  :style="hasTierPricing ? 'background:var(--theme-primary)' : 'background:#d1d5db'">
                                <input type="checkbox" name="has_tier_pricing" value="1" x-model="hasTierPricing" class="peer sr-only">
                                <span class="inline-block h-3.5 w-3.5 rounded-full bg-white transition-transform" :style="hasTierPricing ? 'transform: translateX(1.125rem)' : 'transform: translateX(0.25rem)'"></span>
                            </span>
                            <span class="text-xs font-semibold text-gray-900">Enable Volume Pricing</span>
                        </label>
                        <button type="button" x-show="hasTierPricing" @click="addTier()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> Add Tier
                        </button>
                    </div>

                    <div x-show="hasTierPricing" x-cloak class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="text-left font-semibold px-3 py-1.5">Min Qty</th>
                                    <th class="text-left font-semibold px-3 py-1.5">Max Qty</th>
                                    <th class="text-left font-semibold px-3 py-1.5">Unit Price</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(tier, tIndex) in tierPrices" :key="tIndex">
                                    <tr>
                                        <td class="px-3 py-1.5">
                                            <input type="number" min="1" :name="'tiers[' + tIndex + '][min_quantity]'" x-model="tier.min_quantity" :disabled="!hasTierPricing" placeholder="e.g. 50"
                                                   class="w-full text-xs rounded-md border px-2 py-1 bg-white"
                                                   :class="fieldErrors['tiers.' + tIndex + '.min_quantity'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                                            <p x-show="fieldErrors['tiers.' + tIndex + '.min_quantity']" x-text="fieldErrors['tiers.' + tIndex + '.min_quantity'] ? fieldErrors['tiers.' + tIndex + '.min_quantity'][0] : ''" class="text-[10px] text-red-600 mt-0.5 font-semibold"></p>
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="number" min="1" :name="'tiers[' + tIndex + '][max_quantity]'" x-model="tier.max_quantity" :disabled="!hasTierPricing" placeholder="No limit"
                                                   class="w-full text-xs rounded-md border px-2 py-1 bg-white"
                                                   :class="fieldErrors['tiers.' + tIndex + '.max_quantity'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                                            <p x-show="fieldErrors['tiers.' + tIndex + '.max_quantity']" x-text="fieldErrors['tiers.' + tIndex + '.max_quantity'] ? fieldErrors['tiers.' + tIndex + '.max_quantity'][0] : ''" class="text-[10px] text-red-600 mt-0.5 font-semibold"></p>
                                        </td>
                                        <td class="px-3 py-1.5">
                                            <input type="number" step="0.01" :name="'tiers[' + tIndex + '][unit_price]'" x-model="tier.unit_price" :disabled="!hasTierPricing" placeholder="0.00"
                                                   class="w-full text-xs rounded-md border px-2 py-1 bg-white font-semibold text-indigo-700"
                                                   :class="fieldErrors['tiers.' + tIndex + '.unit_price'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                                            <p x-show="fieldErrors['tiers.' + tIndex + '.unit_price']" x-text="fieldErrors['tiers.' + tIndex + '.unit_price'] ? fieldErrors['tiers.' + tIndex + '.unit_price'][0] : ''" class="text-[10px] text-red-600 mt-0.5 font-semibold"></p>
                                        </td>
                                        <td class="px-2 py-1.5 text-right">
                                            <button type="button" @click="removeTier(tIndex)" class="w-6 h-6 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 inline-flex items-center justify-center text-xs">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Product Inventory Details --}}
                <div x-show="listingType === 'product'" class="pt-3 border-t border-gray-100 space-y-2.5">
                    <h4 class="text-xs font-bold text-gray-900">Inventory & Lead Time</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Stock Status</label>
                            <select name="stock_status" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-1.5 bg-white"
                                    :class="fieldErrors['stock_status'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                                <option value="in_stock" @selected($isEdit && $listing->productDetail?->stock_status === 'in_stock')>In Stock</option>
                                <option value="on_request" @selected($isEdit && $listing->productDetail?->stock_status === 'on_request')>On Request / Made to Order</option>
                                <option value="limited" @selected($isEdit && $listing->productDetail?->stock_status === 'limited')>Limited Availability</option>
                                <option value="out_of_stock" @selected($isEdit && $listing->productDetail?->stock_status === 'out_of_stock')>Out of Stock</option>
                            </select>
                            <p x-show="fieldErrors['stock_status']" x-text="fieldErrors['stock_status'] ? fieldErrors['stock_status'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Available Stock Qty</label>
                            <input type="number" name="stock_quantity" placeholder="0" value="{{ $isEdit ? $listing->productDetail?->stock_quantity : '' }}"
                                   class="focus-accent w-full px-3 py-1.5 border rounded-lg text-sm"
                                   :class="fieldErrors['stock_quantity'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                            <p x-show="fieldErrors['stock_quantity']" x-text="fieldErrors['stock_quantity'] ? fieldErrors['stock_quantity'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Lead Time (Days)</label>
                            <input type="number" name="lead_time_days" placeholder="e.g. 5" value="{{ $isEdit ? $listing->productDetail?->lead_time_days : '' }}"
                                   class="focus-accent w-full px-3 py-1.5 border rounded-lg text-sm"
                                   :class="fieldErrors['lead_time_days'] ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'">
                            <p x-show="fieldErrors['lead_time_days']" x-text="fieldErrors['lead_time_days'] ? fieldErrors['lead_time_days'][0] : ''" class="text-xs text-red-600 mt-1 font-semibold"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3 Sticky Action Bar --}}
            <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                <button type="button" @click="goToStep(2)" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Previous Step
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" @click="saveDraftStep3()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save Draft
                    </button>
                    <button type="submit" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                        <span>Save & Continue to Review</span> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- STEP 4: VARIANTS & FINAL REVIEW / SUBMISSION --}}
    <div x-show="currentStep === 4" x-cloak>
        <form @submit.prevent="submitStep4" id="step4Form" class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Variants & Review</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Configure individual SKU variations (if applicable) and verify your listing details before submitting for approval.</p>
                </div>

                {{-- Variant Management Section (for Products) --}}
                <div x-show="listingType === 'product'" class="p-5 bg-gray-50/70 border border-gray-200 rounded-2xl space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-gray-500 max-w-lg">Configure distinct SKU variations (different colors, sizes, storage, or models) with individual pricing & photos.</p>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="saveVariationsOnly()" :disabled="isSaving || variants.length === 0"
                                    class="px-3.5 py-2 rounded-xl border text-xs font-bold flex items-center gap-1.5 shadow-2xs transition"
                                    :class="variants.length > 0 ? 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 cursor-pointer' : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed'">
                                <i class="fa-solid" :class="isSaving ? 'fa-circle-notch fa-spin' : 'fa-floppy-disk'"></i>
                                <span>Save Variations</span>
                            </button>
                            <button type="button" @click="openGeneratorModal()"
                                    class="px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold flex items-center gap-1.5 shadow-2xs transition">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                <span>Auto-Generate Combinations</span>
                            </button>
                            <button type="button" @click="openCreateVariantModal()"
                                    class="btn-primary text-xs font-semibold px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-2xs">
                                <i class="fa-solid fa-plus"></i>
                                <span>Add Single Variant</span>
                            </button>
                        </div>
                    </div>

                    {{-- Toast for variations saved --}}
                    <div x-show="variationSavedToast" x-cloak class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span x-text="'Saved ' + variants.length + ' variant(s) and photo assignments successfully!'"></span>
                    </div>

                    {{-- Notice for duplicate combinations skipped by the generator --}}
                    <div x-show="generatorSkippedMessage" x-cloak class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                        <span x-text="generatorSkippedMessage"></span>
                    </div>

                    {{-- Variant Search --}}
                    <div x-show="variants.length > 0" class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="variantSearch" @input="variantPage = 1" placeholder="Search variants by name or SKU..."
                               class="w-full sm:w-72 text-xs rounded-xl border border-gray-300 pl-8 pr-3 py-2 bg-white focus-accent transition">
                    </div>

                    {{-- Variants Table — read-only summary rows; editing happens in the modal so 100+ rows never render as live inputs --}}
                    <div x-show="variants.length > 0" class="overflow-x-auto border border-gray-200 rounded-xl bg-white shadow-2xs">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50/90 text-gray-700 font-semibold border-b border-gray-200 text-[11px]">
                                <tr>
                                    <th class="px-3.5 py-3 w-16">Photo</th>
                                    <th class="px-3.5 py-3">Variant Details</th>
                                    <th class="px-3.5 py-3">SKU</th>
                                    <th class="px-3.5 py-3">Price</th>
                                    <th class="px-3.5 py-3">Stock</th>
                                    <th class="px-3.5 py-3">Status</th>
                                    <th class="px-3.5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="v in pagedVariants" :key="v.__idx">
                                    <tr class="hover:bg-gray-50/60 transition cursor-pointer" @click="openEditVariantModal(v.__idx)">
                                        {{-- Image Thumbnail — each variant's own photo, distinct per row; quick-launches the photo picker without opening the full edit modal --}}
                                        <td class="px-3.5 py-2.5 align-middle" @click.stop="openImagePickerForVariant(v.__idx)">
                                            <div class="relative cursor-pointer group" :title="v.primary_image_media_id ? 'Manage variant photos' : 'No photo set for this variant — click to add one'">
                                                <img :src="getVariantImageUrl(v.primary_image_media_id)"
                                                     class="w-11 h-11 rounded-lg object-cover shadow-2xs"
                                                     :class="v.primary_image_media_id ? 'border border-gray-200 bg-gray-50' : 'border-2 border-dashed border-gray-300 bg-gray-50'">
                                                <div class="absolute inset-0 bg-black/40 text-white rounded-lg opacity-0 group-hover:opacity-100 flex items-center justify-center text-[10px] transition font-bold">
                                                    <i class="fa-solid fa-images"></i>
                                                </div>
                                                <span x-show="v.image_media_ids && v.image_media_ids.length > 1"
                                                      class="absolute -bottom-1 -right-1 text-[9px] font-bold bg-gray-900/80 text-white rounded-full w-4 h-4 flex items-center justify-center"
                                                      x-text="v.image_media_ids.length"></span>
                                            </div>
                                            <span x-show="!v.primary_image_media_id" class="block mt-1 text-center text-[9px] font-semibold text-gray-400 uppercase tracking-wide">No photo</span>
                                        </td>

                                        {{-- Variant Name & Attribute Badges --}}
                                        <td class="px-3.5 py-2.5">
                                            <p class="font-bold text-gray-900 text-xs" x-text="v.name"></p>
                                            <div class="flex flex-wrap gap-1 mt-1" x-show="v.attribute_chips && v.attribute_chips.length > 0">
                                                <template x-for="chip in v.attribute_chips" :key="chip.name">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">
                                                        <span class="text-gray-400 mr-1" x-text="chip.name + ':'"></span>
                                                        <strong x-text="chip.value"></strong>
                                                    </span>
                                                </template>
                                            </div>
                                        </td>

                                        {{-- SKU --}}
                                        <td class="px-3.5 py-2.5 font-mono text-gray-700" x-text="v.sku || '—'"></td>

                                        {{-- Price & Tier Pricing Pill — pill quick-launches the tier modal --}}
                                        <td class="px-3.5 py-2.5">
                                            <span class="font-bold text-indigo-700" x-text="v.price ? '$' + Number(v.price).toFixed(2) : '—'"></span>
                                            <button type="button" @click.stop="openTierModalForVariant(v.__idx)"
                                                    class="ml-1.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold border transition"
                                                    :class="(v.tier_prices && v.tier_prices.length > 0) ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100'">
                                                <i class="fa-solid fa-layer-group"></i>
                                                <span x-text="(v.tier_prices && v.tier_prices.length > 0) ? v.tier_prices.length + ' tiers' : '+ tier'"></span>
                                            </button>
                                        </td>

                                        {{-- Stock Quantity --}}
                                        <td class="px-3.5 py-2.5 text-gray-700" x-text="v.stock_quantity ?? 0"></td>

                                        {{-- Stock Status --}}
                                        <td class="px-3.5 py-2.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold capitalize"
                                                  :class="v.stock_status === 'in_stock' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                                                  x-text="(v.stock_status || 'in_stock').replace('_', ' ')"></span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-3.5 py-2.5 text-right" @click.stop>
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" @click="openEditVariantModal(v.__idx)" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-gray-100 rounded-lg text-xs transition" title="Edit Variant">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button type="button" @click="removeVariant(v.__idx)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg text-xs transition" title="Delete Variant">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <p x-show="variants.length > 0 && filteredVariants.length === 0" class="text-xs text-gray-400 text-center py-8">
                            No variants match "<span x-text="variantSearch"></span>".
                        </p>

                        {{-- Variant Table Pagination (design.md §18.1 client-side pagination pattern) --}}
                        <div x-show="filteredVariants.length > 0" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-3.5 py-3 border-t border-gray-100 text-xs">
                            <p class="text-gray-500">
                                Showing <span class="font-medium text-gray-700" x-text="variantPageStart"></span>
                                to <span class="font-medium text-gray-700" x-text="variantPageEnd"></span>
                                of <span class="font-medium text-gray-700" x-text="filteredVariants.length"></span> entries
                            </p>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="variantPage = Math.max(1, variantPage - 1)" :disabled="variantPage === 1"
                                        class="text-xs font-medium px-2.5 py-1 rounded-lg border"
                                        :class="variantPage === 1 ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
                                    Previous
                                </button>
                                <template x-for="p in Array.from({ length: totalVariantPages }, (_, i) => i + 1)" :key="p">
                                    <button type="button" @click="variantPage = p"
                                            class="text-xs font-semibold px-2.5 py-1 rounded-lg"
                                            :class="p === variantPage ? 'text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-50'"
                                            :style="p === variantPage ? 'background:var(--theme-primary)' : ''">
                                        <span x-text="p"></span>
                                    </button>
                                </template>
                                <button type="button" @click="variantPage = Math.min(totalVariantPages, variantPage + 1)" :disabled="variantPage === totalVariantPages"
                                        class="text-xs font-medium px-2.5 py-1 rounded-lg border"
                                        :class="variantPage === totalVariantPages ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>

                    <p x-show="variants.length === 0" class="text-xs text-gray-400 italic py-2">
                        No product variations created. If this item has multiple options (sizes, colors, models), click <strong>Auto-Generate Combinations</strong> or <strong>Add Single Variant</strong> above.
                    </p>
                </div>

                {{-- MODAL 1: Smart Auto-Generate Combinations Matrix Modal --}}
                <div x-show="generatorModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="generatorModalOpen = false"></div>
                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4">
                                {{-- Modal Header --}}
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-2xs">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-900">Smart Combination Generator</h3>
                                            <p class="text-xs text-gray-500">Calculate, filter, and selectively add matrix variations.</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="generatorModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                {{-- Tab Switcher --}}
                                <div class="flex items-center gap-2 border-b border-gray-200 pb-2 text-xs">
                                    <button type="button" @click="generatorViewMode = 'options'"
                                            class="px-3.5 py-1.5 rounded-lg font-semibold transition"
                                            :class="generatorViewMode === 'options' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-100'">
                                        1. Select Attribute Options
                                    </button>
                                    <button type="button" @click="generatorViewMode = 'specific'" :disabled="totalPossibleCombinationsCount === 0"
                                            class="px-3.5 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 disabled:opacity-50"
                                            :class="generatorViewMode === 'specific' ? 'bg-indigo-600 text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-100'">
                                        <span>2. Pick Specific Combinations</span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                                              :class="generatorViewMode === 'specific' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'"
                                              x-text="totalPossibleCombinationsCount"></span>
                                    </button>
                                </div>

                                {{-- TAB 1: Attribute Options Selection --}}
                                <div x-show="generatorViewMode === 'options'" class="space-y-4">
                                    <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                                        <div x-show="isGeneratorLoading" class="py-8 text-center text-xs text-gray-500">
                                            <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-lg mb-2"></i>
                                            <p>Loading category variation specifications...</p>
                                        </div>

                                        <template x-for="attr in activeVariantAttributes" :key="attr.id">
                                            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-xs font-bold text-gray-800" x-text="attr.name"></span>
                                                    <div class="flex items-center gap-2 text-[11px]">
                                                        <button type="button" @click="selectAllOptionsForAttribute(attr)" class="text-indigo-600 hover:underline font-semibold">Select all</button>
                                                        <span class="text-gray-300">|</span>
                                                        <button type="button" @click="clearOptionsForAttribute(attr)" class="text-gray-400 hover:text-gray-600 font-semibold">Clear</button>
                                                        <span class="text-gray-400 font-semibold ml-1" x-text="'(' + (selectedGeneratorOptions[attr.id] || []).length + ')'"></span>
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="opt in (attr.values || [])" :key="opt.id">
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

                                        <p x-show="!isGeneratorLoading && activeVariantAttributes.length === 0" class="text-xs text-gray-400 text-center py-6">
                                            No variant-enabled attributes found for this category. You can use <strong>Add Single Variant</strong> to create custom variations.
                                        </p>
                                    </div>

                                    {{-- Calculation Formula & Threshold Notification Box --}}
                                    <div class="space-y-2">
                                        {{-- Live Formula Box --}}
                                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-500 font-medium">Calculated Formula:</span>
                                                <strong class="text-gray-900" x-text="formulaText || 'Select options above'"></strong>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                                      :class="totalPossibleCombinationsCount === 0 ? 'bg-gray-100 text-gray-500' : (totalPossibleCombinationsCount <= 30 ? 'bg-emerald-100 text-emerald-800' : (totalPossibleCombinationsCount <= 100 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'))"
                                                      x-text="totalPossibleCombinationsCount + ' Combinations'"></span>
                                            </div>
                                        </div>

                                        {{-- Threshold State Banners --}}
                                        <template x-if="totalPossibleCombinationsCount > 0 && totalPossibleCombinationsCount <= 30">
                                            <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
                                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                                <span>Within direct generation limit (max 30). You can generate all or pick specific combinations.</span>
                                            </div>
                                        </template>

                                        <template x-if="totalPossibleCombinationsCount > 30 && totalPossibleCombinationsCount <= 100">
                                            <div class="p-3 bg-amber-50/80 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2">
                                                <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5"></i>
                                                <div>
                                                    <p class="font-bold">Large number of combinations (<span x-text="totalPossibleCombinationsCount"></span>)</p>
                                                    <p class="text-amber-800 mt-0.5">Most merchants only carry 10–25 active SKUs. We recommend using <strong>Pick Specific Combinations</strong> to add only the variants you actually sell.</p>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="totalPossibleCombinationsCount > 100">
                                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 flex items-start gap-2">
                                                <i class="fa-solid fa-circle-stop text-rose-600 mt-0.5 text-sm"></i>
                                                <div>
                                                    <p class="font-bold">Bulk generation disabled (<span x-text="totalPossibleCombinationsCount"></span> combinations)</p>
                                                    <p class="text-rose-800 mt-0.5">Generating over 100 combinations creates inventory clutter. Please refine your attributes above or click <strong>Pick Specific Combinations</strong> to select exact SKUs.</p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Matrix Defaults --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-gray-100 text-xs">
                                        <div>
                                            <label class="block font-medium text-gray-700 mb-1">Default Price</label>
                                            <input type="number" step="0.01" x-model="generatorDefaults.price" placeholder="0.00" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-gray-700 mb-1">Default Stock Qty</label>
                                            <input type="number" x-model="generatorDefaults.stock" placeholder="10" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-gray-700 mb-1">SKU Prefix</label>
                                            <input type="text" x-model="generatorDefaults.skuPrefix" placeholder="VAR-" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-mono">
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB 2: Specific Combination Picker (Searchable & Paginated) --}}
                                <div x-show="generatorViewMode === 'specific'" class="space-y-3">
                                    {{-- Search Bar and Batch Tools --}}
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 text-xs">
                                        <div class="relative flex-1">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
                                            <input type="text" x-model="generatorSearchQuery" @input="generatorPreviewPage = 1"
                                                   placeholder="Filter by color, size, storage..."
                                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-gray-300 bg-white">
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click="selectAllVisiblePreview()" class="px-2.5 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 text-[11px] font-semibold">
                                                Select Page (<span x-text="paginatedCombinations.length"></span>)
                                            </button>
                                            <button type="button" @click="deselectAllCombinations()" class="px-2.5 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 hover:bg-gray-100 text-[11px] font-semibold">
                                                Deselect All
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Selection Counter Banner --}}
                                    <div class="p-2.5 bg-indigo-50/70 rounded-xl border border-indigo-100 flex items-center justify-between text-xs">
                                        <span class="text-indigo-900 font-semibold">
                                            Selected: <strong class="text-indigo-700" x-text="selectedCombinationSignatures.length"></strong> of <span x-text="filteredCombinations.length"></span> combinations
                                        </span>
                                        <span class="text-gray-500 text-[11px]" x-text="'Showing page ' + generatorPreviewPage + ' of ' + totalGeneratorPreviewPages"></span>
                                    </div>

                                    {{-- Paginated Combination Items List --}}
                                    <div class="max-h-60 overflow-y-auto space-y-1.5 border border-gray-200 rounded-xl p-2 bg-gray-50/50">
                                        <template x-for="item in paginatedCombinations" :key="item.signature">
                                            <label class="flex items-center justify-between p-2.5 bg-white rounded-lg border cursor-pointer transition"
                                                   :class="isCombinationSelected(item.signature) ? 'border-indigo-500 ring-1 ring-indigo-400 bg-indigo-50/30' : 'border-gray-200 hover:border-gray-300'">
                                                <div class="flex items-center gap-2.5">
                                                    <input type="checkbox" :checked="isCombinationSelected(item.signature)" @change="toggleCombinationSignature(item.signature)" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-xs font-bold text-gray-900" x-text="item.name"></span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <template x-for="chip in item.attribute_chips" :key="chip.name">
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-medium">
                                                            <span class="text-gray-400" x-text="chip.name + ':'"></span> <strong x-text="chip.value"></strong>
                                                        </span>
                                                    </template>
                                                </div>
                                            </label>
                                        </template>

                                        <p x-show="paginatedCombinations.length === 0" class="text-xs text-gray-400 text-center py-6">
                                            No combinations match your search filter.
                                        </p>
                                    </div>

                                    {{-- Pagination Controls --}}
                                    <div x-show="totalGeneratorPreviewPages > 1" class="flex items-center justify-between pt-1 text-xs">
                                        <button type="button" @click="generatorPreviewPage = Math.max(1, generatorPreviewPage - 1)" :disabled="generatorPreviewPage === 1"
                                                class="px-3 py-1 rounded-lg border border-gray-200 bg-white text-gray-700 disabled:opacity-40 hover:bg-gray-50">
                                            &larr; Prev Page
                                        </button>
                                        <span class="text-gray-500 text-xs">Page <strong x-text="generatorPreviewPage"></strong> of <strong x-text="totalGeneratorPreviewPages"></strong></span>
                                        <button type="button" @click="generatorPreviewPage = Math.min(totalGeneratorPreviewPages, generatorPreviewPage + 1)" :disabled="generatorPreviewPage === totalGeneratorPreviewPages"
                                                class="px-3 py-1 rounded-lg border border-gray-200 bg-white text-gray-700 disabled:opacity-40 hover:bg-gray-50">
                                            Next Page &rarr;
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-gray-50 px-6 py-3.5 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100">
                                <div>
                                    <template x-if="generatorViewMode === 'specific'">
                                        <button type="button" @click="generatorViewMode = 'options'" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                                            &larr; Refine Options
                                        </button>
                                    </template>
                                    <template x-if="generatorViewMode === 'options' && totalPossibleCombinationsCount > 0">
                                        <button type="button" @click="generatorViewMode = 'specific'" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                                            Pick Specific Combinations &rarr;
                                        </button>
                                    </template>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="generatorModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        Cancel
                                    </button>

                                    {{-- Primary Action Button --}}
                                    <template x-if="generatorViewMode === 'options'">
                                        <button type="button" @click="applyAllCombinations()"
                                                :disabled="totalPossibleCombinationsCount === 0 || totalPossibleCombinationsCount > 100 || isCalculatingCombinations || isApplyingCombinations"
                                                class="btn-primary text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed shadow-xs">
                                            <i class="fa-solid" :class="(isCalculatingCombinations || isApplyingCombinations) ? 'fa-circle-notch fa-spin' : 'fa-check'"></i>
                                            <span x-text="totalPossibleCombinationsCount > 100 ? 'Limit Exceeded (>100)' : 'Generate All (' + totalPossibleCombinationsCount + ')'"></span>
                                        </button>
                                    </template>

                                    <template x-if="generatorViewMode === 'specific'">
                                        <button type="button" @click="applySelectedSpecificCombinations()"
                                                :disabled="selectedCombinationSignatures.length === 0 || isApplyingCombinations"
                                                class="btn-primary text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 disabled:opacity-50 shadow-xs">
                                            <i class="fa-solid" :class="isApplyingCombinations ? 'fa-circle-notch fa-spin' : 'fa-check'"></i>
                                            <span x-text="'Add ' + selectedCombinationSignatures.length + ' Selected Variations'"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL 2: Create / Edit Single Variant Modal --}}
                <div x-show="formModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="formModalOpen = false"></div>
                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <h3 class="text-sm font-bold text-gray-900" x-text="editingVariantIndex !== null ? 'Edit Variant' : 'Add New Variant'"></h3>
                                    <button type="button" @click="formModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                {{-- Attribute Dropdowns (if available) --}}
                                <div x-show="activeVariantAttributes.length > 0" class="space-y-3 pb-3 border-b border-gray-100">
                                    <p class="text-xs font-semibold text-gray-700">Select Variant Specifications:</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <template x-for="attr in activeVariantAttributes" :key="attr.id">
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-600 mb-1" x-text="attr.name"></label>
                                                <select x-model="singleFormAttributes[attr.id]" @change="updateSingleFormVariantName()"
                                                        class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                                    <option value="">Select option</option>
                                                    <template x-for="opt in (attr.values || [])" :key="opt.id">
                                                        <option :value="opt.id" x-text="opt.value"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Variant Name / Title <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="singleForm.name" placeholder="e.g. 500ml / Blue" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                                            <input type="number" step="0.01" x-model="singleForm.price" placeholder="0.00" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-bold text-indigo-700">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Compare At Price</label>
                                            <input type="number" step="0.01" x-model="singleForm.compare_at_price" placeholder="Optional" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">SKU / Model Code</label>
                                            <input type="text" x-model="singleForm.sku" placeholder="e.g. VAR-01" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Stock Quantity</label>
                                            <input type="number" x-model="singleForm.stock_quantity" placeholder="0" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Stock Status</label>
                                        <select x-model="singleForm.stock_status" class="w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                            <option value="in_stock">In Stock</option>
                                            <option value="out_of_stock">Out of Stock</option>
                                            <option value="on_backorder">On Backorder</option>
                                            <option value="made_to_order">Made to Order</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                                <button type="button" @click="saveSingleVariantForm()" class="btn-primary text-xs font-semibold px-4 py-2 rounded-xl flex items-center gap-1.5 shadow-xs">
                                    <i class="fa-solid fa-check"></i>
                                    <span x-text="editingVariantIndex !== null ? 'Save Changes' : 'Create Variant'"></span>
                                </button>
                                <button type="button" @click="formModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL 3: Variant Multi-Photo Selector Modal --}}
                <div x-show="imagePickerModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="imagePickerModalOpen = false"></div>
                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-900">Manage Variant Photos</h3>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="'Select one or more photos for: ' + (pickingVariant ? pickingVariant.name : 'this variant')"></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="px-2.5 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold flex items-center gap-1.5 cursor-pointer transition shadow-2xs">
                                            <template x-if="isUploadingVariantPhoto">
                                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                            </template>
                                            <template x-if="!isUploadingVariantPhoto">
                                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                            </template>
                                            <span x-text="isUploadingVariantPhoto ? 'Uploading...' : 'Upload New Photo(s)'"></span>
                                            <input type="file" accept="image/*" multiple class="sr-only" :disabled="isUploadingVariantPhoto" @change="uploadNewVariantPhoto($event)">
                                        </label>
                                        <button type="button" @click="imagePickerModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>

                                <p class="text-xs text-gray-600">
                                    Click images to select/unselect for this variant — you can select as many as you like. Click <strong class="text-amber-600">★ Set as Cover</strong> on any selected photo to make it this variant's primary thumbnail.
                                    <span class="block mt-1 text-gray-500">A photo marked <strong class="text-gray-700">Listing Default</strong> is the overall cover photo from Step 1 — select it (or another photo) here to give this specific variant its own image.</span>
                                </p>

                                <div class="grid grid-cols-3 gap-3 max-h-72 overflow-y-auto pr-1">
                                    <template x-for="(img, mIdx) in mediaItems" :key="img.id">
                                        <div class="relative rounded-xl border-2 overflow-hidden group transition-all"
                                             :class="isVariantImageSelected(img.id) ? 'border-indigo-600 ring-2 ring-indigo-500/20' : 'border-gray-200 hover:border-gray-300'">
                                            <img :src="img.url" class="w-full h-24 object-cover cursor-pointer" @click="toggleVariantImageSelection(img.id)">

                                            {{-- Listing Default Badge — the Step 1 cover photo, distinct from this variant's own selection/cover state below --}}
                                            <div x-show="img.id === primaryImageId" class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-900/75 text-white flex items-center gap-1">
                                                <i class="fa-solid fa-house"></i> Listing Default
                                            </div>

                                            {{-- Selection Checkmark --}}
                                            <div @click="toggleVariantImageSelection(img.id)"
                                                 class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full flex items-center justify-center cursor-pointer transition shadow"
                                                 :class="isVariantImageSelected(img.id) ? 'bg-indigo-600 text-white' : 'bg-black/40 text-white hover:bg-black/60'">
                                                <i class="fa-solid text-[10px]" :class="isVariantImageSelected(img.id) ? 'fa-check' : 'fa-plus'"></i>
                                            </div>

                                            {{-- Delete From Gallery — removes the photo entirely, not just from this variant --}}
                                            <button type="button" @click.stop="deleteGalleryImage(img.id, mIdx)" title="Delete this photo from the gallery"
                                                    class="absolute top-9 right-1.5 w-6 h-6 rounded-full flex items-center justify-center transition shadow opacity-0 group-hover:opacity-100 bg-red-600/90 text-white hover:bg-red-700">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </button>

                                            {{-- Cover Star Pill --}}
                                            <div x-show="isVariantImageSelected(img.id)" class="absolute bottom-1.5 left-1.5 right-1.5">
                                                <button type="button" @click.stop="setVariantCoverImage(img.id)"
                                                        class="w-full py-1 rounded-md text-[10px] font-bold flex items-center justify-center gap-1 shadow transition"
                                                        :class="selectedVariantPrimaryImageId == img.id ? 'bg-amber-500 text-white' : 'bg-white/90 text-gray-700 hover:bg-white'">
                                                    <i class="fa-solid fa-star text-[9px]"></i>
                                                    <span x-text="selectedVariantPrimaryImageId == img.id ? 'Variant Cover' : 'Make Cover'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <p x-show="mediaItems.length === 0" class="text-xs text-gray-400 text-center py-6">
                                    No gallery images uploaded yet. Please upload product photos in Step 1.
                                </p>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-100">
                                <button type="button" @click="clearVariantImages()" class="px-3 py-1.5 rounded-lg border text-xs text-gray-600 hover:bg-gray-100">
                                    Clear Photos
                                </button>
                                <button type="button" @click="saveVariantImages()" class="btn-primary text-xs font-semibold px-5 py-2 rounded-xl shadow-xs">
                                    Done (<span x-text="tempVariantImageIds.length"></span> selected)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL 4: Variant-Specific Tier Pricing Modal --}}
                <div x-show="tierModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="tierModalOpen = false"></div>
                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-2xs">
                                            <i class="fa-solid fa-layer-group"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-900">Variant Tier Pricing</h3>
                                            <p class="text-xs text-gray-500" x-text="managingTierVariant ? managingTierVariant.name : ''"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="tierModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between bg-indigo-50/70 p-3 rounded-xl border border-indigo-100 text-xs">
                                    <div>
                                        <span class="text-gray-600">Base Unit Price:</span>
                                        <strong class="text-indigo-900 ml-1" x-text="'$' + (managingTierVariant ? Number(managingTierVariant.price || 0).toFixed(2) : '0.00')"></strong>
                                    </div>
                                    <template x-if="tierPrices.length > 0">
                                        <button type="button" @click="copyGlobalTiersToVariant()" class="text-indigo-600 hover:text-indigo-800 text-[11px] font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-copy"></i> Copy Global Tiers
                                        </button>
                                    </template>
                                </div>

                                {{-- Variant Tier List Table --}}
                                <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
                                    <template x-for="(tp, tpIdx) in tempVariantTiers" :key="tpIdx">
                                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 grid grid-cols-12 gap-2 items-center text-xs">
                                            <div class="col-span-4">
                                                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Min Qty *</label>
                                                <input type="number" x-model="tp.min_quantity" placeholder="e.g. 10" class="w-full text-xs rounded-lg border-gray-300 py-1.5 px-2 bg-white">
                                            </div>
                                            <div class="col-span-4">
                                                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Max Qty (optional)</label>
                                                <input type="number" x-model="tp.max_quantity" placeholder="&infin;" class="w-full text-xs rounded-lg border-gray-300 py-1.5 px-2 bg-white">
                                            </div>
                                            <div class="col-span-3">
                                                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Unit Price ($) *</label>
                                                <input type="number" step="0.01" x-model="tp.unit_price" placeholder="0.00" class="w-full text-xs rounded-lg border-gray-300 py-1.5 px-2 bg-white font-bold text-indigo-700">
                                            </div>
                                            <div class="col-span-1 text-right pt-3">
                                                <button type="button" @click="tempVariantTiers.splice(tpIdx, 1)" class="text-gray-400 hover:text-red-600 p-1">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    <button type="button" @click="tempVariantTiers.push({ min_quantity: '', max_quantity: '', unit_price: '' })"
                                            class="w-full py-2 rounded-xl border border-dashed border-gray-300 text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-plus text-indigo-600"></i> Add Volume Break
                                    </button>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                                <button type="button" @click="tierModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="button" @click="saveVariantTierPricing()" class="btn-primary text-xs font-semibold px-5 py-2 rounded-xl shadow-xs">
                                    Save Tiers (<span x-text="tempVariantTiers.filter(t => t.min_quantity && t.unit_price).length"></span>)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL 5: Preview Listing Modal --}}
                <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="previewModalOpen = false"></div>
                        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900">Listing Preview</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">This is exactly what buyers and the approval team will see. Your latest changes have been saved as a draft.</p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <a :href="previewUrl" target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-up-right-from-square"></i> View Full Page
                                    </a>
                                    <button type="button" @click="previewModalOpen = false" class="text-gray-400 hover:text-gray-600 text-sm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-gray-50/60 px-6 py-5 max-h-[70vh] overflow-y-auto text-left">
                                <div x-show="isLoadingPreview" class="py-16 text-center text-xs text-gray-500">
                                    <i class="fa-solid fa-circle-notch fa-spin text-indigo-600 text-lg mb-2"></i>
                                    <p>Loading preview…</p>
                                </div>
                                <div x-show="!isLoadingPreview" x-html="previewHtml"></div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2 border-t border-gray-100">
                                <button type="button" @click="submitForApprovalFromPreview()" :disabled="isSaving"
                                        class="btn-primary text-xs font-semibold px-5 py-2 rounded-xl shadow-xs flex items-center justify-center gap-1.5">
                                    <i class="fa-solid" :class="isSaving ? 'fa-circle-notch fa-spin' : 'fa-paper-plane'"></i>
                                    <span>Submit for Platform Approval</span>
                                </button>
                                <button type="button" @click="previewModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    Continue Editing
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Compact Final Review Summary --}}
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl flex flex-wrap items-center gap-x-6 gap-y-2 text-xs">
                    <span class="flex items-center gap-1.5 text-gray-700">
                        <i class="fa-solid fa-image text-gray-400"></i>
                        <strong x-text="mediaItems.length"></strong> photo(s)
                    </span>
                    <span class="flex items-center gap-1.5 text-gray-700">
                        <i class="fa-solid fa-sitemap text-gray-400"></i>
                        <span x-text="selectedCategoryPath || 'No category selected'"></span>
                    </span>
                    <span class="flex items-center gap-1.5 text-gray-700">
                        <i class="fa-solid fa-tag text-gray-400"></i>
                        Pricing: <span x-text="pricingType.replace('_', ' ')" class="capitalize"></span>
                    </span>
                    <span class="flex items-center gap-1.5 text-gray-700">
                        <i class="fa-solid fa-layer-group text-gray-400"></i>
                        <strong x-text="variants.length"></strong> variant(s)
                    </span>
                    <span class="flex items-center gap-1.5 text-indigo-700 font-medium ml-auto">
                        <i class="fa-solid fa-shield-halved"></i>
                        Submitting sends this listing for platform approval
                    </span>
                </div>
            </div>

            {{-- Step 4 Sticky Action Bar --}}
            <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
                <button type="button" @click="goToStep(3)" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Previous Step
                </button>
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="previewListing()" :disabled="isSaving"
                            class="px-4 py-2.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-semibold flex items-center gap-2 transition shadow-2xs">
                        <i class="fa-solid" :class="isSaving ? 'fa-circle-notch fa-spin' : 'fa-eye'"></i>
                        <span>Preview Listing</span>
                    </button>
                    <button type="button" @click="saveDraftStep4()" :disabled="isSaving"
                            class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Save as Draft
                    </button>
                    <button type="button" @click="submitForApproval()" :disabled="isSaving"
                            class="btn-primary text-sm font-medium px-5 py-2.5 rounded-lg flex items-center gap-2 shadow-xs">
                        <i class="fa-solid" :class="isSaving ? 'fa-circle-notch fa-spin' : 'fa-paper-plane'"></i>
                        <span>Submit for Platform Approval</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
function productListingWizard(config) {
    return {
        isEdit: config.isEdit || false,
        listingId: config.listingId || null,
        currentStep: config.currentStep || 1,
        maxCompletedStep: config.maxCompletedStep || 1,
        listingType: config.listingType || 'product',
        // The full service workflow isn't built yet — new listings are
        // gated to "product" in the UI, but an existing service listing
        // (created before this gate) must remain editable.
        wasServiceOnLoad: (config.isEdit && config.listingType == '{{ $listingTypes->firstWhere('code','service')?->id ?? '' }}') || false,
        primaryImageId: config.primaryImageId || null,
        mediaItems: config.mediaItems || [],
        pricingType: config.pricingType || '',
        hasTierPricing: config.hasTierPricing || false,
        tierPrices: config.tierPrices || [],
        variants: config.variants || [],
        step1Url: config.step1Url,
        step2Url: config.step2Url,
        step3Url: config.step3Url,
        step4Url: config.step4Url,
        previewUrl: config.previewUrl || null,
        setCoverUrl: config.setCoverUrl,
        lastSavedTime: config.lastSavedTime || null,
        variationSavedToast: false,
        generatorSkippedMessage: '',
        categoryNodes: config.categoryNodes || [],
        selectedCategoryId: config.selectedCategoryId || null,
        selectedCategoryPath: config.selectedCategoryPath || '',
        categorySearch: '',
        catPage: 1,
        catPerPage: 10,
        variantSearch: '',
        variantPage: 1,
        variantPerPage: 10,
        isSaving: false,
        errorMessage: '',
        fieldErrors: {},
        currencyCode: config.currencyCode || 'USD',
        unitId: config.unitId || '',
        customCurrencyCode: '',
        customCurrencyName: '',
        customCurrencySymbol: '',
        customUnitName: '',
        customUnitSymbol: '',

        activeVariantAttributes: [],
        generatorModalOpen: false,
        formModalOpen: false,
        imagePickerModalOpen: false,
        previewModalOpen: false,
        previewHtml: '',
        isLoadingPreview: false,
        editingVariantIndex: null,
        selectedVariantImageId: null,
        pickingVariantIndex: null,
        selectedGeneratorOptions: {},
        generatorViewMode: 'options',
        generatorSearchQuery: '',
        generatorPreviewPage: 1,
        generatorPreviewPerPage: 15,
        selectedCombinationSignatures: [],
        allPossibleCombinations: [],
        totalPossibleCombinationsCount: 0,
        formulaText: '',
        generatorDefaults: {
            price: '',
            stock: 10,
            skuPrefix: 'VAR-',
        },
        singleForm: {
            name: '',
            price: '',
            compare_at_price: '',
            sku: '',
            stock_quantity: 10,
            stock_status: 'in_stock',
        },
        singleFormAttributes: {},

        get stepDefs() {
            return [
                { num: 1, label: 'Basics & Media' },
                { num: 2, label: this.listingType === 'service' ? 'Category & Service' : 'Category & Specifications' },
                { num: 3, label: this.listingType === 'service' ? 'Pricing & Terms' : 'Pricing & Commercial Terms' },
                { num: 4, label: this.listingType === 'service' ? 'Review & Submit' : 'Variants & Review' },
            ];
        },

        get filteredVariants() {
            // Tag each variant with its real index into this.variants (not
            // its position in this filtered/paged list) so row actions
            // always target the correct underlying variant.
            const indexed = this.variants.map((v, idx) => Object.assign(v, { __idx: idx }));
            const q = (this.variantSearch || '').trim().toLowerCase();
            if (!q) return indexed;
            return indexed.filter(v => (v.name || '').toLowerCase().includes(q) || (v.sku || '').toLowerCase().includes(q));
        },

        get totalVariantPages() {
            return Math.max(1, Math.ceil(this.filteredVariants.length / this.variantPerPage));
        },

        get pagedVariants() {
            const start = (this.variantPage - 1) * this.variantPerPage;
            return this.filteredVariants.slice(start, start + this.variantPerPage);
        },

        get variantPageStart() {
            return this.filteredVariants.length === 0 ? 0 : (this.variantPage - 1) * this.variantPerPage + 1;
        },

        get variantPageEnd() {
            return Math.min(this.variantPage * this.variantPerPage, this.filteredVariants.length);
        },

        get filteredCategoryNodes() {
            const q = (this.categorySearch || '').trim().toLowerCase();
            if (!q) return this.categoryNodes;
            return this.categoryNodes.filter(n => n.name.toLowerCase().includes(q) || (n.path && n.path.toLowerCase().includes(q)));
        },

        get totalCatPages() {
            return Math.max(1, Math.ceil(this.filteredCategoryNodes.length / this.catPerPage));
        },

        get pagedCategoryNodes() {
            const start = (this.catPage - 1) * this.catPerPage;
            return this.filteredCategoryNodes.slice(start, start + this.catPerPage);
        },

        get catPageStart() {
            return this.filteredCategoryNodes.length === 0 ? 0 : (this.catPage - 1) * this.catPerPage + 1;
        },

        get catPageEnd() {
            return Math.min(this.catPage * this.catPerPage, this.filteredCategoryNodes.length);
        },

        selectCategoryNode(node) {
            this.selectedCategoryId = node.id;
            this.selectedCategoryPath = node.path || node.name;
            window.dispatchEvent(new CustomEvent('category-selected', { detail: { categoryId: node.id } }));
            this.loadVariantAttributes(node.id);
        },

        init() {
            if (this.tierPrices.length === 0) {
                this.tierPrices = [{ min_quantity: 10, max_quantity: 49, unit_price: '' }];
            }
            this.rebuildMediaIndex();
            if (this.selectedCategoryId) {
                this.loadVariantAttributes(this.selectedCategoryId);
            }
            window.addEventListener('category-selected', (e) => {
                if (e.detail && e.detail.categoryId) {
                    this.loadVariantAttributes(e.detail.categoryId);
                }
            });
        },

        // Rebuilt whenever mediaItems changes, so getVariantImageUrl() is an
        // O(1) lookup instead of an O(n) scan run per variant row per render.
        mediaUrlById: {},
        rebuildMediaIndex() {
            const map = {};
            this.mediaItems.forEach(m => { map[m.id] = m.url; });
            this.mediaUrlById = map;
        },

        isCalculatingCombinations: false,
        isApplyingCombinations: false,

        combinationSignature(attrs) {
            return Object.keys(attrs || {}).sort().map(k => k + ':' + attrs[k]).join('|');
        },

        get filteredCombinations() {
            const q = (this.generatorSearchQuery || '').trim().toLowerCase();
            if (!q) return this.allPossibleCombinations;
            return this.allPossibleCombinations.filter(c => c.name.toLowerCase().includes(q));
        },

        get totalGeneratorPreviewPages() {
            return Math.max(1, Math.ceil(this.filteredCombinations.length / this.generatorPreviewPerPage));
        },

        get paginatedCombinations() {
            const start = (this.generatorPreviewPage - 1) * this.generatorPreviewPerPage;
            return this.filteredCombinations.slice(start, start + this.generatorPreviewPerPage);
        },

        selectAllOptionsForAttribute(attr) {
            this.selectedGeneratorOptions[attr.id] = (attr.values || []).map(o => ({ id: o.id, value: o.value }));
            this.recalculateCombinations();
        },

        clearOptionsForAttribute(attr) {
            this.selectedGeneratorOptions[attr.id] = [];
            this.recalculateCombinations();
        },

        isCombinationSelected(sig) {
            return this.selectedCombinationSignatures.includes(sig);
        },

        toggleCombinationSignature(sig) {
            const idx = this.selectedCombinationSignatures.indexOf(sig);
            if (idx > -1) {
                this.selectedCombinationSignatures.splice(idx, 1);
            } else {
                this.selectedCombinationSignatures.push(sig);
            }
        },

        selectAllVisiblePreview() {
            this.paginatedCombinations.forEach(item => {
                if (!this.selectedCombinationSignatures.includes(item.signature)) {
                    this.selectedCombinationSignatures.push(item.signature);
                }
            });
        },

        deselectAllCombinations() {
            this.selectedCombinationSignatures = [];
        },

        recalculateCombinations() {
            const activeAttrs = this.activeVariantAttributes.filter(a => {
                const selected = this.selectedGeneratorOptions[a.id] || [];
                return selected.length > 0;
            });

            if (activeAttrs.length === 0) {
                this.allPossibleCombinations = [];
                this.totalPossibleCombinationsCount = 0;
                this.formulaText = '';
                this.selectedCombinationSignatures = [];
                return;
            }

            // 1. Calculate mathematical total
            const counts = activeAttrs.map(a => (this.selectedGeneratorOptions[a.id] || []).length);
            const total = counts.reduce((acc, val) => acc * val, 1);
            this.totalPossibleCombinationsCount = total;

            const formulaParts = activeAttrs.map(a => (this.selectedGeneratorOptions[a.id] || []).length + ' ' + a.name);
            this.formulaText = formulaParts.join(' × ') + ' = ' + total + ' Combinations';

            // 2. Generate full combination objects in memory
            const optionArrays = activeAttrs.map(a => (this.selectedGeneratorOptions[a.id] || []).map(opt => ({
                attribute_id: a.id,
                attribute_name: a.name,
                option_id: opt.id,
                option_value: opt.value,
            })));

            let combos = [[]];
            for (const arr of optionArrays) {
                const next = [];
                for (const acc of combos) {
                    for (const opt of arr) {
                        next.push([...acc, opt]);
                    }
                }
                combos = next;
            }

            this.allPossibleCombinations = combos.map(combo => {
                const name = combo.map(c => c.option_value).join(' / ');
                const attrMap = {};
                const chips = [];
                combo.forEach(c => {
                    attrMap[c.attribute_id] = c.option_id;
                    chips.push({ name: c.attribute_name, value: c.option_value });
                });
                const signature = this.combinationSignature(attrMap);
                return { name, attributes: attrMap, attribute_chips: chips, signature };
            });

            // Default select all if within safe limit (<= 30)
            if (this.totalPossibleCombinationsCount <= 30) {
                this.selectedCombinationSignatures = this.allPossibleCombinations.map(c => c.signature);
            } else {
                this.selectedCombinationSignatures = [];
            }
        },

        applyAllCombinations() {
            if (this.totalPossibleCombinationsCount > 100) {
                alert('Over 100 combinations cannot be bulk-generated directly. Please click "Pick Specific Combinations" to select the exact SKUs you sell.');
                return;
            }
            this.addCombinationsToMatrix(this.allPossibleCombinations);
        },

        applySelectedSpecificCombinations() {
            const selectedCombos = this.allPossibleCombinations.filter(c => this.selectedCombinationSignatures.includes(c.signature));
            if (selectedCombos.length === 0) {
                alert('Please select at least 1 combination.');
                return;
            }
            this.addCombinationsToMatrix(selectedCombos);
        },

        addCombinationsToMatrix(combosList) {
            this.isApplyingCombinations = true;
            setTimeout(() => {
                const existingSignatures = new Set(this.variants.map(v => this.combinationSignature(v.attributes)));
                let skipped = 0;
                let added = 0;

                combosList.forEach(c => {
                    const sig = c.signature || this.combinationSignature(c.attributes);
                    if (existingSignatures.has(sig)) {
                        skipped++;
                        return;
                    }
                    existingSignatures.add(sig);
                    added++;
                    this.variants.push({
                        name: c.name,
                        sku: (this.generatorDefaults.skuPrefix || 'VAR-') + (this.variants.length + 1),
                        price: this.generatorDefaults.price || '',
                        compare_at_price: '',
                        stock_quantity: this.generatorDefaults.stock || 10,
                        stock_status: 'in_stock',
                        primary_image_media_id: null,
                        attributes: c.attributes,
                        attribute_chips: c.attribute_chips,
                    });
                });

                this.isApplyingCombinations = false;
                this.generatorModalOpen = false;

                if (skipped > 0) {
                    this.generatorSkippedMessage = skipped + ' combination(s) already existed in the matrix and were skipped.';
                    setTimeout(() => { this.generatorSkippedMessage = ''; }, 5000);
                }
            }, 30);
        },

        isGeneratorLoading: false,

        async loadVariantAttributes(categoryId) {
            if (!categoryId) return;
            this.isGeneratorLoading = true;
            try {
                const url = '{{ route('supplier.catalog.listings.category.attributes', '__CAT__') }}'.replace('__CAT__', categoryId);
                const res = await fetch(url);
                const data = await res.json();
                const vAttrs = [];
                (data.groups || []).forEach(g => {
                    (g.attributes || []).forEach(a => {
                        if (a.is_variant && a.values && a.values.length > 0) {
                            vAttrs.push(a);
                        }
                    });
                });
                this.activeVariantAttributes = vAttrs;
                vAttrs.forEach(attr => {
                    if (!this.selectedGeneratorOptions[attr.id] || this.selectedGeneratorOptions[attr.id].length === 0) {
                        this.selectedGeneratorOptions[attr.id] = (attr.values || []).map(o => ({ id: o.id, value: o.value }));
                    }
                });
                this.recalculateCombinations();
            } catch (err) {
                console.error('Error loading variant attributes:', err);
            } finally {
                this.isGeneratorLoading = false;
            }
        },

        async openGeneratorModal() {
            this.generatorViewMode = 'options';
            if (this.activeVariantAttributes.length === 0 && this.selectedCategoryId) {
                await this.loadVariantAttributes(this.selectedCategoryId);
            } else {
                this.recalculateCombinations();
            }
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
            this.recalculateCombinations();
        },

        openCreateVariantModal() {
            this.editingVariantIndex = null;
            this.singleForm = {
                name: '',
                price: '',
                compare_at_price: '',
                sku: 'VAR-' + (this.variants.length + 1),
                stock_quantity: 10,
                stock_status: 'in_stock',
            };
            this.singleFormAttributes = {};
            this.formModalOpen = true;
        },

        openEditVariantModal(index) {
            this.editingVariantIndex = index;
            const v = this.variants[index];
            this.singleForm = {
                name: v.name,
                price: v.price,
                compare_at_price: v.compare_at_price || '',
                sku: v.sku || '',
                stock_quantity: v.stock_quantity,
                stock_status: v.stock_status || 'in_stock',
            };
            this.singleFormAttributes = Object.assign({}, v.attributes || {});
            this.formModalOpen = true;
        },

        updateSingleFormVariantName() {
            const parts = [];
            this.activeVariantAttributes.forEach(attr => {
                const optId = this.singleFormAttributes[attr.id];
                if (optId) {
                    const found = (attr.values || []).find(o => o.id == optId);
                    if (found) {
                        parts.push(found.value);
                    }
                }
            });
            if (parts.length > 0) {
                this.singleForm.name = parts.join(' / ');
            }
        },

        saveSingleVariantForm() {
            if (!this.singleForm.name) {
                alert('Variant name is required.');
                return;
            }

            const chips = [];
            this.activeVariantAttributes.forEach(attr => {
                const optId = this.singleFormAttributes[attr.id];
                if (optId) {
                    const found = (attr.values || []).find(o => o.id == optId);
                    if (found) {
                        chips.push({ name: attr.name, value: found.value });
                    }
                }
            });

            const data = {
                name: this.singleForm.name,
                sku: this.singleForm.sku,
                price: this.singleForm.price,
                compare_at_price: this.singleForm.compare_at_price,
                stock_quantity: this.singleForm.stock_quantity,
                stock_status: this.singleForm.stock_status,
                primary_image_media_id: (this.editingVariantIndex !== null && this.variants[this.editingVariantIndex]) ? this.variants[this.editingVariantIndex].primary_image_media_id : null,
                attributes: Object.assign({}, this.singleFormAttributes),
                attribute_chips: chips,
            };

            if (this.editingVariantIndex !== null) {
                Object.assign(this.variants[this.editingVariantIndex], data);
            } else {
                this.variants.push(data);
            }

            this.formModalOpen = false;
        },

        tierModalOpen: false,
        managingTierVariantIndex: null,
        tempVariantTiers: [],

        get managingTierVariant() {
            if (this.managingTierVariantIndex === null) return null;
            return this.variants[this.managingTierVariantIndex] || null;
        },

        openTierModalForVariant(index) {
            this.managingTierVariantIndex = index;
            const v = this.variants[index];
            this.tempVariantTiers = JSON.parse(JSON.stringify(v.tier_prices || []));
            if (this.tempVariantTiers.length === 0) {
                this.tempVariantTiers = [{ min_quantity: 10, max_quantity: 49, unit_price: '' }];
            }
            this.tierModalOpen = true;
        },

        copyGlobalTiersToVariant() {
            if (this.tierPrices.length > 0) {
                this.tempVariantTiers = JSON.parse(JSON.stringify(this.tierPrices));
            }
        },

        saveVariantTierPricing() {
            if (this.managingTierVariantIndex !== null && this.variants[this.managingTierVariantIndex]) {
                const validTiers = this.tempVariantTiers.filter(t => t.min_quantity && t.unit_price);
                this.variants[this.managingTierVariantIndex].tier_prices = validTiers;
            }
            this.tierModalOpen = false;
        },

        get pickingVariant() {
            if (this.pickingVariantIndex === null) return null;
            return this.variants[this.pickingVariantIndex] || null;
        },

        tempVariantImageIds: [],
        selectedVariantPrimaryImageId: null,

        openImagePickerForVariant(index) {
            this.pickingVariantIndex = index;
            const v = this.variants[index];
            this.tempVariantImageIds = v.image_media_ids ? [...v.image_media_ids] : (v.primary_image_media_id ? [v.primary_image_media_id] : []);
            this.selectedVariantPrimaryImageId = v.primary_image_media_id || (this.tempVariantImageIds.length > 0 ? this.tempVariantImageIds[0] : null);
            this.imagePickerModalOpen = true;
        },

        isVariantImageSelected(mediaId) {
            return this.tempVariantImageIds.includes(mediaId);
        },

        toggleVariantImageSelection(mediaId) {
            const idx = this.tempVariantImageIds.indexOf(mediaId);
            if (idx > -1) {
                this.tempVariantImageIds.splice(idx, 1);
                if (this.selectedVariantPrimaryImageId === mediaId) {
                    this.selectedVariantPrimaryImageId = this.tempVariantImageIds.length > 0 ? this.tempVariantImageIds[0] : null;
                }
            } else {
                this.tempVariantImageIds.push(mediaId);
                if (!this.selectedVariantPrimaryImageId) {
                    this.selectedVariantPrimaryImageId = mediaId;
                }
            }
        },

        setVariantCoverImage(mediaId) {
            if (!this.tempVariantImageIds.includes(mediaId)) {
                this.tempVariantImageIds.push(mediaId);
            }
            this.selectedVariantPrimaryImageId = mediaId;
        },

        clearVariantImages() {
            this.tempVariantImageIds = [];
            this.selectedVariantPrimaryImageId = null;
        },

        uploadMediaUrl: config.uploadMediaUrl || null,
        isUploadingVariantPhoto: false,

        async uploadNewVariantPhoto(event) {
            const files = Array.from(event.target.files || []);
            if (files.length === 0) return;
            if (!this.listingId || !this.uploadMediaUrl) {
                alert('Please complete Step 1 basics before uploading new photos directly.');
                return;
            }
            this.isUploadingVariantPhoto = true;
            let failedCount = 0;
            try {
                // Uploaded sequentially (not Promise.all) so the media
                // library's per-listing filename generator never sees two
                // concurrent requests race for the same name.
                for (const file of files) {
                    try {
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('_token', '{{ csrf_token() }}');

                        const res = await fetch(this.uploadMediaUrl, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData
                        });
                        const data = await res.json();
                        if (data.success && data.media) {
                            this.mediaItems.push(data.media);
                            this.tempVariantImageIds.push(data.media.id);
                            if (!this.selectedVariantPrimaryImageId) {
                                this.selectedVariantPrimaryImageId = data.media.id;
                            }
                        } else {
                            failedCount++;
                        }
                    } catch (err) {
                        console.error(err);
                        failedCount++;
                    }
                }
                this.rebuildMediaIndex();
                if (failedCount > 0) {
                    alert(failedCount + ' of ' + files.length + ' photo(s) failed to upload.');
                }
            } finally {
                this.isUploadingVariantPhoto = false;
                event.target.value = '';
            }
        },

        saveVariantImages() {
            if (this.pickingVariantIndex !== null && this.variants[this.pickingVariantIndex]) {
                this.variants[this.pickingVariantIndex].image_media_ids = [...this.tempVariantImageIds];
                this.variants[this.pickingVariantIndex].primary_image_media_id = this.selectedVariantPrimaryImageId || (this.tempVariantImageIds.length > 0 ? this.tempVariantImageIds[0] : null);
            }
            this.imagePickerModalOpen = false;
        },

        getVariantImageUrl(mediaId) {
            const placeholder = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="%23e5e7eb" viewBox="0 0 24 24"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm1 2v10h14V7H5zm2 8l3-4 2.5 3 3.5-4.5 3 5.5H7z"/></svg>';
            if (!mediaId) return placeholder;
            return this.mediaUrlById[mediaId] || placeholder;
        },

        formatSavedTime(timeVal) {
            if (!timeVal) return '';
            if (timeVal === 'Just now') return 'Just now';
            try {
                const d = new Date(timeVal);
                if (!isNaN(d.getTime())) {
                    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                }
            } catch (e) {}
            return timeVal;
        },

        goToStep(step) {
            if (step <= this.maxCompletedStep || step === 1) {
                this.currentStep = step;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        addTier() {
            this.tierPrices.push({ min_quantity: '', max_quantity: '', unit_price: '' });
        },

        removeTier(index) {
            this.tierPrices.splice(index, 1);
        },

        removeVariant(index) {
            this.variants.splice(index, 1);
            this.variantPage = Math.min(this.variantPage, this.totalVariantPages);
        },

        pendingFiles: [],

        handleFileSelect(event) {
            this.processFiles(event.target.files);
        },

        handleFileDrop(event) {
            this.processFiles(event.dataTransfer.files);
        },

        processFiles(fileList) {
            if (!fileList || fileList.length === 0) return;
            for (let i = 0; i < fileList.length; i++) {
                const f = fileList[i];
                if (!f.type.startsWith('image/')) continue;
                const isFirst = this.mediaItems.length === 0 && this.pendingFiles.length === 0;
                this.pendingFiles.push({
                    file: f,
                    name: f.name,
                    formattedSize: (f.size / (1024 * 1024)).toFixed(2) + ' MB',
                    previewUrl: URL.createObjectURL(f),
                    isCover: isFirst
                });
            }
        },

        setPendingCover(index) {
            this.pendingFiles.forEach((f, idx) => f.isCover = (idx === index));
            this.mediaItems.forEach(m => m.is_primary = false);
        },

        removePendingFile(index) {
            this.pendingFiles.splice(index, 1);
            if (this.pendingFiles.length > 0 && !this.pendingFiles.some(f => f.isCover) && this.mediaItems.length === 0) {
                this.pendingFiles[0].isCover = true;
            }
        },

        async setAsCover(mediaId) {
            if (!this.listingId || !this.setCoverUrl) return;
            this.isSaving = true;
            try {
                const res = await fetch(this.setCoverUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ media_id: mediaId })
                });
                const data = await res.json();
                if (data.success) {
                    this.primaryImageId = mediaId;
                    this.mediaItems.forEach(m => m.is_primary = (m.id === mediaId));
                    this.pendingFiles.forEach(f => f.isCover = false);
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isSaving = false;
            }
        },

        async deleteMedia(mediaId, index) {
            if (!confirm('Remove this image from listing?')) return;
            try {
                const deleteUrl = '{{ url('supplier/catalog/listings') }}/' + this.listingId + '/media/' + mediaId;
                const res = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                this.mediaItems.splice(index, 1);
                this.rebuildMediaIndex();
            } catch (err) {
                console.error(err);
            }
        },

        // Deleting from the variant photo picker removes the photo from the
        // whole listing gallery (same as Step 1's delete), then scrubs any
        // variant — and the in-progress picker selection — still pointing
        // at it, so nothing in the UI is left referencing a missing photo.
        async deleteGalleryImage(mediaId, index) {
            await this.deleteMedia(mediaId, index);
            if (this.mediaItems.some(m => m.id === mediaId)) return;

            const tIdx = this.tempVariantImageIds.indexOf(mediaId);
            if (tIdx > -1) this.tempVariantImageIds.splice(tIdx, 1);
            if (this.selectedVariantPrimaryImageId === mediaId) {
                this.selectedVariantPrimaryImageId = this.tempVariantImageIds.length > 0 ? this.tempVariantImageIds[0] : null;
            }

            this.variants.forEach(v => {
                if (Array.isArray(v.image_media_ids)) {
                    v.image_media_ids = v.image_media_ids.filter(id => id !== mediaId);
                }
                if (v.primary_image_media_id === mediaId) {
                    v.primary_image_media_id = (v.image_media_ids && v.image_media_ids.length > 0) ? v.image_media_ids[0] : null;
                }
            });
        },

        async submitStep1() {
            await this.saveStep1(false);
        },

        async saveDraftStep1() {
            await this.saveStep1(true);
        },

        async saveStep1(isDraftOnly = false) {
            const form = document.getElementById('step1Form');
            const formData = new FormData(form);
            if (this.listingId) {
                formData.append('listing_id', this.listingId);
            }

            // Append all locally added pending files
            if (this.pendingFiles.length > 0) {
                formData.delete('images[]');
                this.pendingFiles.forEach((item, idx) => {
                    formData.append('images[]', item.file);
                    if (item.isCover) {
                        formData.append('cover_image_index', idx);
                    }
                });
            }

            this.isSaving = true;
            this.errorMessage = '';
            this.fieldErrors = {};

            try {
                const res = await fetch(this.step1Url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (data.errors || !data.success) {
                    this.fieldErrors = data.errors || {};
                    const firstErr = data.errors ? Object.values(data.errors)[0][0] : data.message;
                    this.errorMessage = firstErr;
                    this.isSaving = false;
                    return;
                }

                this.listingId = data.listing_id;
                this.isEdit = true;
                this.maxCompletedStep = Math.max(this.maxCompletedStep, 2);
                this.mediaItems = data.media || [];
                this.rebuildMediaIndex();
                this.pendingFiles = [];
                this.lastSavedTime = data.last_autosaved_at || 'Just now';
                this.step2Url = '{{ url('supplier/catalog/listings') }}/' + data.listing_id + '/wizard/step-2';
                this.step3Url = '{{ url('supplier/catalog/listings') }}/' + data.listing_id + '/wizard/step-3';
                this.step4Url = '{{ url('supplier/catalog/listings') }}/' + data.listing_id + '/wizard/step-4';
                this.setCoverUrl = '{{ url('supplier/catalog/listings') }}/' + data.listing_id + '/media/primary';

                if (!isDraftOnly) {
                    this.currentStep = 2;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (err) {
                this.errorMessage = 'Network error while saving draft. Please try again.';
            } finally {
                this.isSaving = false;
            }
        },

        async submitStep2() {
            await this.saveStep2(false);
        },

        async saveDraftStep2() {
            await this.saveStep2(true);
        },

        async saveStep2(isDraftOnly = false) {
            const form = document.getElementById('step2Form');
            const formData = new FormData(form);

            this.isSaving = true;
            this.errorMessage = '';
            this.fieldErrors = {};

            try {
                const res = await fetch(this.step2Url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (data.errors || !data.success) {
                    this.fieldErrors = data.errors || {};
                    const firstErr = data.errors ? Object.values(data.errors)[0][0] : data.message;
                    this.errorMessage = firstErr;
                    this.isSaving = false;
                    return;
                }

                this.maxCompletedStep = Math.max(this.maxCompletedStep, 3);
                this.lastSavedTime = data.last_autosaved_at || 'Just now';

                if (!isDraftOnly) {
                    this.currentStep = 3;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (err) {
                this.errorMessage = 'Error saving specifications.';
            } finally {
                this.isSaving = false;
            }
        },

        async submitStep3() {
            await this.saveStep3(false);
        },

        async saveDraftStep3() {
            await this.saveStep3(true);
        },

        async saveStep3(isDraftOnly = false) {
            const form = document.getElementById('step3Form');
            const formData = new FormData(form);

            // If tier pricing is disabled, remove all tiers entries so dummy inputs are not submitted
            if (!this.hasTierPricing) {
                Array.from(formData.keys()).forEach(key => {
                    if (key.startsWith('tiers[')) {
                        formData.delete(key);
                    }
                });
            }

            this.isSaving = true;
            this.errorMessage = '';
            this.fieldErrors = {};

            try {
                const res = await fetch(this.step3Url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (data.errors || !data.success) {
                    this.fieldErrors = data.errors || {};
                    const firstErr = data.errors ? Object.values(data.errors)[0][0] : data.message;
                    this.errorMessage = firstErr;
                    this.isSaving = false;
                    return;
                }

                this.maxCompletedStep = Math.max(this.maxCompletedStep, 4);
                this.lastSavedTime = data.last_autosaved_at || 'Just now';

                if (!isDraftOnly) {
                    this.currentStep = 4;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (err) {
                this.errorMessage = 'Error saving pricing & inventory.';
            } finally {
                this.isSaving = false;
            }
        },

        async saveVariationsOnly() {
            if (!this.variants || this.variants.length === 0) {
                alert('No variations have been added yet. Please add a variant or click "Auto-Generate Combinations" first.');
                return;
            }

            this.isSaving = true;
            this.errorMessage = '';
            this.fieldErrors = {};
            try {
                const res = await fetch(this.step4Url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_draft',
                        variants: this.variants
                    })
                });
                const data = await res.json();
                if (data.errors) {
                    this.fieldErrors = data.errors || {};
                    this.errorMessage = Object.values(data.errors)[0][0];
                    return;
                }
                if (data.success) {
                    if (data.variants) {
                        this.variants = data.variants;
                    }
                    this.lastSavedTime = data.last_autosaved_at || 'Just now';
                    this.variationSavedToast = true;
                    setTimeout(() => { this.variationSavedToast = false; }, 3500);
                    if (data.duplicate_variants_skipped > 0) {
                        this.generatorSkippedMessage = data.duplicate_variants_skipped + ' duplicate combination(s) in your submission were not saved twice.';
                        setTimeout(() => { this.generatorSkippedMessage = ''; }, 5000);
                    }
                }
            } catch (err) {
                this.errorMessage = 'Failed to save variations.';
            } finally {
                this.isSaving = false;
            }
        },

        async previewListing() {
            this.isSaving = true;
            this.errorMessage = '';
            this.fieldErrors = {};
            try {
                const res = await fetch(this.step4Url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_draft',
                        variants: this.variants
                    })
                });
                const data = await res.json();
                if (data.errors) {
                    this.fieldErrors = data.errors || {};
                    this.errorMessage = Object.values(data.errors)[0][0];
                    return;
                }
                if (data.variants) {
                    this.variants = data.variants;
                }
            } catch (err) {
                this.errorMessage = 'Error saving listing for preview.';
                this.isSaving = false;
                return;
            }
            this.isSaving = false;

            // Open the modal immediately with a loading state, then fill it
            // in — the fetch above already persisted the draft, so this is
            // just rendering the same read-only detail page as a fragment
            // instead of navigating away from the wizard.
            this.previewModalOpen = true;
            this.isLoadingPreview = true;
            this.previewHtml = '';
            try {
                const previewUrl = '{{ url('supplier/catalog/listings') }}/' + this.listingId + '/preview';
                const res = await fetch(previewUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.previewHtml = data.success ? data.html : '<p class="text-xs text-red-600 text-center py-8">Could not load the preview.</p>';
            } catch (err) {
                this.previewHtml = '<p class="text-xs text-red-600 text-center py-8">Could not load the preview.</p>';
            } finally {
                this.isLoadingPreview = false;
            }
        },

        async submitForApprovalFromPreview() {
            this.previewModalOpen = false;
            await this.submitForApproval();
        },

        async saveDraftStep4() {
            this.isSaving = true;
            this.errorMessage = '';
            this.fieldErrors = {};
            try {
                const res = await fetch(this.step4Url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_draft',
                        variants: this.variants
                    })
                });
                const data = await res.json();
                if (data.errors) {
                    this.fieldErrors = data.errors || {};
                    this.errorMessage = Object.values(data.errors)[0][0];
                    return;
                }
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            } catch (err) {
                this.errorMessage = 'Error saving draft.';
            } finally {
                this.isSaving = false;
            }
        },

        async submitForApproval() {
            this.isSaving = true;
            this.errorMessage = '';
            this.fieldErrors = {};
            try {
                const res = await fetch(this.step4Url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'submit_approval',
                        variants: this.variants
                    })
                });
                const data = await res.json();
                if (data.errors) {
                    this.fieldErrors = data.errors || {};
                    this.errorMessage = Object.values(data.errors)[0][0];
                    this.isSaving = false;
                    return;
                }
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            } catch (err) {
                this.errorMessage = 'Error submitting for approval.';
            } finally {
                this.isSaving = false;
            }
        }
    };
}
</script>
