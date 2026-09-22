@php
    $quotation = $quotation ?? null;
    $revisionRequest = $revisionRequest ?? null;
    $isRevision = $isRevision ?? false;
    $previousQuotations = $previousQuotations ?? collect();
    $cloneSource = $cloneSource ?? null;
    $cloneMatches = $cloneMatches ?? [];
    $isEdit = $quotation?->exists ?? false;
    $action = $isRevision
        ? route('supplier.quotations.revision.store', $quotation)
        : ($isEdit ? route('supplier.quotations.update', $quotation) : route('supplier.quotations.store', $rfq));

    // Read-only "what the buyer asked for" lookup, keyed by rfq_item id —
    // shared by every response row so the comparison always reflects the
    // buyer's actual requirement, never the supplier's own listing data.
    // Raw, structured version of the buyer's attribute values (as opposed to
    // the formatted display strings in `attributes`/`attributes_by_id` below)
    // — rfq_item_attribute_values and listing_attribute_values mirror
    // quotation_item_attribute_values' columns exactly, so these can be
    // copied straight into a quotation item's attribute_values with no
    // string parsing/matching, keyed by attribute_id like the offer side.
    $toRawAttrValue = fn ($v) => [
        'attribute_value_id' => $v->attribute_value_id,
        'custom_value' => $v->custom_value,
        'value_text' => $v->value_text,
        'value_number' => $v->value_number !== null ? rtrim(rtrim((string) $v->value_number, '0'), '.') : null,
        'value_boolean' => $v->value_boolean,
        'value_date' => $v->value_date?->format('Y-m-d'),
        'value_json' => $v->value_json,
    ];

    $allCategoryIds = collect($rfq->items)->flatMap(function ($i) {
        $ids = [];
        if (! empty($i->category_id)) {
            $ids[] = (int) $i->category_id;
        }
        if (is_array($i->specs)) {
            foreach ($i->specs as $s) {
                if (is_array($s) && ($s['name'] ?? '') === '__category_ids') {
                    foreach (explode(',', (string) ($s['value'] ?? '')) as $cid) {
                        $cid = (int) trim($cid);
                        if ($cid) {
                            $ids[] = $cid;
                        }
                    }
                }
            }
        }
        return $ids;
    })->unique()->filter()->values()->all();

    $categoryNameMap = ! empty($allCategoryIds)
        ? \App\Models\Category::whereIn('id', $allCategoryIds)->pluck('name', 'id')->all()
        : [];

    $rfqItemsById = $rfq->items->keyBy('id')->map(function ($i) use ($toRawAttrValue, $categoryNameMap) {
        $attrs = collect();
        // Plain array, not a Collection — attribute_id keys are integers, and
        // Collection::merge() runs them through array_merge(), which
        // silently renumbers/duplicates integer keys instead of overwriting
        // by key. Direct array assignment overwrites correctly regardless of
        // key type, same as attributes_by_id further below relies on.
        $attrsRawById = [];
        if ($i->listing && $i->listing->relationLoaded('attributeValues')) {
            $attrs = $i->listing->attributeValues->map(fn ($v) => [
                'name' => $v->attribute?->name,
                'value' => $v->custom_value ?? $v->value_text ?? $v->value_number ?? ($v->attributeValue?->name ?? null),
            ])->filter(fn ($a) => !empty($a['name']) && !empty($a['value']));
            foreach ($i->listing->attributeValues as $v) {
                $attrsRawById[$v->attribute_id] = $toRawAttrValue($v);
            }
        }
        if ($i->attributeValues->isNotEmpty()) {
            $customAttrs = $i->attributeValues->map(fn ($v) => [
                'name' => $v->attribute?->name,
                'value' => $v->formattedValue(),
            ])->filter(fn ($a) => !empty($a['name']) && !empty($a['value']));
            $attrs = $attrs->keyBy('name')->merge($customAttrs->keyBy('name'))->values();
            foreach ($i->attributeValues as $v) {
                $attrsRawById[$v->attribute_id] = $toRawAttrValue($v);
            }
        }

        $imageUrl = $i->listing?->primaryImage?->getUrl()
            ?? ($i->listing?->relationLoaded('media') && $i->listing?->media->isNotEmpty() ? $i->listing?->media->first()?->getUrl() : null)
            ?? $i->listing?->getFirstMediaUrl('gallery')
            ?: null;

        $itemCategoryIds = [];
        if (! empty($i->category_id)) {
            $itemCategoryIds[] = (int) $i->category_id;
        }
        if (is_array($i->specs)) {
            foreach ($i->specs as $s) {
                if (is_array($s) && ($s['name'] ?? '') === '__category_ids') {
                    foreach (explode(',', (string) ($s['value'] ?? '')) as $cid) {
                        $cid = (int) trim($cid);
                        if ($cid && ! in_array($cid, $itemCategoryIds, true)) {
                            $itemCategoryIds[] = $cid;
                        }
                    }
                }
            }
        }
        $categoryNames = collect($itemCategoryIds)
            ->map(fn ($cid) => $categoryNameMap[$cid] ?? null)
            ->filter()
            ->values()
            ->all();

        if (empty($categoryNames)) {
            $fallbackName = $i->category?->name ?? $i->listing?->mainCategory?->name;
            if ($fallbackName) {
                $categoryNames = [$fallbackName];
            }
        }

        return [
            'item_name' => $i->item_name,
            'category_id' => $i->category_id,
            'category_name' => $categoryNames[0] ?? ($i->category?->name ?? $i->listing?->mainCategory?->name),
            'category_names' => $categoryNames,
            'quantity' => rtrim(rtrim((string) $i->quantity, '0'), '.'),
            'unit' => $i->unit?->symbol ?? $i->unit?->name ?? $i->custom_unit ?? $i->listing?->unit?->symbol ?? $i->listing?->unit?->name,
            'unit_id' => $i->unit_id,
            'description' => $i->description ?? $i->listing?->short_description,
            'listing_image_url' => $imageUrl,
            'is_marketplace' => !empty($i->listing_id),
            'is_requirement' => method_exists($i, 'isRequirement') ? $i->isRequirement() : false,
            'attachments' => method_exists($i, 'getMedia') ? $i->getMedia('attachments')->map(fn ($m) => [
                'id'       => $m->id,
                'name'     => $m->file_name,
                'size'     => $m->human_readable_size,
                'url'      => $m->getUrl(),
                'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
            ])->values() : [],
            'estimated_unit_price' => $i->estimated_unit_price ? number_format((float)$i->estimated_unit_price, 2) : ($i->listing?->base_price ? number_format((float)$i->listing->base_price, 2) : null),
            'specs' => is_array($i->specs) ? array_values(array_filter($i->specs, fn($s) => is_array($s) && !in_array($s['name'] ?? '', ['__is_requirement', '__category_ids'], true) && (!empty(trim((string)($s['name'] ?? ''))) || !empty(trim((string)($s['value'] ?? '')))))) : [],
            'attributes' => $attrs->values(),
            'attributes_by_id' => $i->attributeValues->mapWithKeys(fn ($v) => [$v->attribute_id => $v->formattedValue()]),
            'attribute_values_raw' => $attrsRawById,
        ];
    });

    // Flat, indented list ({id, label}) for the optional category picker
    // inside "Create Custom Offer" — same source RequirementController uses
    // for the buyer's own category picker, just rendered as a plain <select>
    // here instead of a searchable multi-select tree (single category per
    // offer, kept deliberately simple).
    $categoryOptions = \App\Models\Category::getTreeSelectOptions(['product', 'service', 'both']);

    if ($isEdit || $isRevision) {
        $existingRfqItemIds = $quotation->items->pluck('rfq_item_id')->filter()->all();
        $initialItems = $quotation->items->where('is_optional_addon', false)->values()->map(function ($item, $idx) use ($errors) {
            $offers = ($item->relationLoaded('offers') && $item->offers->isNotEmpty())
                ? $item->offers->map(function ($o, $oIdx) {
                    return [
                        'id' => $o->id,
                        'offer_method' => $o->offer_method,
                        'marketplace_product_id' => $o->marketplace_product_id,
                        'offered_variant_id' => $o->offered_variant_id,
                        'product_name' => $o->product_name,
                        'category_id' => $o->category_id,
                        'description' => $o->description,
                        'specifications' => is_array($o->specifications) ? $o->specifications : [],
                        'quantity' => (string) $o->quantity,
                        'unit_id' => $o->unit_id,
                        'custom_unit' => $o->custom_unit,
                        'unit_price' => $o->unit_price,
                        'tax_rate' => $o->tax_rate,
                        'discount' => $o->discount,
                        'delivery_time' => $o->delivery_time,
                        'is_primary' => (bool) $o->is_primary,
                        'is_selected' => (bool) $o->is_selected,
                        'sort_order' => (int) $o->sort_order,
                        '_localKey' => \Illuminate\Support\Str::random(12),
                        '_collapsed' => $oIdx > 0,
                        '_image_url' => $o->marketplaceProduct?->primaryImage?->getUrl() ?? ($o->marketplaceProduct?->getFirstMediaUrl('gallery') ?: null),
                        '_brand_name' => $o->marketplaceProduct?->brand?->name,
                        '_category_name' => $o->marketplaceProduct?->mainCategory?->name,
                        '_slug' => $o->marketplaceProduct?->slug,
                        '_variants' => $o->marketplaceProduct ? $o->marketplaceProduct->variants->map(fn($v) => ['id' => $v->id, 'label' => $v->title . ($v->sku ? ' (' . $v->sku . ')' : '')])->values()->all() : [],
                        '_attrGroups' => [],
                        '_attrLoading' => false,
                        '_attribute_values' => [],
                        '_custom_specs' => is_array($o->specifications) ? $o->specifications : [],
                        '_documents' => method_exists($o, 'getMedia') ? $o->getMedia('document')->map(fn ($m) => [
                            'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
                            'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
                        ])->values()->all() : [],
                    ];
                })->values()->all()
                : [[
                    'id' => null,
                    'offer_method' => $item->response_method ?? ($item->offered_listing_id ? 'marketplace' : 'custom'),
                    'marketplace_product_id' => $item->offered_listing_id,
                    'offered_variant_id' => $item->offered_variant_id,
                    'product_name' => $item->item_name,
                    'category_id' => $item->rfqItem?->category_id,
                    'description' => $item->description,
                    'specifications' => is_array($item->specs) ? $item->specs : [],
                    'quantity' => (string) $item->quantity,
                    'unit_id' => $item->unit_id,
                    'custom_unit' => $item->custom_unit,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'discount' => $item->discount_amount,
                    'delivery_time' => $item->lead_time_days,
                    'is_primary' => true,
                    'is_selected' => false,
                    'sort_order' => 0,
                    '_localKey' => \Illuminate\Support\Str::random(12),
                    '_collapsed' => false,
                    '_image_url' => $item->offeredListing?->primaryImage?->getUrl() ?? ($item->offeredListing?->getFirstMediaUrl('gallery') ?: null),
                    '_brand_name' => $item->offeredListing?->brand?->name,
                    '_category_name' => $item->offeredListing?->mainCategory?->name,
                    '_variants' => $item->offeredListing ? $item->offeredListing->variants->map(fn($v) => ['id' => $v->id, 'label' => $v->title . ($v->sku ? ' (' . $v->sku . ')' : '')])->values()->all() : [],
                    '_attrGroups' => [],
                    '_attrLoading' => false,
                    '_attribute_values' => [],
                    '_custom_specs' => is_array($item->specs) ? $item->specs : [],
                    '_documents' => [],
                ]];

            return [
                'id' => $item->id, 'rfq_item_id' => $item->rfq_item_id,
                'offered_listing_id' => $item->offered_listing_id, 'offered_variant_id' => $item->offered_variant_id,
                'is_alternative' => (bool) $item->is_alternative,
                'item_name' => $item->item_name, 'description' => $item->description, 'quantity' => (string) $item->quantity,
                'unit_id' => $item->unit_id, 'custom_unit' => $item->custom_unit,
                'unit_price' => $item->unit_price, 'tax_rate' => $item->tax_rate, 'discount_amount' => $item->discount_amount,
                'lead_time_days' => $item->lead_time_days,
                'attribute_values' => (object) $item->attributeValues->mapWithKeys(fn ($v) => [$v->attribute_id => [
                    'attribute_value_id' => $v->attribute_value_id, 'custom_value' => $v->custom_value,
                    'value_text' => $v->value_text, 'value_number' => $v->value_number,
                    'value_boolean' => $v->value_boolean, 'value_date' => $v->value_date, 'value_json' => $v->value_json,
                ]])->all(),
                'offers' => $offers,
                '_responseMethod' => $item->response_method ?? ($item->offered_listing_id ? 'marketplace' : 'custom'),
                '_localKey' => \Illuminate\Support\Str::random(12),
                '_collapsed' => $idx > 0 && !$errors->has("items.$idx.*"),
                '_documents' => method_exists($item, 'getMedia') ? $item->getMedia('document')->map(fn ($m) => [
                    'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
                    'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
                ])->values()->all() : [],
                '_uploadPreparing' => false,
                '_attrLoading' => false, '_attrGroups' => [], '_variants' => [], '_suggestedListing' => null,
            ];
        })->values();

        $newRfqItems = $rfq->items->whereNotIn('id', $existingRfqItemIds)->map(fn ($item) => [
            'id' => null, 'rfq_item_id' => $item->id,
            'offered_listing_id' => null, 'offered_variant_id' => null, 'is_alternative' => false,
            'item_name' => $item->item_name, 'description' => null, 'quantity' => (string) $item->quantity,
            'unit_id' => $item->unit_id, 'custom_unit' => $item->custom_unit,
            'unit_price' => null, 'tax_rate' => null, 'discount_amount' => null, 'lead_time_days' => null,
            'attribute_values' => (object) [],
            'offers' => [],
            '_responseMethod' => null,
            '_localKey' => \Illuminate\Support\Str::random(12),
            '_collapsed' => false,
            '_documents' => [], '_uploadPreparing' => false, '_attrLoading' => false, '_attrGroups' => [], '_variants' => [], '_suggestedListing' => null,
        ]);
        $initialItems = $initialItems->concat($newRfqItems)->values();

        $initialAddons = $quotation->items->where('is_optional_addon', true)->values()->map(fn ($item) => [
            'id' => $item->id, 'item_name' => $item->item_name, 'description' => $item->description,
            'quantity' => (string) $item->quantity, 'unit_id' => $item->unit_id,
            'unit_price' => $item->unit_price, 'tax_rate' => $item->tax_rate, 'discount_amount' => $item->discount_amount,
            'lead_time_days' => $item->lead_time_days,
        ])->values();
    } else {
        $initialItems = $rfq->items->values()->map(function ($item, $idx) use ($cloneMatches, $errors) {
            $clone = $cloneMatches[$item->id] ?? null;

            return [
                'id' => null, 'rfq_item_id' => $item->id,
                'offered_listing_id' => null, 'offered_variant_id' => null, 'is_alternative' => false,
                'item_name' => $item->item_name, 'description' => $clone['description'] ?? null, 'quantity' => (string) $item->quantity,
                'unit_id' => $item->unit_id, 'custom_unit' => $item->custom_unit,
                'unit_price' => $clone['unit_price'] ?? null, 'tax_rate' => $clone['tax_rate'] ?? null,
                'discount_amount' => $clone['discount_amount'] ?? null, 'lead_time_days' => $clone['lead_time_days'] ?? null,
                'attribute_values' => (object) ($clone['attribute_values'] ?? []),
                'offers' => [],
                '_responseMethod' => null,
                '_localKey' => \Illuminate\Support\Str::random(12),
                '_collapsed' => $idx > 0 && !$errors->has("items.$idx.*"),
                '_documents' => [], '_uploadPreparing' => false, '_attrLoading' => false, '_attrGroups' => [], '_variants' => [], '_suggestedListing' => null,
            ];
        })->values();

        $initialAddons = collect();
    }
