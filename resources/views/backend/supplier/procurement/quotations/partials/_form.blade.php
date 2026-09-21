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

    if ($isEdit || $isRevision) {
        $existingRfqItemIds = $quotation->items->pluck('rfq_item_id')->filter()->all();
        $initialItems = $quotation->items->where('is_optional_addon', false)->values()->map(fn ($item, $idx) => [
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
            '_offerType' => $item->is_alternative ? 'alternative' : ($item->offered_listing_id ? 'existing' : 'custom'),
            '_collapsed' => $idx > 0 && !$errors->has("items.$idx.*"), '_advancedOpen' => false,
            '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [], '_variants' => [], '_suggestedListing' => null,
        ])->values();

        $newRfqItems = $rfq->items->whereNotIn('id', $existingRfqItemIds)->map(fn ($item) => [
            'id' => null, 'rfq_item_id' => $item->id,
            'offered_listing_id' => null, 'offered_variant_id' => null, 'is_alternative' => false,
            'item_name' => $item->item_name, 'description' => null, 'quantity' => (string) $item->quantity,
            'unit_id' => $item->unit_id, 'custom_unit' => $item->custom_unit,
            'unit_price' => null, 'tax_rate' => null, 'discount_amount' => null, 'lead_time_days' => null,
            'attribute_values' => (object) [],
            '_offerType' => 'custom',
            '_collapsed' => true, '_advancedOpen' => false,
            '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [], '_variants' => [], '_suggestedListing' => null,
        ]);
        $initialItems = $initialItems->concat($newRfqItems)->values();

        $initialAddons = $quotation->items->where('is_optional_addon', true)->values()->map(fn ($item) => [
            'id' => $item->id, 'item_name' => $item->item_name, 'description' => $item->description,
            'quantity' => (string) $item->quantity, 'unit_id' => $item->unit_id,
            'unit_price' => $item->unit_price, 'tax_rate' => $item->tax_rate, 'discount_amount' => $item->discount_amount,
            'lead_time_days' => $item->lead_time_days,
        ])->values();
    } else {
        // "Start from a previous quotation" (?clone_from=) overlays matched
        // pricing/terms onto the item seed below — name/quantity/category
        // always stay tied to THIS rfq's own item, never copied from the source.
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
                '_offerType' => 'custom',
                '_collapsed' => $idx > 0 && !$errors->has("items.$idx.*"), '_advancedOpen' => false,
                '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [], '_variants' => [], '_suggestedListing' => null,
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
        listingsSearchUrl: '{{ route('supplier.quotations.listings.search') }}',
        listingsPrefillUrl: '{{ url('/supplier/quotations/listings') }}',
        autoMatchUrl: '{{ route('supplier.quotations.listings.auto-match', $rfq) }}',
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
                <x-backend.form-card title="Requested Items" description="Respond to each RFQ item — use one of your listings, offer an alternative, or create a fully custom offer. Only Unit Price is required; everything else can wait.">
                    <div x-show="items.length > 1" x-cloak class="flex items-center gap-3 mb-4 -mt-1">
                        <button type="button" @click="collapseAllItems()" class="text-xs font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-compress"></i> Collapse All
                        </button>
                        <button type="button" @click="expandAllItems()" class="text-xs font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-expand"></i> Expand All
                        </button>
                    </div>

                    <template x-for="(item, index) in items" :key="index">
                        @include('backend.supplier.procurement.quotations.partials._item')
                    </template>

                    <button type="button" @click="addItem()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Add Another Item
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
            currencyCode: config.currencyCode,
            shippingCharge: config.shippingCharge,

            quotationId: config.quotationId,
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
                    return this.items.length > 0 && this.items.every(i => parseFloat(i.unit_price) >= 0 && i.unit_price !== null && i.unit_price !== '');
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
                        Swal.fire({ icon: 'warning', title: 'Not quite ready', text: 'Every item needs a Unit Price before moving on.' });
                    }
                    this.items.forEach(item => {
                        if (item.unit_price === null || item.unit_price === '') item._collapsed = false;
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
            // Every autosave/submit always serializes the *complete* items +
            // addons arrays (never a "just this step" subset) — syncItems()
            // on the backend deletes any QuotationItem row not present in the
            // payload, so a partial send would silently drop other items.
            async autosave() {
                if (this.isRevision) return true;
                this.isSaving = true;
                this.saveError = null;
                try {
                    const url = this.quotationId ? (config.autosaveUpdateUrlBase + '/' + this.quotationId + '/autosave') : config.autosaveCreateUrl;
                    const res = await fetch(url, {
                        method: this.quotationId ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({
                            items: this.items, addons: this.addons,
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
                    return true;
                } catch (e) {
                    this.saveError = 'Could not save your progress — check your connection and try again.';
                    return false;
                } finally {
                    this.isSaving = false;
                }
            },

            init() {
                this.items.forEach(item => {
                    const buyerItem = this.rfqItemsById[item.rfq_item_id];
                    if (buyerItem) this.fetchItemAttributes(item, buyerItem.category_id);
                });
                this.loadAutoMatches();
            },

            // One bulk lookup suggesting the supplier's own best-matching
            // listing per RFQ item. Only offered as a suggestion the
            // supplier accepts via "Use it" — never applied automatically —
            // and only onto items still untouched (no price/listing yet),
            // so a late-arriving response can't clobber in-progress edits.
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
                this.selectListingForItem(item, { id: item._suggestedListing.listing_id, name: item._suggestedListing.name });
                item._suggestedListing = null;
            },
            dismissSuggestedListing(item) { item._suggestedListing = null; },

            // "Copy buyer's specifications" checkbox above one item's
            // Specifications Comparison — fills that item's name,
            // description, quantity, unit AND every buyer-requested attribute
            // value straight into the matching "Your Offer" fields, so the
            // supplier starts from the buyer's ask instead of retyping it.
            // Attribute values copy 1:1 (attribute_value_id/value_text/etc.)
            // rather than parsing the formatted display text, since
            // rfq_item_attribute_values and listing_attribute_values mirror
            // quotation_item_attribute_values' columns exactly — same
            // attribute definitions, same option ids, on both sides.
            applyCopyBuyerRequirements(item) {
                const buyerItem = this.rfqItemsById[item.rfq_item_id];
                if (!buyerItem) return;

                if (buyerItem.item_name) item.item_name = buyerItem.item_name;
                if (buyerItem.description) item.description = buyerItem.description;
                if (buyerItem.quantity) item.quantity = buyerItem.quantity;
                if (buyerItem.unit_id) item.unit_id = buyerItem.unit_id;

                Object.entries(buyerItem.attribute_values_raw || {}).forEach(([attrId, raw]) => {
                    const target = this.getAttrVal(item, attrId);
                    target.attribute_value_id = raw.attribute_value_id;
                    target.custom_value = raw.custom_value;
                    target.value_text = raw.value_text;
                    target.value_number = raw.value_number;
                    target.value_boolean = raw.value_boolean;
                    target.value_date = raw.value_date;
                    target.value_json = raw.value_json;
                });
            },

            copyBuyerRequirementsToAll() {
                this.items.forEach(item => this.applyCopyBuyerRequirements(item));
            },

            addItem() {
                // Accordion behaviour: collapse whatever's already there so the
                // newly added item is the one thing left open to work on.
                this.collapseAllItems();
                this.items.push({
                    id: null, rfq_item_id: null, offered_listing_id: null, offered_variant_id: null, is_alternative: false,
                    item_name: '', description: '', quantity: '1', unit_id: null, custom_unit: null,
                    unit_price: null, tax_rate: null, discount_amount: null, lead_time_days: null,
                    attribute_values: {}, _offerType: 'custom', _collapsed: false, _advancedOpen: false,
                    _attrLoading: false, _attrGroups: [], _listingQuery: '', _listingResults: [], _variants: [], _suggestedListing: null,
                });
            },
            removeItem(index) {
                this.items.splice(index, 1);
                if (this.items.length === 1) this.items[0]._collapsed = false;
            },

            collapseAllItems() { this.items.forEach(i => { i._collapsed = true; }); },
            expandAllItems() { this.items.forEach(i => { i._collapsed = false; }); },

            addAddon() {
                this.addons.push({ id: null, item_name: '', description: '', quantity: '1', unit_id: null, unit_price: null, tax_rate: null, discount_amount: null, lead_time_days: null });
            },
            removeAddon(index) { this.addons.splice(index, 1); },

            // _offerType is the explicit source of truth for which pill is
            // selected — it used to be derived purely from is_alternative/
            // offered_listing_id, which meant clicking "Use Existing Listing"
            // was a no-op until a listing was actually picked (nothing set
            // offered_listing_id yet), so the pill could never highlight and
            // the search box never appeared. Storing intent directly fixes
            // that; offered_listing_id is still what actually gets submitted.
            getOfferType(item) {
                return item._offerType || (item.is_alternative ? 'alternative' : (item.offered_listing_id ? 'existing' : 'custom'));
            },
            setOfferType(item, type) {
                item._offerType = type;
                item.is_alternative = (type === 'alternative');
                if (type === 'custom') {
                    item.offered_listing_id = null;
                    item.offered_variant_id = null;
                    item._variants = [];
                }
            },

            searchListingsForItem(item) {
                if (item._listingQuery.trim().length < 2) { item._listingResults = []; return; }
                const buyerItem = this.rfqItemsById[item.rfq_item_id];
                const categoryParam = buyerItem && buyerItem.category_id ? '&category_id=' + buyerItem.category_id : '';
                fetch(config.listingsSearchUrl + '?q=' + encodeURIComponent(item._listingQuery) + categoryParam)
                    .then(r => r.json())
                    .then(data => { item._listingResults = data; });
            },
            selectListingForItem(item, listing) {
                item._listingQuery = '';
                item._listingResults = [];
                fetch(config.listingsPrefillUrl + '/' + listing.id + '/prefill')
                    .then(r => r.json())
                    .then(data => {
                        item.offered_listing_id = data.item.offered_listing_id;
                        item.item_name = data.item.item_name;
                        item.description = data.item.description;
                        item.unit_id = data.item.unit_id;
                        item.unit_price = data.item.unit_price;
                        item.attribute_values = data.attribute_values || {};
                        item._variants = data.variants || [];
                    });
            },
            clearListingForItem(item) {
                item.offered_listing_id = null;
                item.offered_variant_id = null;
                item._variants = [];
            },

            fetchItemAttributes(item, categoryId) {
                if (!categoryId) { item._attrGroups = []; return; }
                item._attrLoading = true;
                // Editing/revising an existing quotation that already has a
                // value for an attribute since deactivated — keep it visible
                // instead of silently dropping it.
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
            isOtherSelected(item, attrId) {
                return this.getAttrVal(item, attrId).attribute_value_id === '__other__';
            },
            isMultiSelected(item, attrId, value) {
                const v = this.getAttrVal(item, attrId).value_json;
                return Array.isArray(v) && v.includes(value);
            },
            toggleMultiSelect(item, attrId, value) {
                const val = this.getAttrVal(item, attrId);
                if (!Array.isArray(val.value_json)) val.value_json = [];
                const idx = val.value_json.indexOf(value);
                if (idx === -1) val.value_json.push(value); else val.value_json.splice(idx, 1);
            },

            lineTotal(row) {
                const qty = parseFloat(row.quantity || 0);
                const price = parseFloat(row.unit_price || 0);
                const lineSubtotal = qty * price;
                const discount = parseFloat(row.discount_amount || 0);
                const taxRate = (row.tax_rate !== null && row.tax_rate !== '' && row.tax_rate !== undefined) ? parseFloat(row.tax_rate) : null;
                const tax = taxRate !== null ? (lineSubtotal - discount) * taxRate / 100 : 0;
                return lineSubtotal - discount + tax;
            },
            lineTax(row) {
                const qty = parseFloat(row.quantity || 0);
                const price = parseFloat(row.unit_price || 0);
                const lineSubtotal = qty * price;
                const discount = parseFloat(row.discount_amount || 0);
                const taxRate = (row.tax_rate !== null && row.tax_rate !== '' && row.tax_rate !== undefined) ? parseFloat(row.tax_rate) : null;
                return taxRate !== null ? (lineSubtotal - discount) * taxRate / 100 : 0;
            },
            formatMoney(n) {
                return (this.currencyCode || 'USD') + ' ' + (isNaN(n) ? '0.00' : n.toFixed(2));
            },
            allRows() { return [...this.items, ...this.addons]; },
            subtotal() { return this.allRows().reduce((s, r) => s + parseFloat(r.quantity || 0) * parseFloat(r.unit_price || 0), 0); },
            totalTax() { return this.allRows().reduce((s, r) => s + this.lineTax(r), 0); },
            totalDiscount() { return this.allRows().reduce((s, r) => s + parseFloat(r.discount_amount || 0), 0); },
            grandTotal() { return this.subtotal() - this.totalDiscount() + this.totalTax() + parseFloat(this.shippingCharge || 0); },
        }));
    });
</script>
@endpush
