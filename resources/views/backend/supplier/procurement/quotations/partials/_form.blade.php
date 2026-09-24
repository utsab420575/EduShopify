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
            $attrs = $i->listing->attributeValues->map(function ($v) {
                $val = $v->custom_value ?? $v->value_text;
                if ($val === null && $v->value_number !== null) {
                    $val = rtrim(rtrim((string)$v->value_number, '0'), '.');
                }
                if ($val === null) {
                    $val = $v->attributeValue?->name;
                }
                return [
                    'id'         => $v->attribute_id,
                    'name'       => $v->attribute?->name,
                    'value'      => $val,
                    'group_name' => $v->attribute?->attributeGroup?->name ?: 'Key Features',
                    'group_sort' => $v->attribute?->attributeGroup?->sort_order ?? 999,
                    'attr_sort'  => $v->attribute?->sort_order ?? 999,
                ];
            })->filter(fn ($a) => !empty($a['name']) && !empty($a['value']));
            foreach ($i->listing->attributeValues as $v) {
                $attrsRawById[$v->attribute_id] = $toRawAttrValue($v);
            }
        }
        if ($i->attributeValues->isNotEmpty()) {
            $customAttrs = $i->attributeValues->map(function ($v) {
                return [
                    'id'         => $v->attribute_id,
                    'name'       => $v->attribute?->name,
                    'value'      => $v->formattedValue(),
                    'group_name' => $v->attribute?->attributeGroup?->name ?: 'Key Features',
                    'group_sort' => $v->attribute?->attributeGroup?->sort_order ?? 999,
                    'attr_sort'  => $v->attribute?->sort_order ?? 999,
                ];
            })->filter(fn ($a) => !empty($a['name']) && !empty($a['value']));
            $attrs = $attrs->keyBy('name')->merge($customAttrs->keyBy('name'))->values();
            foreach ($i->attributeValues as $v) {
                $attrsRawById[$v->attribute_id] = $toRawAttrValue($v);
            }
        }

        $groupedAttributes = $attrs->groupBy('group_name')->map(function ($groupAttrs, $groupName) {
            return [
                'group_name' => $groupName,
                'group_sort' => $groupAttrs->first()['group_sort'] ?? 999,
                'attributes' => $groupAttrs->sortBy('attr_sort')->values()->all(),
            ];
        })->sortBy('group_sort')->values()->all();

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
            'grouped_attributes' => $groupedAttributes,
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
                        'shipping_charge' => $o->shipping_charge,
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
                        // Seeded from this offer's OWN quotation_item_attribute_values
                        // rows (QuotationItemOffer::attributeValues(), eager-loaded in
                        // QuotationController::edit()) — same shape init() below expects
                        // getOfferAttrVal()/_offer-attributes.blade.php to read/write.
                        // _attrGroups (the field DEFINITIONS) still loads separately via
                        // fetchOfferAttributes() in init(), since that comes from the
                        // category's attribute config, not the offer's saved values.
                        '_attribute_values' => $o->relationLoaded('attributeValues')
                            ? (object) $o->attributeValues->mapWithKeys(fn ($v) => [$v->attribute_id => [
                                'attribute_value_id' => $v->attribute_value_id, 'custom_value' => $v->custom_value,
                                'value_text' => $v->value_text, 'value_number' => $v->value_number,
                                'value_boolean' => $v->value_boolean, 'value_date' => $v->value_date, 'value_json' => $v->value_json,
                            ]])->all()
                            : [],
                        '_attrCategoryId' => null,
                        '_categorySearch' => '',
                        '_categoryPickerOpen' => false,
                        '_copyBuyerSpecs' => false,
                        '_custom_specs' => is_array($o->specifications) ? $o->specifications : [],
                        '_documents' => method_exists($o, 'getMedia') ? $o->getMedia('document')->map(fn ($m) => [
                            'id' => $m->id, 'name' => $m->file_name, 'size' => $m->human_readable_size,
                            'url' => $m->getUrl(), 'is_image' => str_starts_with($m->mime_type ?? '', 'image/'),
                        ])->values()->all() : [],
                        '_uploadPreparing' => false,
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
                    'shipping_charge' => 0,
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
                    '_attrCategoryId' => null,
                    '_categorySearch' => '',
                    '_categoryPickerOpen' => false,
                    '_copyBuyerSpecs' => false,
                    '_custom_specs' => is_array($item->specs) ? $item->specs : [],
                    '_documents' => [],
                    '_uploadPreparing' => false,
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

        // Seeded once server-side (QuotationService::copyDeliveryAddressesFromRfq(),
        // called the moment the quotation row is first created) from the RFQ's
        // own address(es) — from here on this is purely the quotation's own,
        // independently editable list; sort_order 0 is always rendered as
        // "Address 1" and can never be removed, only cleared.
        $initialDeliveryAddresses = $quotation->deliveryAddresses->map(fn ($a) => [
            'country_id' => $a->country_id ?? 0, 'state_id' => $a->state_id ?? 0, 'city_id' => $a->city_id ?? 0,
            'address' => $a->address ?? '', '_states' => [], '_cities' => [],
        ])->values();
        if ($initialDeliveryAddresses->isEmpty()) {
            $initialDeliveryAddresses = collect([['country_id' => 0, 'state_id' => 0, 'city_id' => 0, 'address' => '', '_states' => [], '_cities' => []]]);
        }
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
        $initialDeliveryAddresses = collect([['country_id' => 0, 'state_id' => 0, 'city_id' => 0, 'address' => '', '_states' => [], '_cities' => []]]);
    }
@endphp