@endphp

<form
    method="POST"
    action="{{ $action }}"
    x-data="quotationForm({
        items: {{ $initialItems->toJson() }},
        addons: {{ $initialAddons->toJson() }},
        rfqItemsById: {{ $rfqItemsById->toJson() }},
        allowAlternativeProducts: {{ $rfq->allow_alternative_products ? 'true' : 'false' }},
        currencyCode: '{{ old('currency_code', $quotation?->currency_code ?? $cloneSource?->currency_code ?? $rfq->currency_code ?? 'USD') }}',
        shippingCharge: {{ (float) old('shipping_charge', $quotation?->shipping_charge ?? 0) }},
        categoryAttributesUrl: '{{ url('/supplier/quotations/categories') }}',
        categoryOptions: {{ json_encode($categoryOptions) }},
        listingsSearchUrl: '{{ route('supplier.quotations.listings.search') }}',
        listingsPrefillUrl: '{{ url('/supplier/quotations/listings') }}',
        autoMatchUrl: '{{ route('supplier.quotations.listings.auto-match', $rfq) }}',
        selectProductUrl: '{{ route('supplier.quotations.listings.select', $rfq) }}',
        itemDocumentUrlBase: '{{ url('/supplier/quotations') }}',
        combinedDocumentUrlBase: '{{ url('/supplier/quotations') }}',
        combinedDocuments: {{ ($isEdit && method_exists($quotation, 'getMedia') ? $quotation->getMedia('combined_document')->map(fn ($m) => [
            'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
            'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
        ])->values() : collect())->toJson() }},
        quotationId: {{ $isEdit ? $quotation->id : 'null' }},
        isRevision: {{ $isRevision ? 'true' : 'false' }},
        autosaveCreateUrl: '{{ route('supplier.quotations.autosave.create', $rfq) }}',
        autosaveUpdateUrlBase: '{{ url('/supplier/quotations') }}',
        csrfToken: '{{ csrf_token() }}',
        initialStep: {{ $isEdit ? ($quotation->current_step ?? 1) : 1 }},
        maxCompletedStep: {{ $isEdit ? ($quotation->max_completed_step ?? 1) : 1 }},
    })"
    x-init="init()"
>
    @csrf
    @if($isRevision || $isEdit) @method('PUT') @endif
    <input type="hidden" name="current_step" :value="currentStep">
    <input type="hidden" name="max_completed_step" :value="maxCompletedStep">

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <div class="xl:col-span-8 space-y-6">

            @if($isRevision && $revisionRequest)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-rotate"></i> Buyer Requested Changes
                    </h4>
                    <p class="text-xs text-amber-800 mt-1">{{ $revisionRequest->requested_changes }}</p>
                </div>
            @endif

            {{-- Buyer's overall deadlines — visible on every step, not just per-item --}}
            <div class="flex items-center flex-wrap gap-x-5 gap-y-1 bg-white rounded-xl border border-gray-200 px-4 py-2.5 text-xs">
                <span class="flex items-center gap-1.5 text-gray-600">
                    <i class="fa-regular fa-clock text-amber-500"></i>
                    Quotation Deadline: <span class="font-semibold text-gray-900">{{ $rfq->quotation_deadline?->format('d M Y, h:i A') ?? '—' }}</span>
                </span>
                @if($rfq->expected_delivery_date)
                    <span class="flex items-center gap-1.5 text-gray-600">
                        <i class="fa-solid fa-truck-fast text-indigo-500"></i>
                        Expected Delivery: <span class="font-semibold text-gray-900">{{ $rfq->expected_delivery_date->format('d M Y') }}</span>
                    </span>
                @endif
            </div>

            {{-- ═══ Step tab bar ═══ --}}
            @php
                $quoteSteps = [
                    1 => ['label' => 'Price Items', 'icon' => 'fa-tags'],
                    2 => ['label' => 'Refine & Add-Ons', 'icon' => 'fa-sliders'],
                    3 => ['label' => 'Terms & Submit', 'icon' => 'fa-circle-check'],
                ];
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-2">
                    <nav class="flex gap-0 -mb-px overflow-x-auto" aria-label="Quotation steps">
                        @foreach($quoteSteps as $num => $step)
                            <button type="button" @click="setStep({{ $num }})"
                                    :disabled="isStepLocked({{ $num }})"
                                    :title="isStepLocked({{ $num }}) ? 'Complete the earlier steps first' : ''"
                                    class="flex items-center gap-2 px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors focus:outline-none disabled:cursor-not-allowed"
                                    :class="isStepLocked({{ $num }})
                                        ? 'border-transparent text-gray-300'
                                        : (currentStep === {{ $num }}
                                            ? 'border-indigo-600 text-indigo-600'
                                            : (stepValid({{ $num }}) ? 'border-emerald-500 text-emerald-600 hover:text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'))">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 transition-colors"
                                      :class="isStepLocked({{ $num }})
                                          ? 'bg-gray-100 text-gray-300'
                                          : (stepValid({{ $num }})
                                              ? 'bg-emerald-500 text-white'
                                              : (currentStep === {{ $num }} ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'))">
                                    <i class="fa-solid fa-lock" x-show="isStepLocked({{ $num }})" x-cloak style="font-size:7px"></i>
                                    <i class="fa-solid fa-check" x-show="!isStepLocked({{ $num }}) && stepValid({{ $num }})" x-cloak style="font-size:8px"></i>
                                    <span x-show="!isStepLocked({{ $num }}) && !stepValid({{ $num }})">{{ $num }}</span>
                                </span>
                                <i class="fa-solid {{ $step['icon'] }} text-xs"></i>
                                <span>{{ $step['label'] }}</span>
                            </button>
                        @endforeach

                        <div class="flex-1 flex items-center justify-end px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-50 border border-gray-200" x-show="quotationId || isSaving" x-cloak>
                                <span class="w-1.5 h-1.5 rounded-full" :class="isSaving ? 'bg-amber-400 animate-pulse' : 'bg-emerald-500'"></span>
                                <span x-text="isSaving ? 'Saving…' : 'Draft saved'"></span>
                            </span>
                            <p x-show="saveError" x-cloak class="text-xs text-red-600 ml-3" x-text="saveError"></p>
                        </div>
                    </nav>
                </div>

                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-300" :style="'width:' + completionPercent + '%; background:var(--theme-primary, #4f46e5)'"></div>
                    </div>
                    <span class="text-xs font-bold text-gray-700 shrink-0" x-text="completionPercent + '% Complete'"></span>
                </div>
            </div>

            {{-- ═══════ STEP 1 — Price Items ═══════ --}}
            <div x-show="currentStep === 1" x-cloak class="space-y-6">
                <x-backend.form-card title="Requested Items" description="Respond to each RFQ item — add a product, choose how you want to provide it, and add alternatives if you have more than one option.">
                    <div x-show="items.length > 1" x-cloak class="flex items-center gap-3 mb-4 -mt-1">
                        <button type="button" @click="collapseAllItems()" class="text-xs font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-compress"></i> Collapse All
                        </button>
                        <button type="button" @click="expandAllItems()" class="text-xs font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-expand"></i> Expand All
                        </button>
                    </div>

                    @foreach($rfq->items as $rfqItem)
                        <div class="mb-6 pb-6 border-b border-gray-100 last:border-b-0 last:mb-0 last:pb-0">
                            @include('backend.supplier.procurement.quotations.partials._rfq-item-panel')

                            <div class="space-y-3 mb-3">
                                <template x-for="item in itemsForRfq({{ $rfqItem->id }})" :key="item._localKey">
                                    @include('backend.supplier.procurement.quotations.partials._item')
                                </template>
                            </div>

                            <button type="button" @click="addProduct({{ $rfqItem->id }})"
                                    class="text-xs font-semibold px-3 py-2 rounded-lg border border-dashed border-indigo-300 text-indigo-700 hover:bg-indigo-50 flex items-center gap-1.5 transition-colors">
                                <i class="fa-solid fa-plus text-[10px]"></i> Add Another Product Response
                            </button>
                        </div>
                    @endforeach

                    <template x-if="extraItems().length > 0">
                        <div class="mb-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Additional Items You're Offering</label>
                            <div class="space-y-3">
                                <template x-for="item in extraItems()" :key="item._localKey">
                                    @include('backend.supplier.procurement.quotations.partials._item')
                                </template>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addItem()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Offer Something Else
                    </button>
                </x-backend.form-card>

                <div class="flex justify-end">
                    <button type="button" @click="goNext(1)" class="btn-primary text-sm font-semibold px-5 py-2.5 rounded-lg flex items-center gap-2">
                        Next: Refine &amp; Add-Ons <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- ═══════ STEP 2 — Refine & Add-Ons ═══════ --}}
            <div x-show="currentStep === 2" x-cloak class="space-y-6">
                <x-backend.form-card title="Refine Your Offer" description="Every item's already priced — optionally fine-tune specs, offer type, or bulk-fill from the buyer's own requirements before moving on.">
                    <button type="button" @click="copyBuyerRequirementsToAll()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                        <i class="fa-solid fa-copy"></i> Copy buyer's requirements to all items
                    </button>
                    <p class="text-xs text-gray-400 mt-2">Each item's "Advanced" section (offer type, listing, specifications) is still available on the Price Items step if you'd like to adjust it further.</p>
                </x-backend.form-card>

                <x-backend.form-card title="Optional Add-Ons" description="Products or services you'd like to offer that the buyer didn't request — shown separately and never treated as a response to an RFQ item.">
                    <template x-for="(addon, index) in addons" :key="index">
                        @include('backend.supplier.procurement.quotations.partials._addon')
                    </template>

                    <button type="button" @click="addAddon()" class="text-sm font-medium px-4 py-2 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Add Optional Item
                    </button>
                </x-backend.form-card>

                <div class="flex justify-between">
                    <button type="button" @click="setStep(1)" class="text-sm font-semibold px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Back
                    </button>
                    <button type="button" @click="goNext(2)" class="btn-primary text-sm font-semibold px-5 py-2.5 rounded-lg flex items-center gap-2">
                        Next: Terms &amp; Submit <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- ═══════ STEP 3 — Terms & Submit ═══════ --}}
            <div x-show="currentStep === 3" x-cloak class="space-y-6">
                <x-backend.form-card title="Commercial Proposal &amp; Terms">
                    <div class="space-y-4">
                        @if($isRevision)
                            <x-backend.textarea name="change_summary" label="What changed in this revision?" required :value="old('change_summary')" />
                        @endif
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-backend.select name="currency_code" label="Currency" placeholder="Select currency">
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->code }}" @selected(old('currency_code', $quotation?->currency_code ?? $cloneSource?->currency_code ?? $rfq->currency_code) === $currency->code)>{{ $currency->code }} — {{ $currency->name }}</option>
                                @endforeach
                            </x-backend.select>
                            <x-backend.input type="number" name="lead_time_days" label="Overall Lead Time (Days)" :value="old('lead_time_days', $quotation?->lead_time_days ?? $cloneSource?->lead_time_days)" />
                            <x-backend.input type="date" name="valid_until" label="Quotation Validity Date" :value="old('valid_until', optional($quotation?->valid_until)->format('Y-m-d'))" />
                        </div>
                        <x-backend.input type="number" name="shipping_charge" label="Shipping Charge" step="0.01" min="0" :value="old('shipping_charge', $quotation?->shipping_charge ?? 0)" />
                        <x-backend.textarea name="proposal" label="Executive Summary / Proposal" :value="old('proposal', $quotation?->proposal ?? $cloneSource?->proposal)" placeholder="Explain your proposal, brand advantages, quality assurances..." />

                        {{-- Supplementary, whole-quotation attachment(s) — does not
                             replace the itemized pricing on Step 1/2. --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Supporting Documents (optional)</label>
                            <p class="text-[11px] text-gray-400 mb-2">Attach a formal quote, brochure, or other supporting file. This does not replace your itemized pricing above.</p>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="doc in (combinedDocuments || [])" :key="doc.id">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1.5 rounded-md bg-white border border-gray-200 text-gray-700 shadow-2xs">
                                        <i class="fa-solid" :class="doc.is_image ? 'fa-file-image text-emerald-500' : 'fa-file-pdf text-red-500'"></i>
                                        <a :href="doc.url" target="_blank" class="font-medium text-gray-800 hover:text-indigo-600" x-text="doc.name"></a>
                                        <span class="text-gray-400 font-mono" x-text="'(' + doc.size + ')'"></span>
                                        <button type="button" @click="deleteCombinedDocument(doc.id)" class="text-red-400 hover:text-red-600 ml-1"><i class="fa-solid fa-xmark"></i></button>
                                    </span>
                                </template>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer"
                                   :class="combinedDocUploading ? 'opacity-50 pointer-events-none' : ''">
                                <i class="fa-solid" :class="combinedDocUploading ? 'fa-spinner fa-spin' : 'fa-upload'"></i>
                                <span x-text="combinedDocUploading ? 'Uploading…' : 'Upload File'"></span>
                                <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip,.csv,.txt"
                                       @change="if ($event.target.files[0]) { uploadCombinedDocument($event.target.files[0]); $event.target.value = ''; }">
                            </label>
                            <p class="text-[11px] text-gray-400 mt-1.5">PDF, Word, Excel, or image — up to 10MB.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-backend.input name="warranty_terms" label="Warranty Terms" :value="old('warranty_terms', $quotation?->warranty_terms ?? $cloneSource?->warranty_terms)" placeholder="e.g. 1 Year Standard" />
                            <x-backend.input name="support_terms" label="Support Terms" :value="old('support_terms', $quotation?->support_terms ?? $cloneSource?->support_terms)" placeholder="e.g. 24/7 Phone Support" />
                            <x-backend.input name="payment_terms" label="Payment Terms" :value="old('payment_terms', $quotation?->payment_terms ?? $cloneSource?->payment_terms)" placeholder="e.g. Net 30" />
                        </div>
                    </div>
                </x-backend.form-card>

                <div class="flex justify-start">
                    <button type="button" @click="setStep(2)" class="text-sm font-semibold px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Back
                    </button>
                </div>
            </div>

        </div>

        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Quotation Summary">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Subtotal</span><span class="font-semibold text-gray-800" x-text="formatMoney(subtotal())"></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Tax</span><span class="font-semibold text-gray-800" x-text="formatMoney(totalTax())"></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Discount</span><span class="font-semibold text-gray-800">-<span x-text="formatMoney(totalDiscount())"></span></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Shipping</span><span class="font-semibold text-gray-800" x-text="formatMoney(parseFloat(shippingCharge || 0))"></span></div>
                    <div class="flex justify-between py-2 text-sm"><span class="text-gray-700 font-semibold">Grand Total</span><span class="font-bold text-indigo-700 text-base" x-text="formatMoney(grandTotal())"></span></div>
                </div>

                @if($isRevision)
                    <button type="submit" class="btn-primary w-full text-sm font-bold py-3 rounded-xl flex items-center justify-center gap-2 mt-4 shadow-sm">
                        <i class="fa-solid fa-rotate"></i> Submit Revised Quotation
                    </button>
                @else
                    <button type="submit" class="w-full text-sm font-semibold py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 mt-4">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Save Draft
                    </button>
                    <p class="text-[11px] text-gray-400 mt-2 text-center">Review and submit from the quotation page when you're ready.</p>
                @endif
            </x-backend.form-card>
        </div>

    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quotationForm', (config) => ({
            items: config.items,
            addons: config.addons,
            rfqItemsById: config.rfqItemsById,
            allowAlternativeProducts: config.allowAlternativeProducts,
            categoryOptions: config.categoryOptions,
            currencyCode: config.currencyCode,
            shippingCharge: config.shippingCharge,

            quotationId: config.quotationId,
            combinedDocuments: config.combinedDocuments,
            combinedDocUploading: false,
            isSaving: false,
            saveError: null,
            currentStep: config.initialStep || 1,
            // Forward-only ratchet: the furthest step tab reachable directly.
            // Step 1 is always reachable; anything beyond stays locked until
            // goNext() advances past it.
            maxCompletedStep: config.maxCompletedStep || 1,
            stepDefs: [{ num: 1 }, { num: 2 }, { num: 3 }],

            isStepLocked(n) {
                return n !== 1 && n > this.maxCompletedStep;
            },
            get completionPercent() {
                return Math.round((Math.min(this.maxCompletedStep, this.stepDefs.length) / this.stepDefs.length) * 100);
            },
            stepValid(n) {
                if (n === 1) {
                    if (this.items.length === 0) return false;
                    return this.items.every(i => {
                        if (i.offers && i.offers.length > 0) {
                            const primary = i.offers.find(o => o.is_primary) || i.offers[0];
                            return primary && parseFloat(primary.unit_price) >= 0 && primary.unit_price !== null && primary.unit_price !== '';
                        }
                        return parseFloat(i.unit_price) >= 0 && i.unit_price !== null && i.unit_price !== '';
                    });
                }
                if (n === 2) return true;
                if (n === 3) return this.stepValid(1) && this.stepValid(2);
                return true;
            },
            setStep(step) {
                if (this.currentStep === step) return;
                if (this.isStepLocked(step)) return;
                this.currentStep = step;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (this.quotationId) this.autosave();
            },
            async goNext(n) {
                if (!this.stepValid(n)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'Not quite ready', text: 'Every item needs at least one offer with a Unit Price before moving on.' });
                    }
                    this.items.forEach(item => {
                        item._collapsed = false;
                        if (item.offers) item.offers.forEach(o => o._collapsed = false);
                    });
                    return;
                }
                this.currentStep = n + 1;
                this.maxCompletedStep = Math.max(this.maxCompletedStep, n + 1);
                const saved = await this.autosave();
                if (!saved) {
                    this.currentStep = n;
                    return;
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            async autosave() {
                if (this.isRevision) return true;
                this.isSaving = true;
                this.saveError = null;
                try {
                    const url = this.quotationId ? (config.autosaveUpdateUrlBase + '/' + this.quotationId + '/autosave') : config.autosaveCreateUrl;
                    const itemsPayload = this.items.map(item => {
                        this.syncItemWithPrimaryOffer(item);
                        return {
                            ...item,
                            client_ref: item._localKey,
                            response_method: item._responseMethod,
                            offers: (item.offers || []).map(o => ({
                                ...o,
                                specifications: this.getOfferSpecsPayload(o),
                            })),
                        };
                    });
                    const res = await fetch(url, {
                        method: this.quotationId ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            items: itemsPayload, addons: this.addons,
                            currency_code: this.currencyCode, shipping_charge: this.shippingCharge,
                            current_step: this.currentStep, max_completed_step: this.maxCompletedStep,
                        }),
                    });
                    if (!res.ok) {
                        this.saveError = 'Could not save your progress — check your connection and try again.';
                        return false;
                    }
                    const data = await res.json();
                    if (!this.quotationId && data.id) this.quotationId = data.id;
                    if (data.items) {
                        this.items.forEach(item => {
                            const assignedId = data.items[item._localKey];
                            if (assignedId && !item.id) item.id = assignedId;
                        });
                    }
                    return true;
                } catch (e) {
                    this.saveError = 'Could not save your progress — check your connection and try again.';
                    return false;
                } finally {
                    this.isSaving = false;
                }
            },

            init() {
                this.restoreFromMarketplaceSelection();
                this.items.forEach(item => {
                    this.syncItemWithPrimaryOffer(item);
                    const buyerItem = this.rfqItemsById[item.rfq_item_id];
                    if (buyerItem) this.fetchItemAttributes(item, buyerItem.category_id);
                });
                this.loadAutoMatches();
            },

            generateLocalKey() {
                return (window.crypto && window.crypto.randomUUID) ? window.crypto.randomUUID() : Math.random().toString(36).slice(2);
            },

            itemsForRfq(rfqItemId) {
                return this.items.filter(i => String(i.rfq_item_id) === String(rfqItemId));
            },
            extraItems() {
                return this.items.filter(i => !i.rfq_item_id);
            },
            allocatedQty(rfqItemId) {
                return this.itemsForRfq(rfqItemId).reduce((sum, item) => {
                    const primary = (item.offers && item.offers.length > 0)
                        ? (item.offers.find(o => o.is_primary) || item.offers[0])
                        : item;
                    return sum + parseFloat(primary.quantity || 0);
                }, 0);
            },
            addProduct(rfqItemId) {
                const buyerItem = this.rfqItemsById[rfqItemId];
                const newItem = {
                    id: null,
                    rfq_item_id: rfqItemId,
                    offered_listing_id: null,
                    offered_variant_id: null,
                    is_alternative: false,
                    item_name: buyerItem?.item_name ?? '',
                    description: null,
                    quantity: buyerItem?.quantity ?? '1',
                    unit_id: buyerItem?.unit_id ?? null,
                    custom_unit: null,
                    unit_price: null,
                    tax_rate: null,
                    discount_amount: null,
                    lead_time_days: null,
                    attribute_values: {},
                    specs: [],
                    offers: [],
                    _responseMethod: null,
                    _localKey: this.generateLocalKey(),
                    _collapsed: false,
                    _documents: [],
                    _uploadPreparing: false,
                    _attrLoading: false,
                    _attrGroups: [],
                    _variants: [],
                    _suggestedListing: null,
                };
                this.items.push(newItem);
            },
            removeProduct(item) {
                const idx = this.items.findIndex(i => i._localKey === item._localKey);
                if (idx !== -1) {
                    this.items.splice(idx, 1);
                }
            },

            openMarketplaceSelector(item) {
                try {
                    const raw = (window.Alpine && window.Alpine.raw) ? window.Alpine.raw(this.items) : this.items;
                    sessionStorage.setItem('quotationItemsSnapshot', JSON.stringify(raw));
                } catch (e) {}

                const returnUrl = window.location.href.split('#')[0];
                const url = new URL(config.selectProductUrl, window.location.origin);
                url.searchParams.set('rfq_item_id', item.rfq_item_id ?? '');
                url.searchParams.set('item_token', item._localKey);
                url.searchParams.set('return_url', returnUrl);
                window.location.href = url.toString();
            },

            async restoreFromMarketplaceSelection() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('restore_items') !== '1') return;

                try {
                    const saved = sessionStorage.getItem('quotationItemsSnapshot');
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (Array.isArray(parsed) && parsed.length > 0) this.items = parsed;
                    }
                } catch (e) {}
                sessionStorage.removeItem('quotationItemsSnapshot');

                const selectedListingIdsParam = params.get('selected_listing_ids') || params.get('selected_listing_id');
                const itemToken = params.get('item_token');
                const rfqItemId = params.get('rfq_item_id');

                if (selectedListingIdsParam) {
                    const listingIds = selectedListingIdsParam.split(',')
                        .map(s => parseInt(s.trim(), 10))
                        .filter(n => !isNaN(n));

                    let item = itemToken ? this.items.find(i => i._localKey === itemToken) : null;
                    if (!item && rfqItemId) {
                        item = this.items.find(i => String(i.rfq_item_id) === String(rfqItemId));
                    }

                    if (item && listingIds.length > 0) {
                        item._collapsed = false;
                        if (!Array.isArray(item.offers)) item.offers = [];

                        const hasExistingRealOffers = item.offers.some(o => o.marketplace_product_id || (parseFloat(o.unit_price) > 0));

                        for (let idx = 0; idx < listingIds.length; idx++) {
                            const lid = listingIds[idx];
                            try {
                                const res = await fetch(config.listingsPrefillUrl + '/' + lid + '/prefill');
                                if (!res.ok) continue;
                                const data = await res.json();
                                
                                const isFirstInBatch = idx === 0;
                                const isPrimary = !hasExistingRealOffers && isFirstInBatch;
                                const sortOrder = hasExistingRealOffers ? (item.offers.length + idx) : idx;

                                const customSpecs = [];
                                if (data.attribute_values) {
                                    Object.entries(data.attribute_values).forEach(([attrId, val]) => {
                                        if (val && val.value_text) {
                                            customSpecs.push({ name: 'Spec #' + attrId, value: val.value_text });
                                        }
                                    });
                                }

                                const offer = {
                                    id: null,
                                    offer_method: 'marketplace',
                                    marketplace_product_id: data.item.offered_listing_id,
                                    offered_variant_id: null,
                                    product_name: data.item.product_name || data.item.item_name,
                                    category_id: data.item.category_id,
                                    description: data.item.description || '',
                                    specifications: customSpecs,
                                    quantity: item.quantity || data.item.quantity || '1',
                                    unit_id: data.item.unit_id || item.unit_id,
                                    custom_unit: null,
                                    unit_price: data.item.unit_price || '',
                                    tax_rate: item.tax_rate ?? null,
                                    discount: null,
                                    delivery_time: item.lead_time_days ?? null,
                                    is_primary: isPrimary,
                                    is_selected: false,
                                    sort_order: sortOrder,
                                    _localKey: this.generateLocalKey(),
                                    _collapsed: !isPrimary,
                                    _image_url: data.item.image_url,
                                    _brand_name: data.item.brand_name,
                                    _category_name: data.item.category_name,
                                    _slug: data.item.slug,
                                    _variants: data.variants || [],
                                    _attrGroups: data.category_attributes || [],
                                    _attrLoading: false,
                                    _attribute_values: data.attribute_values || {},
                                    _custom_specs: customSpecs,
                                    _documents: [],
                                };

                                if (!hasExistingRealOffers && isFirstInBatch) {
                                    item.offers = [offer];
                                } else {
                                    item.offers.push(offer);
                                }
                            } catch (err) {
                                console.error('Error prefilling listing', lid, err);
                            }
                        }

                        this.syncItemWithPrimaryOffer(item);
                    }
                }

                params.delete('restore_items');
                params.delete('selected_listing_ids');
                params.delete('selected_listing_id');
                params.delete('item_token');
                params.delete('rfq_item_id');
                const query = params.toString();
                const cleanUrl = window.location.pathname + (query ? '?' + query : '');
                window.history.replaceState({}, '', cleanUrl);
            },

            addCustomOffer(item, makePrimary = true) {
                if (!Array.isArray(item.offers)) item.offers = [];
                const isPrimary = makePrimary && (item.offers.length === 0 || !item.offers.some(o => o.is_primary));
                const buyerItem = this.rfqItemsById[item.rfq_item_id];
                
                const offer = {
                    id: null,
                    offer_method: 'custom',
                    marketplace_product_id: null,
                    offered_variant_id: null,
                    product_name: item.item_name || (buyerItem?.item_name ?? ''),
                    category_id: buyerItem?.category_id ?? null,
                    description: item.description || (buyerItem?.description ?? ''),
                    specifications: [],
                    quantity: item.quantity || (buyerItem?.quantity ?? '1'),
                    unit_id: item.unit_id || (buyerItem?.unit_id ?? null),
                    custom_unit: null,
                    unit_price: '',
                    tax_rate: null,
                    discount: null,
                    delivery_time: null,
                    is_primary: isPrimary,
                    is_selected: false,
                    sort_order: item.offers.length,
                    _localKey: this.generateLocalKey(),
                    _collapsed: false,
                    _image_url: null,
                    _brand_name: null,
                    _category_name: buyerItem?.category_name ?? null,
                    _variants: [],
                    _attrGroups: [],
                    _attrLoading: false,
                    _attribute_values: {},
                    _custom_specs: [],
                    _documents: [],
                };
                
                item.offers.push(offer);
                item._collapsed = false;
                this.syncItemWithPrimaryOffer(item);
            },

            addCopyBuyerSpecOffer(item, makePrimary = true) {
                if (!Array.isArray(item.offers)) item.offers = [];
                const isPrimary = makePrimary && (item.offers.length === 0 || !item.offers.some(o => o.is_primary));
                const buyerItem = this.rfqItemsById[item.rfq_item_id];
                
                const customSpecs = [];
                if (buyerItem?.specs && Array.isArray(buyerItem.specs)) {
                    buyerItem.specs.forEach(s => {
                        if (s.name && !s.name.startsWith('__')) {
                            customSpecs.push({ name: s.name, value: s.value || '' });
                        }
                    });
                }

                const offer = {
                    id: null,
                    offer_method: 'copy_spec',
                    marketplace_product_id: null,
                    offered_variant_id: null,
                    product_name: buyerItem?.item_name || item.item_name || '',
                    category_id: buyerItem?.category_id ?? null,
                    description: buyerItem?.description || item.description || '',
                    specifications: customSpecs,
                    quantity: buyerItem?.quantity || item.quantity || '1',
                    unit_id: buyerItem?.unit_id || item.unit_id || null,
                    custom_unit: null,
                    unit_price: buyerItem?.estimated_unit_price ? parseFloat(String(buyerItem.estimated_unit_price).replace(/,/g, '')) : '',
                    tax_rate: null,
                    discount: null,
                    delivery_time: null,
                    is_primary: isPrimary,
                    is_selected: false,
                    sort_order: item.offers.length,
                    _localKey: this.generateLocalKey(),
                    _collapsed: false,
                    _image_url: buyerItem?.listing_image_url ?? null,
                    _brand_name: null,
                    _category_name: buyerItem?.category_name ?? null,
                    _variants: [],
                    _attrGroups: [],
                    _attrLoading: false,
                    _attribute_values: JSON.parse(JSON.stringify(buyerItem?.attribute_values_raw || {})),
                    _custom_specs: customSpecs,
                    _documents: [],
                };

                item.offers.push(offer);
                item._collapsed = false;
                this.syncItemWithPrimaryOffer(item);
            },

            addDocumentOffer(item, makePrimary = true) {
                if (!Array.isArray(item.offers)) item.offers = [];
                const isPrimary = makePrimary && (item.offers.length === 0 || !item.offers.some(o => o.is_primary));
                const buyerItem = this.rfqItemsById[item.rfq_item_id];
                
                const offer = {
                    id: null,
                    offer_method: 'document',
                    marketplace_product_id: null,
                    offered_variant_id: null,
                    product_name: (buyerItem?.item_name ? (buyerItem.item_name + ' — Document Quotation') : 'Quotation Document'),
                    category_id: buyerItem?.category_id ?? null,
                    description: '',
                    specifications: [],
                    quantity: '1',
                    unit_id: null,
                    custom_unit: null,
                    unit_price: '',
                    tax_rate: null,
                    discount: null,
                    delivery_time: null,
                    is_primary: isPrimary,
                    is_selected: false,
                    sort_order: item.offers.length,
                    _localKey: this.generateLocalKey(),
                    _collapsed: false,
                    _image_url: null,
                    _brand_name: null,
                    _category_name: null,
                    _variants: [],
                    _attrGroups: [],
                    _attrLoading: false,
                    _attribute_values: {},
                    _custom_specs: [],
                    _documents: [],
                };

                item.offers.push(offer);
                item._collapsed = false;
                this.syncItemWithPrimaryOffer(item);
            },

            makePrimaryOffer(item, targetOffer) {
                if (!item.offers) return;
                item.offers.forEach(o => {
                    o.is_primary = (o._localKey === targetOffer._localKey);
                });
                this.syncItemWithPrimaryOffer(item);
            },

            removeOffer(item, offerIndex) {
                if (!item.offers) return;
                const removedWasPrimary = item.offers[offerIndex]?.is_primary;
                item.offers.splice(offerIndex, 1);
                if (removedWasPrimary && item.offers.length > 0) {
                    item.offers[0].is_primary = true;
                }
                item.offers.forEach((o, idx) => { o.sort_order = idx; });
                this.syncItemWithPrimaryOffer(item);
            },

            addCustomSpec(offer) {
                if (!Array.isArray(offer._custom_specs)) offer._custom_specs = [];
                offer._custom_specs.push({ name: '', value: '' });
            },

            removeCustomSpec(offer, specIndex) {
                if (!Array.isArray(offer._custom_specs)) return;
                offer._custom_specs.splice(specIndex, 1);
            },

            getOfferSpecsPayload(offer) {
                const specs = [];
                if (Array.isArray(offer._custom_specs)) {
                    offer._custom_specs.forEach(s => {
                        if (s.name && s.name.trim() !== '') {
                            specs.push({ name: s.name.trim(), value: s.value || '' });
                        }
                    });
                }
                return specs;
            },

            methodLabel(key) {
                switch(key) {
                    case 'marketplace': return 'Catalog Product';
                    case 'custom': return 'Custom Offer';
                    case 'copy_spec': return 'Copied Spec';
                    case 'document': return 'Quotation Doc';
                    default: return 'Offer';
                }
            },

            syncItemWithPrimaryOffer(item) {
                if (!item.offers || item.offers.length === 0) return;
                const primary = item.offers.find(o => o.is_primary) || item.offers[0];
                if (primary) {
                    item.item_name = primary.product_name || item.item_name;
                    item.unit_price = primary.unit_price;
                    item.quantity = primary.quantity;
                    item.unit_id = primary.unit_id;
                    item.custom_unit = primary.custom_unit;
                    item.tax_rate = primary.tax_rate;
                    item.discount_amount = primary.discount;
                    item.lead_time_days = primary.delivery_time;
                    item.offered_listing_id = primary.marketplace_product_id;
                    item.offered_variant_id = primary.offered_variant_id;
                    item._responseMethod = primary.offer_method;
                    item.description = primary.description;
                }
            },

            offerTotal(offer) {
                const qty = parseFloat(offer.quantity || 0);
                const price = parseFloat(offer.unit_price || 0);
                const lineSubtotal = qty * price;
                const discount = parseFloat(offer.discount || 0);
                const taxRate = (offer.tax_rate !== null && offer.tax_rate !== '' && offer.tax_rate !== undefined) ? parseFloat(offer.tax_rate) : null;
                const tax = taxRate !== null ? (lineSubtotal - discount) * taxRate / 100 : 0;
                return lineSubtotal - discount + tax;
            },

            loadAutoMatches() {
                fetch(config.autoMatchUrl)
                    .then(r => r.json())
                    .then(matches => {
                        this.items.forEach(item => {
                            const match = matches[item.rfq_item_id];
                            if (match && !item.offered_listing_id && !item.unit_price) {
                                item._suggestedListing = match;
                            }
                        });
                    })
                    .catch(() => {});
            },
            useSuggestedListing(item) {
                if (!item._suggestedListing) return;
                item._responseMethod = 'marketplace';
                this.selectListingForItem(item, { id: item._suggestedListing.listing_id, name: item._suggestedListing.name });
                item._suggestedListing = null;
            },
            dismissSuggestedListing(item) { item._suggestedListing = null; },

            applyCopyBuyerRequirements(item) {
                this.addCopyBuyerSpecOffer(item, true);
            },

            copyBuyerRequirementsToAll() {
                this.items.forEach(item => this.applyCopyBuyerRequirements(item));
            },

            addItem() {
                this.collapseAllItems();
                this.items.push({
                    id: null, rfq_item_id: null, offered_listing_id: null, offered_variant_id: null, is_alternative: false,
                    item_name: '', description: '', quantity: '1', unit_id: null, custom_unit: null,
                    unit_price: null, tax_rate: null, discount_amount: null, lead_time_days: null,
                    attribute_values: {}, offers: [], _responseMethod: null, _localKey: this.generateLocalKey(),
                    _collapsed: false,
                    _documents: [], _uploadPreparing: false,
                    _attrLoading: false, _attrGroups: [], _variants: [], _suggestedListing: null,
                });
            },
            removeItem(index) {
                this.items.splice(index, 1);
                if (this.items.length === 1) this.items[0]._collapsed = false;
            },

            collapseAllItems() { this.items.forEach(i => { i._collapsed = true; if (i.offers) i.offers.forEach(o => o._collapsed = true); }); },
            expandAllItems() { this.items.forEach(i => { i._collapsed = false; if (i.offers) i.offers.forEach(o => o._collapsed = false); }); },

            addAddon() {
                this.addons.push({ id: null, item_name: '', description: '', quantity: '1', unit_id: null, unit_price: null, tax_rate: null, discount_amount: null, lead_time_days: null });
            },
            removeAddon(index) { this.addons.splice(index, 1); },

            async uploadCombinedDocument(file) {
                if (!this.quotationId) return;
                this.combinedDocUploading = true;
                try {
                    const body = new FormData();
                    body.append('document', file);
                    const res = await fetch(config.combinedDocumentUrlBase + '/' + this.quotationId + '/combined-document', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body,
                    });
                    if (!res.ok) return;
                    const media = await res.json();
                    this.combinedDocuments = [...(this.combinedDocuments || []), media];
                } finally {
                    this.combinedDocUploading = false;
                }
            },
            async deleteCombinedDocument(mediaId) {
                if (!this.quotationId) return;
                await fetch(config.combinedDocumentUrlBase + '/' + this.quotationId + '/combined-document/' + mediaId, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                });
                this.combinedDocuments = (this.combinedDocuments || []).filter(d => d.id !== mediaId);
            },

            selectListingForItem(item, listing) {
                fetch(config.listingsPrefillUrl + '/' + listing.id + '/prefill')
                    .then(r => r.json())
                    .then(data => {
                        const offer = {
                            id: null,
                            offer_method: 'marketplace',
                            marketplace_product_id: data.item.offered_listing_id,
                            offered_variant_id: null,
                            product_name: data.item.product_name || data.item.item_name,
                            category_id: data.item.category_id,
                            description: data.item.description || '',
                            specifications: [],
                            quantity: item.quantity || data.item.quantity || '1',
                            unit_id: data.item.unit_id || item.unit_id,
                            custom_unit: null,
                            unit_price: data.item.unit_price || '',
                            tax_rate: item.tax_rate ?? null,
                            discount: null,
                            delivery_time: item.lead_time_days ?? null,
                            is_primary: true,
                            is_selected: false,
                            sort_order: 0,
                            _localKey: this.generateLocalKey(),
                            _collapsed: false,
                            _image_url: data.item.image_url,
                            _brand_name: data.item.brand_name,
                            _category_name: data.item.category_name,
                            _variants: data.variants || [],
                            _attrGroups: data.category_attributes || [],
                            _attrLoading: false,
                            _attribute_values: data.attribute_values || {},
                            _custom_specs: [],
                            _documents: [],
                        };
                        item.offers = [offer];
                        this.syncItemWithPrimaryOffer(item);
                    });
            },
            clearListingForItem(item) {
                item.offered_listing_id = null;
                item.offered_variant_id = null;
                item._variants = [];
                item.offers = [];
            },

            fetchItemAttributes(item, categoryId) {
                if (!categoryId) { item._attrGroups = []; return; }
                item._attrLoading = true;
                let url = config.categoryAttributesUrl + '/' + categoryId + '/attributes';
                const keepIds = Object.keys(item.attribute_values || {});
                if (keepIds.length > 0) {
                    url += '?keep_attribute_ids=' + keepIds.join(',');
                }
                fetch(url)
                    .then(r => r.json())
                    .then(data => { item._attrGroups = data.groups || []; })
                    .finally(() => { item._attrLoading = false; });
            },

            getAttrVal(item, attrId) {
                if (!item.attribute_values[attrId]) {
                    item.attribute_values[attrId] = {
                        attribute_value_id: null, custom_value: null, value_text: null,
                        value_number: null, value_boolean: null, value_date: null, value_json: [],
                    };
                }
                return item.attribute_values[attrId];
            },

            lineTotal(row) {
                const qty = parseFloat(row.quantity || 0);
                const price = parseFloat(row.unit_price || 0);
                const lineSubtotal = qty * price;
                const discount = parseFloat(row.discount_amount || row.discount || 0);
                const taxRate = (row.tax_rate !== null && row.tax_rate !== '' && row.tax_rate !== undefined) ? parseFloat(row.tax_rate) : null;
                const tax = taxRate !== null ? (lineSubtotal - discount) * taxRate / 100 : 0;
                return lineSubtotal - discount + tax;
            },
            lineTax(row) {
                const qty = parseFloat(row.quantity || 0);
                const price = parseFloat(row.unit_price || 0);
                const lineSubtotal = qty * price;
                const discount = parseFloat(row.discount_amount || row.discount || 0);
                const taxRate = (row.tax_rate !== null && row.tax_rate !== '' && row.tax_rate !== undefined) ? parseFloat(row.tax_rate) : null;
                return taxRate !== null ? (lineSubtotal - discount) * taxRate / 100 : 0;
            },
            formatMoney(n) {
                return (this.currencyCode || 'USD') + ' ' + (isNaN(n) ? '0.00' : Number(n).toFixed(2));
            },
            allRows() { return [...this.items, ...this.addons]; },
            subtotal() {
                return this.allRows().reduce((s, r) => {
                    return s + parseFloat(r.quantity || 0) * parseFloat(r.unit_price || 0);
                }, 0);
            },
            totalTax() { return this.allRows().reduce((s, r) => s + this.lineTax(r), 0); },
            totalDiscount() {
                return this.allRows().reduce((s, r) => {
                    return s + parseFloat(r.discount_amount || r.discount || 0);
                }, 0);
            },
            grandTotal() { return this.subtotal() - this.totalDiscount() + this.totalTax() + parseFloat(this.shippingCharge || 0); },
        }));
    });
</script>
@endpush