@push('styles')
    {{-- Flatpickr — replaces the native browser date widget (whose look
         varies per browser) with one consistent calendar UI, same as the
         buyer's own RFQ form. Also what the date-type structured attribute
         fields in _offer-attributes.blade.php already expect (they
         gracefully no-op without it), so this fixes those too. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar { font-family: 'Inter', sans-serif; box-shadow: 0 10px 30px -5px rgba(0,0,0,.15), 0 0 0 1px rgba(0,0,0,.05); }
        .flatpickr-day.selected, .flatpickr-day.selected:hover { background: var(--theme-primary, #4f46e5); border-color: var(--theme-primary, #4f46e5); }
    </style>
@endpush

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<form
    method="POST"
    action="{{ $action }}"
    {{-- Only reached by a real native submit now — "Submit Revised
         Quotation" (isRevision mode). "Save Draft" is a type="button" that
         goes through saveDraftManually()/autosave() instead (stays on this
         page, no redirect) and sets isSaving itself. Reuses the same flag
         the autosave status badge above already shows, so this button also
         visibly switches to "Saving…" the instant it's submitted (click or
         Enter) — no need to clear it back afterwards, since a real page
         navigation follows (redirect to the quotation show page) that tears
         down this whole Alpine instance and its state along with it. --}}
    @submit="isSaving = true"
    x-data="quotationForm({
        items: {{ $initialItems->toJson() }},
        addons: {{ $initialAddons->toJson() }},
        rfqItemsById: {{ $rfqItemsById->toJson() }},
        allowAlternativeProducts: {{ $rfq->allow_alternative_products ? 'true' : 'false' }},
        currencyCode: '{{ old('currency_code', $quotation?->currency_code ?? $cloneSource?->currency_code ?? $rfq->currency_code ?? 'USD') }}',
        title: {!! \Illuminate\Support\Js::from(old('title', $quotation?->title ?? '')) !!},
        description: {!! \Illuminate\Support\Js::from(old('description', $quotation?->description ?? '')) !!},
        expectedDeliveryDate: {!! \Illuminate\Support\Js::from(old('expected_delivery_date', optional($quotation?->expected_delivery_date)->format('Y-m-d') ?? '')) !!},
        validUntil: {!! \Illuminate\Support\Js::from(old('valid_until', optional($quotation?->valid_until)->format('Y-m-d') ?? '')) !!},
        warrantyTerms: {!! \Illuminate\Support\Js::from(old('warranty_terms', $quotation?->warranty_terms ?? $cloneSource?->warranty_terms ?? '')) !!},
        supportTerms: {!! \Illuminate\Support\Js::from(old('support_terms', $quotation?->support_terms ?? $cloneSource?->support_terms ?? '')) !!},
        paymentTerms: {!! \Illuminate\Support\Js::from(old('payment_terms', $quotation?->payment_terms ?? $cloneSource?->payment_terms ?? '')) !!},
        proposal: {!! \Illuminate\Support\Js::from(old('proposal', $quotation?->proposal ?? $cloneSource?->proposal ?? '')) !!},
        deliveryAddresses: {{ $initialDeliveryAddresses->toJson() }},
        statesUrl: '{{ url('/lookup/countries') }}',
        citiesUrl: '{{ url('/lookup/states') }}',
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
                    2 => ['label' => 'Detail & Delivery', 'icon' => 'fa-truck-fast'],
                    3 => ['label' => 'Preview & Submit', 'icon' => 'fa-circle-check'],
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
                        Next: Detail &amp; Delivery <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- ═══════ STEP 2 — Detail & Delivery ═══════ --}}
            <div x-show="currentStep === 2" x-cloak class="space-y-6">
                <x-backend.form-card title="Basic Information">
                    <div class="space-y-4">
                        @if($isRevision)
                            <x-backend.textarea name="change_summary" label="What changed in this revision?" required :value="old('change_summary')" />
                        @endif
                        <x-backend.input name="title" label="Quotation Title" x-model="title" placeholder="e.g. Laptop Supply Quotation" />
                        <x-backend.textarea name="description" label="Quotation Description" x-model="description" placeholder="e.g. Complete laptop supply with warranty and support." />
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Delivery" description="Overall delivery time and one or more delivery locations for this quotation.">
                    <div class="space-y-4">
                        <x-backend.input type="text" name="expected_delivery_date" label="Overall Delivery Time"
                                         x-model="expectedDeliveryDate" x-ref="expectedDeliveryInput"
                                         autocomplete="off" placeholder="Select date" />

                        <div class="pt-3 border-t border-gray-100 space-y-4">
                            <template x-for="(addr, idx) in deliveryAddresses" :key="idx">
                                <div :class="idx > 0 ? 'pt-4 border-t border-gray-100' : ''">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Address <span x-text="idx + 1"></span></p>
                                        <button type="button" x-show="idx > 0" @click="removeDeliveryAddress(idx)" class="text-xs text-red-500 hover:text-red-700"><i class="fa-solid fa-trash"></i> Remove</button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                                            <select :name="'delivery_addresses['+idx+'][country_id]'" x-model.number="addr.country_id" @change="onDeliveryAddressCountryChange(addr)" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                                <option value="0">Select country</option>
                                                @foreach(\App\Models\Country::active()->get(['id', 'name']) as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                                            <select :name="'delivery_addresses['+idx+'][state_id]'" x-model.number="addr.state_id" @change="onDeliveryAddressStateChange(addr)" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                                <option value="0">Select state</option>
                                                <template x-for="s in addr._states" :key="s.id">
                                                    <option :value="s.id" x-text="s.name" :selected="s.id === addr.state_id"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                                            <select :name="'delivery_addresses['+idx+'][city_id]'" x-model.number="addr.city_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                                <option value="0">Select city</option>
                                                <template x-for="c in addr._cities" :key="c.id">
                                                    <option :value="c.id" x-text="c.name" :selected="c.id === addr.city_id"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Address</label>
                                        <textarea :name="'delivery_addresses['+idx+'][address]'" x-model="addr.address" rows="2" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"></textarea>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="addDeliveryAddress()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <i class="fa-solid fa-plus"></i> Add Address
                            </button>
                        </div>
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Commercial Terms">
                    <div class="space-y-4">
                        {{-- Supplementary, whole-quotation attachment(s) — does not
                             replace the itemized pricing on Step 1. --}}
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
                            <x-backend.input name="warranty_terms" label="Warranty Terms" x-model="warrantyTerms" placeholder="e.g. 1 Year Standard" />
                            <x-backend.input name="support_terms" label="Support Terms" x-model="supportTerms" placeholder="e.g. 24/7 Phone Support" />
                            <x-backend.input name="payment_terms" label="Payment Terms" x-model="paymentTerms" placeholder="e.g. Net 30" />
                        </div>
                    </div>
                </x-backend.form-card>

                <div class="flex justify-between">
                    <button type="button" @click="setStep(1)" class="text-sm font-semibold px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Back
                    </button>
                    <button type="button" @click="goNext(2)" class="btn-primary text-sm font-semibold px-5 py-2.5 rounded-lg flex items-center gap-2">
                        Next: Preview &amp; Submit <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- ═══════ STEP 3 — Preview & Submit ═══════ --}}
            <div x-show="currentStep === 3" x-cloak class="space-y-6">
                <x-backend.form-card title="Review Your Quotation" description="Double-check everything below, then submit from the summary card on the right when you're ready.">
                    @include('backend.supplier.procurement.quotations.partials._review-summary')
                </x-backend.form-card>

                <div class="flex justify-start">
                    <button type="button" @click="setStep(2)" class="text-sm font-semibold px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Back
                    </button>
                </div>
            </div>

        </div>

        {{-- Sticky below the topbar (h-20 = 80px there, so top-24 leaves a
             16px gap) while scrolling the (usually much taller) items column
             on the left — the parent grid already has items-start, so this
             column doesn't stretch to match its height. --}}
        <div class="xl:col-span-4 space-y-6 xl:sticky xl:top-24 self-start">
            <x-backend.form-card title="Quotation Summary" description="Quick recap of what you priced — go back to Step 1 to change anything.">
                <div class="divide-y divide-gray-100 mb-3">
                    <template x-for="item in items" :key="item._localKey">
                        <div class="py-2.5 flex items-center justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 truncate" x-text="item.item_name || 'Untitled item'"></p>
                                <p class="text-[11px] text-gray-400" x-text="item.quantity + ' pcs @ ' + formatMoney(item.unit_price || 0)"></p>
                            </div>
                            <p class="font-semibold text-gray-900 shrink-0" x-text="formatMoney((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0))"></p>
                        </div>
                    </template>
                    <template x-if="items.length === 0">
                        <p class="py-2.5 text-sm text-gray-400 italic">No items priced yet — go back to Step 1.</p>
                    </template>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Subtotal</span><span class="font-semibold text-gray-800" x-text="formatMoney(subtotal())"></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Tax</span><span class="font-semibold text-gray-800" x-text="formatMoney(totalTax())"></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Discount</span><span class="font-semibold text-gray-800">-<span x-text="formatMoney(totalDiscount())"></span></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Shipping</span><span class="font-semibold text-gray-800" x-text="formatMoney(totalShipping())"></span></div>
                    <div class="flex justify-between py-2 text-sm"><span class="text-gray-700 font-semibold">Grand Total</span><span class="font-bold text-indigo-700 text-base" x-text="formatMoney(grandTotal())"></span></div>
                </div>

                @if($isRevision)
                    <button type="submit" :disabled="isSaving"
                            class="btn-primary w-full text-sm font-bold py-3 rounded-xl flex items-center justify-center gap-2 mt-4 shadow-sm hover:shadow-md transition-all disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:shadow-sm">
                        <i class="fa-solid" :class="isSaving ? 'fa-circle-notch fa-spin' : 'fa-rotate'"></i>
                        <span x-text="isSaving ? 'Saving…' : 'Submit Revised Quotation'"></span>
                    </button>
                @else
                    {{-- Step 3 ("Preview & Submit") is the final, deliberate
                         action: it saves the latest state, then flips the
                         draft to 'submitted' (QuotationService::submitDraft())
                         and notifies the buyer — a real navigation to the
                         quotation show page, not a silent AJAX save, since
                         this is the moment the supplier commits to the offer.
                         Steps 1-2 keep the lightweight, stay-on-page Save
                         Draft button. --}}
                    <template x-if="currentStep === 3">
                        <div class="mt-4 space-y-2">
                            <button type="button" @click="confirmFinalSubmit()" :disabled="isSaving"
                                    class="btn-primary w-full text-sm font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-sm hover:shadow-md transition-all disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:shadow-sm">
                                <i class="fa-solid" :class="isSaving ? 'fa-circle-notch fa-spin' : 'fa-paper-plane'"></i>
                                <span x-text="isSaving ? 'Submitting…' : 'Submit Quotation'"></span>
                            </button>
                            <button type="button" @click="saveDraftManually()" :disabled="isSaving"
                                    class="w-full text-xs font-semibold py-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                                Save as draft instead
                            </button>
                            <p class="text-[11px] text-gray-400 text-center leading-relaxed">Once submitted, the buyer is notified and pricing is locked until they request a revision.</p>
                        </div>
                    </template>
                    <template x-if="currentStep !== 3">
                        <div>
                            {{-- type="button", not submit: stays on this page
                                 (AJAX via the same autosave() the rest of the
                                 form already uses) and shows a toast on
                                 completion, instead of a full-page POST +
                                 redirect. --}}
                            <button type="button" @click="saveDraftManually()" :disabled="isSaving"
                                    class="w-full text-sm font-semibold py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400 hover:shadow-sm transition-all mt-4 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:shadow-none">
                                <i class="fa-solid" :class="isSaving ? 'fa-circle-notch fa-spin' : 'fa-floppy-disk'"></i>
                                <span x-text="isSaving ? 'Saving…' : 'Save Draft'"></span>
                            </button>
                            <p class="text-[11px] text-gray-400 mt-2 text-center">Review and submit from Step 3 when you're ready.</p>
                        </div>
                    </template>
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
            title: config.title,
            description: config.description,
            expectedDeliveryDate: config.expectedDeliveryDate,
            validUntil: config.validUntil,
            warrantyTerms: config.warrantyTerms,
            supportTerms: config.supportTerms,
            paymentTerms: config.paymentTerms,
            proposal: config.proposal,
            deliveryAddresses: config.deliveryAddresses,
            statesUrl: config.statesUrl,
            citiesUrl: config.citiesUrl,

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
                                client_ref: o._localKey,
                                specifications: this.getOfferSpecsPayload(o),
                                attribute_values: o._attribute_values || {},
                            })),
                        };
                    });
                    const res = await fetch(url, {
                        method: this.quotationId ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            items: itemsPayload, addons: this.addons,
                            currency_code: this.currencyCode,
                            current_step: this.currentStep, max_completed_step: this.maxCompletedStep,
                            title: this.title, description: this.description,
                            expected_delivery_date: this.expectedDeliveryDate, valid_until: this.validUntil,
                            warranty_terms: this.warrantyTerms, support_terms: this.supportTerms,
                            payment_terms: this.paymentTerms, proposal: this.proposal,
                            delivery_addresses: this.deliveryAddresses.map(a => ({
                                country_id: a.country_id || null, state_id: a.state_id || null,
                                city_id: a.city_id || null, address: a.address || null,
                            })),
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
                    if (data.offers) {
                        this.items.forEach(item => {
                            (item.offers || []).forEach(offer => {
                                const assignedOfferId = data.offers[offer._localKey];
                                if (assignedOfferId && !offer.id) offer.id = assignedOfferId;
                            });
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

            // "Save Draft" button — same autosave() everything else already
            // goes through (so it stays on this page, no full form POST/
            // redirect), just with an explicit toast on completion since
            // this is a deliberate supplier action, unlike the silent
            // background autosaves triggered by step changes/field edits.
            async saveDraftManually() {
                const saved = await this.autosave();
                if (typeof Swal === 'undefined') return;
                if (saved) {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: 'Draft saved', showConfirmButton: false,
                        timer: 3500, timerProgressBar: true,
                    });
                } else {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'error',
                        title: this.saveError || 'Could not save your progress.', showConfirmButton: false,
                        timer: 4500, timerProgressBar: true,
                    });
                }
            },

            // "Submit Quotation" (Step 3) — asks for explicit confirmation
            // since, unlike Save Draft, this is the point of no easy return:
            // it locks pricing until the buyer requests a revision. Always
            // saves the latest state first (autosave()) so nothing typed on
            // this visit is lost before the status flips.
            async confirmFinalSubmit() {
                if (typeof Swal === 'undefined') {
                    this.submitQuotation();
                    return;
                }
                const result = await Swal.fire({
                    title: 'Submit this quotation?',
                    html: 'The buyer will be notified right away. You won\'t be able to change pricing again unless they request a revision.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4F46E5',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Yes, submit quotation',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: {
                        popup: 'rounded-2xl font-sans text-xs',
                        title: 'text-base font-bold text-gray-900',
                        htmlContainer: 'text-xs text-gray-600',
                        confirmButton: 'text-xs font-semibold px-4 py-2 rounded-lg',
                        cancelButton: 'text-xs font-semibold px-4 py-2 rounded-lg',
                    },
                });
                if (result.isConfirmed) this.submitQuotation();
            },
            // Saves, then does a REAL native form POST to the submit
            // endpoint (flips status draft → submitted, records the
            // 'submitted' activity, notifies the buyer server-side) — never
            // a fetch()+window.location.href navigation, because that
            // requires guessing whether the response was actually a
            // followed redirect; any non-redirect reply (a 403 from an
            // auth edge case, a stale RFQ-version check, etc.) leaves
            // res.url pointing at this POST-only endpoint, and navigating
            // there via GET 405s. A genuine form submit lets the browser
            // handle every case exactly like any other page navigation:
            // follows a 302 to the show page correctly, or renders
            // whatever error page the server actually returned.
            submitQuotation() {
                this.isSaving = true;
                this.autosave().then(saved => {
                    if (!saved) {
                        this.isSaving = false;
                        return;
                    }
                    if (!this.quotationId) {
                        this.isSaving = false;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'error',
                                title: 'Could not submit — please try saving again.', showConfirmButton: false,
                                timer: 4500, timerProgressBar: true,
                            });
                        }
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = config.autosaveUpdateUrlBase + '/' + this.quotationId + '/submit';
                    form.style.display = 'none';
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = config.csrfToken;
                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                });
            },

            // Custom/Copy-Spec offers have no single "confirm" action the way
            // Marketplace (product picked) or Document (file picked) do —
            // the supplier just edits fields directly (category, attribute
            // values, price, quantity, specs), so those need their own
            // autosave trigger instead of relying on a step change or the
            // Save Draft button. Debounced so a burst of keystrokes/clicks
            // collapses into one save shortly after the supplier pauses,
            // rather than firing a request per character. See the x-init
            // $watch on _offer-card.blade.php's root, which is what actually
            // calls this on every relevant field change.
            _customOfferAutosaveTimer: null,
            scheduleCustomOfferAutosave() {
                clearTimeout(this._customOfferAutosaveTimer);
                this._customOfferAutosaveTimer = setTimeout(() => {
                    this.autosave();
                }, 1200);
            },

            init() {
                this.restoreFromMarketplaceSelection();
                this.items.forEach(item => {
                    this.syncItemWithPrimaryOffer(item);
                    const buyerItem = this.rfqItemsById[item.rfq_item_id];
                    if (buyerItem) this.fetchItemAttributes(item, buyerItem.category_id);

                    // Reloading a draft with an existing offer that already
                    // has a category set (Custom/Copy-Spec, or Marketplace
                    // with the listing's own category) — load that category's
                    // attribute FIELD DEFINITIONS so they show up immediately
                    // instead of only appearing after the supplier re-touches
                    // the category picker. The saved VALUES themselves
                    // (offer._attribute_values) are already seeded server-side
                    // above, from this offer's own quotation_item_attribute_values
                    // rows — this fetch is only for the field definitions to
                    // display them in. `offer` here comes from iterating the
                    // reactive item.offers array, so it's already the proxied
                    // reference (see addCustomOffer's comment on why that
                    // distinction matters).
                    (item.offers || []).forEach(offer => {
                        const method = offer.offer_method;
                        if ((method === 'custom' || method === 'copy_spec' || method === 'marketplace') && offer.category_id) {
                            offer._attrCategoryId = offer.category_id;
                            this.fetchOfferAttributes(offer);
                        }
                    });
                });
                this.deliveryAddresses.forEach(addr => this.initDeliveryAddressRow(addr));
                this.loadAutoMatches();
                this.$nextTick(() => this.initDatePickers());
            },

            initDatePickers() {
                if (typeof flatpickr === 'undefined') return;
                const config = { dateFormat: 'Y-m-d' };
                if (this.expectedDeliveryDate) config.defaultDate = this.expectedDeliveryDate;
                if (this.$refs.expectedDeliveryInput) flatpickr(this.$refs.expectedDeliveryInput, config);
            },

            // ── Delivery addresses — same cascading country/state/city
            //    pattern the buyer's own RFQ "Details & Delivery" step uses
            //    (addAddress()/onAddressCountryChange() etc. in the buyer's
            //    _form.blade.php), just scoped to `deliveryAddresses` here
            //    instead of a split primary-field + additionalAddresses[]. ──
            addDeliveryAddress() {
                this.deliveryAddresses.push({ country_id: 0, state_id: 0, city_id: 0, address: '', _states: [], _cities: [] });
            },
            removeDeliveryAddress(idx) {
                if (idx === 0) return; // "Address 1" can be cleared, not removed
                this.deliveryAddresses.splice(idx, 1);
            },
            initDeliveryAddressRow(addr) {
                if (addr.country_id) {
                    fetch(this.statesUrl + '/' + addr.country_id + '/states').then(r => r.json()).then(d => { addr._states = d; });
                }
                if (addr.state_id) {
                    fetch(this.citiesUrl + '/' + addr.state_id + '/cities').then(r => r.json()).then(d => { addr._cities = d; });
                }
            },
            onDeliveryAddressCountryChange(addr) {
                addr.state_id = 0; addr.city_id = 0; addr._cities = [];
                if (!addr.country_id) { addr._states = []; return; }
                fetch(this.statesUrl + '/' + addr.country_id + '/states').then(r => r.json()).then(d => { addr._states = d; });
            },
            onDeliveryAddressStateChange(addr) {
                addr.city_id = 0;
                if (!addr.state_id) { addr._cities = []; return; }
                fetch(this.citiesUrl + '/' + addr.state_id + '/cities').then(r => r.json()).then(d => { addr._cities = d; });
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

            // `offer` is only passed when this is a "Change" click on an
            // EXISTING marketplace offer — carrying its _localKey through as
            // replace_offer_token lets restoreFromMarketplaceSelection() swap
            // that specific offer for the new pick instead of just adding a
            // second one alongside it. "Add Alternative Product" / the
            // initial "From Catalog" choice call this with no offer, which
            // keeps their existing append behavior.
            openMarketplaceSelector(item, offer = null) {
                try {
                    const raw = (window.Alpine && window.Alpine.raw) ? window.Alpine.raw(this.items) : this.items;
                    sessionStorage.setItem('quotationItemsSnapshot', JSON.stringify(raw));
                } catch (e) {}

                const returnUrl = window.location.href.split('#')[0];
                const url = new URL(config.selectProductUrl, window.location.origin);
                url.searchParams.set('rfq_item_id', item.rfq_item_id ?? '');
                url.searchParams.set('item_token', item._localKey);
                if (offer) {
                    url.searchParams.set('replace_offer_token', offer._localKey);
                }
                url.searchParams.set('return_url', returnUrl);
                window.location.href = url.toString();
            },

            async restoreFromMarketplaceSelection() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('restore_items') !== '1') return;

                // Read everything we need out of the URL, THEN strip the
                // restore_* params immediately — before any async work — so
                // a page refresh, slow network, or back/forward navigation
                // mid-restore can never re-run this against the same
                // selection and append duplicate offers (the address bar is
                // clean from this point on, regardless of how long the
                // fetches below take).
                const selectedListingIdsParam = params.get('selected_listing_ids') || params.get('selected_listing_id');
                const itemToken = params.get('item_token');
                const rfqItemId = params.get('rfq_item_id');
                const replaceOfferToken = params.get('replace_offer_token');

                params.delete('restore_items');
                params.delete('selected_listing_ids');
                params.delete('selected_listing_id');
                params.delete('item_token');
                params.delete('rfq_item_id');
                params.delete('replace_offer_token');
                const cleanQuery = params.toString();
                window.history.replaceState({}, '', window.location.pathname + (cleanQuery ? '?' + cleanQuery : ''));

                try {
                    const saved = sessionStorage.getItem('quotationItemsSnapshot');
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (Array.isArray(parsed) && parsed.length > 0) this.items = parsed;
                    }
                } catch (e) {}
                sessionStorage.removeItem('quotationItemsSnapshot');

                if (selectedListingIdsParam) {
                    // De-duplicated: a stray repeated id in the query string
                    // (or a re-selected product) must never queue two fetches
                    // for the same listing.
                    const listingIds = [...new Set(
                        selectedListingIdsParam.split(',')
                            .map(s => parseInt(s.trim(), 10))
                            .filter(n => !isNaN(n))
                    )];

                    let item = itemToken ? this.items.find(i => i._localKey === itemToken) : null;
                    if (!item && rfqItemId) {
                        item = this.items.find(i => String(i.rfq_item_id) === String(rfqItemId));
                    }

                    if (item && listingIds.length > 0) {
                        item._collapsed = false;
                        if (!Array.isArray(item.offers)) item.offers = [];

                        // "Change" on an existing offer: remove the offer being
                        // replaced FIRST, so the new pick takes its place (and
                        // its is_primary status) instead of sitting alongside
                        // it as a confusing second offer.
                        let replaceAtIndex = -1;
                        let replacedWasPrimary = false;
                        if (replaceOfferToken) {
                            replaceAtIndex = item.offers.findIndex(o => o._localKey === replaceOfferToken);
                            if (replaceAtIndex !== -1) {
                                replacedWasPrimary = !!item.offers[replaceAtIndex].is_primary;
                                item.offers.splice(replaceAtIndex, 1);
                            }
                        }

                        // Checking is_primary directly (not a price/marketplace-id
                        // proxy) is what makes this reliable: a Custom/Copy-Spec/
                        // Document offer that hasn't been priced yet still counts
                        // as "already has a primary", so a newly added catalog
                        // pick correctly comes in as an alternative instead of a
                        // second primary.
                        const hasExistingPrimary = item.offers.some(o => o.is_primary);
                        let addedAny = false;

                        for (let idx = 0; idx < listingIds.length; idx++) {
                            const lid = listingIds[idx];

                            // Idempotency guard: never add a second offer for a
                            // marketplace product already present on this item —
                            // this is what actually prevents duplicate rows,
                            // regardless of how this loop got invoked (fresh
                            // selection, a re-run from a stale URL, etc).
                            if (item.offers.some(o => o.marketplace_product_id === lid)) {
                                continue;
                            }

                            try {
                                const res = await fetch(config.listingsPrefillUrl + '/' + lid + '/prefill');
                                if (!res.ok) continue;
                                const data = await res.json();

                                // Re-check post-fetch: another iteration of this
                                // same loop (or a concurrent call) may have added
                                // this exact product while we were awaiting.
                                if (item.offers.some(o => o.marketplace_product_id === data.item.offered_listing_id)) {
                                    continue;
                                }

                                const isFirstInBatch = idx === 0;
                                const isReplacement = replaceAtIndex !== -1 && isFirstInBatch;
                                const isPrimary = isReplacement ? replacedWasPrimary : (!hasExistingPrimary && isFirstInBatch);
                                const sortOrder = item.offers.length;

                                // Structured attribute values (_attribute_values) are
                                // the listing's own category attributes — those persist
                                // relationally via quotation_item_attribute_values (see
                                // syncOfferAttributeValues in QuotationService), not as
                                // free text here. _custom_specs/specifications start
                                // empty and only ever hold what the supplier explicitly
                                // types in "Additional Specifications" — matching
                                // selectListingForItem()'s pattern below, which never had
                                // this duplication.
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
                                    shipping_charge: null,
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
                                    // listingPrefill() wraps the groups array
                                    // as { category_id, category_name, groups,
                                    // total_count } (Category::attributesGroupedForForm()'s
                                    // shape) — the .groups list itself is what
                                    // _offer-attributes.blade.php's x-for
                                    // expects, not the wrapper object.
                                    _attrGroups: data.category_attributes?.groups || [],
                                    _attrLoading: false,
                                    _attribute_values: data.attribute_values || {},
                                    _custom_specs: [],
                                    _documents: [],
                                    _uploadPreparing: false,
                                };

                                if (isReplacement) {
                                    item.offers.splice(replaceAtIndex, 0, offer);
                                } else {
                                    item.offers.push(offer);
                                }
                                addedAny = true;
                            } catch (err) {
                                console.error('Error prefilling listing', lid, err);
                            }
                        }

                        this.syncItemWithPrimaryOffer(item);

                        // Persist right away — this whole method only runs after
                        // coming back from the marketplace product selector, so
                        // without this the newly added offer sits in local Alpine
                        // state only until some unrelated action (step change,
                        // manual save) triggers an autosave.
                        if (addedAny) {
                            await this.autosave();

                            // On a multi-item RFQ the item that just got a new
                            // offer can be well below the fold after this full
                            // page reload — scroll it into view and give it a
                            // brief highlight so it's unmistakable which item
                            // changed, without collapsing or otherwise touching
                            // any other item on the page.
                            this.$nextTick(() => {
                                const el = document.getElementById('product-response-' + item._localKey);
                                if (!el) return;
                                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                item._justUpdated = true;
                                setTimeout(() => { item._justUpdated = false; }, 2000);
                            });
                        }
                    }
                }
            },

            // Starts with its OWN category — independent of the buyer's, per
            // the "Custom Offer" two-sided design: the buyer's category/
            // attributes/specs are shown read-only for reference (in
            // _offer-card.blade.php's left column) but never auto-applied
            // here. "Copy buyer specifications" (toggleCopyBuyerSpecs below)
            // is the one explicit, opt-in way to pull them across.
            // Custom Offer is the ONLY place buyer specs ever get copied from
            // now — "Copy buyer specifications to my offer" defaults to
            // CHECKED here, so a fresh Custom offer starts pre-filled with
            // the buyer's category/attributes/specs (via copyBuyerSpecsIntoOffer
            // below); the supplier can uncheck it or just start picking their
            // own category, which auto-unchecks it (selectCategoryNodeForOffer).
            addCustomOffer(item, makePrimary = true) {
                if (!Array.isArray(item.offers)) item.offers = [];
                const isPrimary = makePrimary && (item.offers.length === 0 || !item.offers.some(o => o.is_primary));
                const buyerItem = this.rfqItemsById[item.rfq_item_id];

                const offer = {
                    id: null,
                    offer_method: 'custom',
                    marketplace_product_id: null,
                    offered_variant_id: null,
                    // Buyer's own RFQ item name/description first, NOT
                    // item.item_name/item.description — those get overwritten
                    // to mirror whichever offer is currently primary (see
                    // syncItemWithPrimaryOffer), so once a first offer exists
                    // they'd leak that offer's own name/notes onto every new
                    // alternative instead of starting fresh from what the
                    // buyer actually asked for. item.item_name/description
                    // only remain as the fallback for a freestanding item
                    // with no rfq_item_id (no buyer item to reference at all).
                    product_name: buyerItem?.item_name ?? item.item_name ?? '',
                    category_id: null,
                    description: buyerItem?.description ?? item.description ?? '',
                    specifications: [],
                    quantity: item.quantity || (buyerItem?.quantity ?? '1'),
                    unit_id: item.unit_id || (buyerItem?.unit_id ?? null),
                    custom_unit: null,
                    unit_price: '',
                    tax_rate: null,
                    discount: null,
                    shipping_charge: null,
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
                    _attrCategoryId: null,
                    _categorySearch: '',
                    _categoryPickerOpen: false,
                    // Checked by default only when there's actually buyer
                    // data behind it (e.g. not for a free-standing "Add
                    // Another Product Response" row with no rfq_item_id).
                    _copyBuyerSpecs: !!buyerItem,
                    _custom_specs: [],
                    _documents: [],
                    _uploadPreparing: false,
                    // Flips to true only once the supplier genuinely changes
                    // something after this card mounts (see the $watch in
                    // _offer-card.blade.php) — starts false even though
                    // copyBuyerSpecsIntoOffer below may immediately fill in
                    // category/attributes/specs, since that's the default
                    // auto-copy, not something the supplier did themselves.
                    _hasUserEdited: false,
                };

                item.offers.push(offer);
                item._collapsed = false;

                // Must re-read the offer back OUT of item.offers (not use the
                // raw `offer` variable above) — see copyBuyerSpecsIntoOffer's
                // reactivity note below.
                const reactiveOffer = item.offers[item.offers.length - 1];
                if (buyerItem) {
                    this.copyBuyerSpecsIntoOffer(reactiveOffer, buyerItem);
                }

                this.syncItemWithPrimaryOffer(item);
            },

            // The one place buyer data (rfq_items' category/
            // attribute_values_raw/specs, read from rfqItemsById) is copied
            // across into a supplier offer's OWN fields — called by
            // addCustomOffer above (default-on) and toggleCopyBuyerSpecs
            // below (manual re-check). After this runs the values are plain
            // offer state, freely editable and completely independent of the
            // buyer's — nothing here writes back to or re-reads rfq_items.
            copyBuyerSpecsIntoOffer(offer, buyerItem) {
                if (!buyerItem) return;

                offer.category_id = buyerItem.category_id ?? null;
                offer._category_name = buyerItem.category_name ?? null;
                offer._attribute_values = JSON.parse(JSON.stringify(buyerItem.attribute_values_raw || {}));
                // Prevents onOfferCategoryChange (called by fetchOfferAttributes
                // below via the category-picker path) from treating this as a
                // fresh category change and wiping the values just copied in.
                offer._attrCategoryId = offer.category_id;

                const customSpecs = [];
                if (Array.isArray(buyerItem.specs)) {
                    buyerItem.specs.forEach(s => {
                        if (s.name && !s.name.startsWith('__')) {
                            customSpecs.push({ name: s.name, value: s.value || '' });
                        }
                    });
                }
                offer._custom_specs = customSpecs;

                // Loads the ATTRIBUTE DEFINITIONS (names/input types/option
                // lists) for the copied category, so the just-copied raw
                // values render in their proper structured fields instead of
                // sitting inert with no groups to display them in.
                this.fetchOfferAttributes(offer);
            },

            // Checkbox change handler — offer here is already the reactive
            // proxy (bound via x-model in the template), so copyBuyerSpecsIntoOffer's
            // mutations correctly trigger a re-render.
            toggleCopyBuyerSpecs(offer, item) {
                if (!offer._copyBuyerSpecs) return; // unchecking leaves whatever was copied in place, still editable
                const buyerItem = this.rfqItemsById[item.rfq_item_id];
                this.copyBuyerSpecsIntoOffer(offer, buyerItem);
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
                    shipping_charge: null,
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
                    _uploadPreparing: false,
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

            // Removing an offer needs a real confirmation (it's destructive —
            // once saved, an already-persisted offer and its attached files
            // are gone for good), and it needs to actually persist right
            // away rather than waiting for the next autosave/step-change —
            // otherwise a removed offer just reappears from the database on
            // the next save. So: confirm via SweetAlert, then splice locally
            // and immediately autosave when the offer already exists server-side
            // (target.id only gets set once this.quotationId does, so this
            // also naturally skips autosave for a purely local, never-saved offer).
            removeOffer(item, offerIndex) {
                if (!item.offers) return;
                const target = item.offers[offerIndex];
                if (!target) return;

                const hasDocs = target.offer_method === 'document' && target._documents && target._documents.length > 0;
                const text = hasDocs
                    ? ('This offer has ' + target._documents.length + ' uploaded document(s) attached. Removing it will permanently delete those files as well.')
                    : 'This offer will be permanently removed. This action cannot be undone.';

                const doRemove = async () => {
                    const removedWasPrimary = item.offers[offerIndex]?.is_primary;
                    item.offers.splice(offerIndex, 1);
                    if (removedWasPrimary && item.offers.length > 0) {
                        item.offers[0].is_primary = true;
                    }
                    item.offers.forEach((o, idx) => { o.sort_order = idx; });
                    this.syncItemWithPrimaryOffer(item);

                    if (target.id) {
                        await this.autosave();
                    }
                };

                if (typeof confirmSwal === 'function') {
                    confirmSwal(doRemove, 'Remove this offer?', text, 'warning', 'Yes, remove it');
                } else if (window.confirm(text)) {
                    doRemove();
                }
            },

            addCustomSpec(offer) {
                if (!Array.isArray(offer._custom_specs)) offer._custom_specs = [];
                offer._custom_specs.push({ name: '', value: '' });
            },

            removeCustomSpec(offer, specIndex) {
                if (!Array.isArray(offer._custom_specs)) return;
                offer._custom_specs.splice(specIndex, 1);
            },

            // quotation_item_offers.specifications is ONLY the supplier's
            // free-form "Additional Specifications" rows (_custom_specs) now
            // — every offer's structured category attribute values (marketplace,
            // custom, or copy_spec) persist relationally instead, via
            // QuotationItemOffer::attributeValues() / syncOfferAttributeValues()
            // in QuotationService, keyed by attribute_id so they stay
            // comparable against the buyer's own rfq_item_attribute_values.
            // Flattening them into this JSON too would just duplicate the
            // same data in two places with no machine-readable link between
            // them (same reasoning RfqService.buildItemSpecs() already
            // follows on the buyer side — specs stays free-text-only there
            // too, attribute_values is submitted separately).
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

            // ── Custom Offer category picker + dynamic attributes — same
            //    categoryOptions tree (id/name/path/depth/attributes_count)
            //    and /categories/{id}/attributes endpoint the buyer's "Add
            //    Custom Product" flow uses, just scoped to `offer` instead
            //    of the buyer's `item`. ──
            filteredCategoryNodesForOffer(offer) {
                const q = (offer._categorySearch || '').trim().toLowerCase();
                if (!q) return this.categoryOptions;
                return this.categoryOptions.filter(n =>
                    n.name.toLowerCase().includes(q) || (n.path && n.path.toLowerCase().includes(q))
                );
            },
            getCategoryNodePath(catId) {
                if (!catId) return '';
                const node = this.categoryOptions.find(n => n.id == catId);
                return node ? node.path : '';
            },
            reopenCategoryPickerForOffer(offer, event) {
                offer._categorySearch = '';
                offer._categoryPickerOpen = true;
                this.$nextTick(() => {
                    if (!offer.category_id) return;
                    const wrapper = event.currentTarget.closest('.relative');
                    const row = wrapper && wrapper.querySelector('[data-node-id="' + offer.category_id + '"]');
                    if (row) row.scrollIntoView({ block: 'center' });
                });
            },
            // Manually picking a category means the supplier is deliberately
            // offering something outside (or more specific than) the buyer's
            // own category — the copied buyer attribute values no longer
            // apply to it, so this both unchecks "Copy buyer specifications"
            // and (via onOfferCategoryChange) clears them before loading the
            // new category's own attribute fields.
            selectCategoryNodeForOffer(offer, node) {
                offer.category_id = node.id;
                offer._category_name = node.name;
                offer._categorySearch = '';
                offer._categoryPickerOpen = false;
                offer._copyBuyerSpecs = false;
                this.onOfferCategoryChange(offer);
            },
            clearCategoryForOffer(offer) {
                offer.category_id = null;
                offer._category_name = null;
                offer._categorySearch = '';
                offer._categoryPickerOpen = false;
                offer._attrCategoryId = null;
                offer._attribute_values = {};
                offer._attrGroups = [];
                offer._copyBuyerSpecs = false;
            },
            onOfferCategoryChange(offer) {
                // Only clear already-answered values when the category is
                // ACTUALLY changing — this also runs right after creating a
                // new Custom offer (pre-filled from the buyer's category via
                // copyBuyerSpecsIntoOffer), where there's nothing to wipe yet.
                if (offer._attrCategoryId === offer.category_id) {
                    this.fetchOfferAttributes(offer);
                    return;
                }
                offer._attrCategoryId = offer.category_id;
                offer._attribute_values = {};
                this.fetchOfferAttributes(offer);
            },
            fetchOfferAttributes(offer) {
                if (!offer.category_id) { offer._attrGroups = []; return; }
                offer._attrLoading = true;
                let url = config.categoryAttributesUrl + '/' + offer.category_id + '/attributes';
                const keepIds = Object.keys(offer._attribute_values || {});
                if (keepIds.length > 0) {
                    url += '?keep_attribute_ids=' + keepIds.join(',');
                }
                fetch(url)
                    .then(r => r.json())
                    .then(data => { offer._attrGroups = data.groups || []; })
                    .finally(() => { offer._attrLoading = false; });
            },
            getOfferAttrVal(offer, attrId) {
                if (!offer._attribute_values) offer._attribute_values = {};
                if (!offer._attribute_values[attrId]) {
                    offer._attribute_values[attrId] = {
                        attribute_value_id: null, custom_value: null, value_text: null,
                        value_number: null, value_boolean: null, value_date: null, value_json: [],
                    };
                }
                return offer._attribute_values[attrId];
            },
            isOfferOtherSelected(offer, attrId) {
                return this.getOfferAttrVal(offer, attrId).attribute_value_id === '__other__';
            },
            isOfferMultiSelected(offer, attrId, value) {
                const v = this.getOfferAttrVal(offer, attrId).value_json;
                return Array.isArray(v) && v.includes(value);
            },
            toggleOfferMultiSelect(offer, attrId, value) {
                const val = this.getOfferAttrVal(offer, attrId);
                if (!Array.isArray(val.value_json)) val.value_json = [];
                const idx = val.value_json.indexOf(value);
                if (idx === -1) val.value_json.push(value); else val.value_json.splice(idx, 1);
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
                    item.shipping_charge = primary.shipping_charge;
                    item.lead_time_days = primary.delivery_time;
                    item.offered_listing_id = primary.marketplace_product_id;
                    item.offered_variant_id = primary.offered_variant_id;
                    item._responseMethod = primary.offer_method;
                    item.description = primary.description;
                    if (primary._attribute_values) {
                        item.attribute_values = primary._attribute_values;
                    }
                    if (primary.category_id) {
                        item.category_id = primary.category_id;
                    }
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

            // Document Quotation offer files — quotation_item_offers' own
            // 'document' media collection (see QuotationItemOfferDocumentController),
            // distinct per offer just like combined_document is per-quotation.
            // A brand-new offer has no id until autosaved at least once, so
            // this autosaves first when needed rather than leaving the
            // supplier stuck with no way to upload.
            async handleOfferDocumentSelect(item, offer, fileList) {
                const files = Array.from(fileList || []);
                for (const file of files) {
                    await this.uploadOfferDocument(item, offer, file);
                }
            },
            async uploadOfferDocument(item, offer, file) {
                // Set BEFORE the id-bootstrap autosave below (not after) — the
                // backend's isValidOffer() treats _uploadPreparing as the
                // signal that this specific offer is actively being uploaded
                // to, which is what lets that bootstrap autosave persist a
                // brand-new offer despite it having no price/docs yet.
                offer._uploadPreparing = true;
                try {
                    if (!this.quotationId || !item.id || !offer.id) {
                        const saved = await this.autosave();
                        if (!saved || !this.quotationId || !item.id || !offer.id) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: 'Could not upload', text: 'Your progress could not be saved — check your connection and try again.' });
                            }
                            return;
                        }
                    }
                    const body = new FormData();
                    body.append('document', file);
                    const url = config.itemDocumentUrlBase + '/' + this.quotationId + '/items/' + item.id + '/offers/' + offer.id + '/document';
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body,
                    });
                    if (!res.ok) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Upload failed', text: 'Check the file type (PDF, DOC, DOCX, XLS, XLSX, CSV) and size (max 10MB), then try again.' });
                        }
                        return;
                    }
                    const media = await res.json();
                    offer._documents = [...(offer._documents || []), media];
                } finally {
                    offer._uploadPreparing = false;
                }
            },
            async deleteOfferDocument(item, offer, mediaId) {
                if (!this.quotationId || !item.id || !offer.id) return;
                const url = config.itemDocumentUrlBase + '/' + this.quotationId + '/items/' + item.id + '/offers/' + offer.id + '/document/' + mediaId;
                await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                });
                offer._documents = (offer._documents || []).filter(d => d.id !== mediaId);
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
                            shipping_charge: null,
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
                            _attrGroups: data.category_attributes?.groups || [],
                            _attrLoading: false,
                            _attribute_values: data.attribute_values || {},
                            _custom_specs: [],
                            _documents: [],
                            _uploadPreparing: false,
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
            // Shipping is per-offer now (quotation_item_offers.shipping_charge,
            // entered in Step 1), not one flat quotation-level value — this
            // sums each item's own shipping_charge (kept in sync with its
            // primary offer by syncItemWithPrimaryOffer()), same pattern as
            // totalDiscount() above. Add-ons have no offers/shipping of
            // their own, so they contribute 0 here via the same || 0 fallback.
            totalShipping() {
                return this.allRows().reduce((s, r) => {
                    return s + parseFloat(r.shipping_charge || 0);
                }, 0);
            },
            grandTotal() { return this.subtotal() - this.totalDiscount() + this.totalTax() + this.totalShipping(); },
        }));
    });
</script>
@endpush
