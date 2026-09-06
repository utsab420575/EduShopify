@php
    $isEdit = $rfq->exists;
    $action = $isEdit ? route('buyer.rfqs.update', $rfq) : route('buyer.rfqs.store');
    $itemAttributeValues = $itemAttributeValues ?? [];
    $targetFilter = $targetFilter ?? null;
    $isEditingPublished = $isEdit && $rfq->status !== 'draft';

    $initialItems = $items->isNotEmpty() ? $items->values()->map(fn ($i, $idx) => [
        'id' => $i->id, 'item_type' => $i->item_type, 'listing_id' => $i->listing_id ?? null, 'category_id' => $i->category_id,
        'category_name' => $i->category_name ?? $i->category?->name ?? null,
        'item_name' => $i->item_name, 'description' => $i->description, 'quantity' => (string) $i->quantity,
        'unit_id' => $i->unit_id, 'custom_unit' => $i->custom_unit, 'estimated_unit_price' => $i->estimated_unit_price,
        'attribute_values' => (object) ($itemAttributeValues[$idx] ?? []),
        'custom_attributes' => is_array($i->specs ?? null) ? $i->specs : [],
        '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [],
        '_mode' => ($idx === 0 && !empty($i->listing_id)) ? 'initial_marketplace' : (!empty($i->listing_id) ? 'marketplace' : 'custom'),
        '_specsOpen' => !($idx === 0 && !empty($i->listing_id)) || !empty($i->specs),
        'listing_image_url' => $i->listing_image_url ?? ($i->listing ? ($i->listing->primaryImage?->getUrl() ?? $i->listing->getFirstMediaUrl('gallery') ?: null) : null),
    ])->values() : collect([[
        'id' => null, 'item_type' => 'product', 'listing_id' => null, 'category_id' => null, 'category_name' => null,
        'item_name' => '', 'description' => '', 'quantity' => '1', 'unit_id' => null, 'custom_unit' => null,
        'estimated_unit_price' => null, 'attribute_values' => (object) ($itemAttributeValues[0] ?? []),
        'custom_attributes' => [], '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [],
        '_mode' => 'custom', '_specsOpen' => true, 'listing_image_url' => null,
    ]]);

    $initialSuppliers = $invitedSuppliers->map(fn ($a) => ['id' => $a->id, 'name' => $a->supplierProfile?->display_name ?? $a->display_name])->values();
    $defaultVtId = $rfq->visibility_type_id ?? (isset($visibilityTypes) && $visibilityTypes->first() ? $visibilityTypes->first()->id : 0);

    $buyerVisibilityCodes = ['direct', 'invited', 'open_matching'];
    $buyerVisibilityLabels = [
        'direct' => ['label' => 'Specific Supplier', 'desc' => 'Send RFQ to one supplier only.'],
        'invited' => ['label' => 'Selected Suppliers', 'desc' => 'Invite multiple suppliers manually.'],
        'open_matching' => ['label' => 'All Eligible Suppliers', 'desc' => 'Automatically match suppliers based on category and location.'],
    ];

    $initialTargetFilter = [
        'category_id' => old('target_filter.category_id', $targetFilter?->category_id),
        'location_match_level' => old('target_filter.location_match_level', $targetFilter?->location_match_level ?? 'none'),
        'country_id' => old('target_filter.country_id', $targetFilter?->country_id ?? 0),
        'state_id' => old('target_filter.state_id', $targetFilter?->state_id ?? 0),
        'city_id' => old('target_filter.city_id', $targetFilter?->city_id ?? 0),
    ];

    $initialAdditionalAddresses = $isEdit
        ? $rfq->deliveryAddresses->map(fn ($a) => [
            'country_id' => $a->country_id ?? 0, 'state_id' => $a->state_id ?? 0, 'city_id' => $a->city_id ?? 0,
            'address' => $a->address, '_states' => [], '_cities' => [],
        ])->values()
        : collect();

    // Resuming an in-progress draft lands on the first step that isn't done
    // yet, rather than always restarting at 1 — a brand-new RFQ (even one
    // prefilled from a listing) always starts at step 1 so the buyer sees
    // that prefilled item first.
    $initialStep = 1;
    if ($isEdit && $rfq->status === 'draft') {
        $requestedStep = (int) request('step');
        if ($requestedStep >= 1 && $requestedStep <= 4) {
            $initialStep = $requestedStep;
        } elseif (!empty($rfq->current_step) && $rfq->current_step >= 1 && $rfq->current_step <= 4) {
            $initialStep = (int) $rfq->current_step;
        } else {
            $initialStep = match (true) {
                $items->isEmpty() => 1,
                empty(old('title', $rfq->title)) => 2,
                ! old('quotation_deadline', $rfq->quotation_deadline) || ($rfq->isInvited() && $invitedSuppliers->isEmpty()) => 3,
                default => 4,
            };
        }
    }
@endphp

@push('styles')
    {{-- Flatpickr — replaces the native browser datetime-local/date widgets
         (whose look varies wildly per browser) with one consistent, modern
         calendar UI for the deadline/expected-delivery fields below. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar { font-family: 'Inter', sans-serif; box-shadow: 0 10px 30px -5px rgba(0,0,0,.15), 0 0 0 1px rgba(0,0,0,.05); }
        .flatpickr-day.selected, .flatpickr-day.selected:hover { background: var(--theme-primary, #4f46e5); border-color: var(--theme-primary, #4f46e5); }
        .flatpickr-time input:hover, .flatpickr-time .flatpickr-am-pm:hover { background: #f3f4f6; }
    </style>
@endpush

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<form
    method="POST"
    action="{{ $action }}"
    x-data="rfqForm({
        items: {{ $initialItems->toJson() }},
        suppliers: {{ $initialSuppliers->toJson() }},
        visibilityTypeId: {{ (int) old('visibility_type_id', $defaultVtId) }},
        visibilityTypes: {{ isset($visibilityTypes) ? $visibilityTypes->toJson() : '[]' }},
        country: {{ (int) old('delivery_country_id', $rfq->delivery_country_id ?? 0) }},
        state: {{ (int) old('delivery_state_id', $rfq->delivery_state_id ?? 0) }},
        city: {{ (int) old('delivery_city_id', $rfq->delivery_city_id ?? 0) }},
        statesUrl: '{{ url('/lookup/countries') }}',
        citiesUrl: '{{ url('/lookup/states') }}',
        searchUrl: '{{ route('buyer.rfqs.supplier-search') }}',
        targetFilter: {{ collect($initialTargetFilter)->toJson() }},
        categoryAttributesUrl: '{{ url('/buyer/rfqs/categories') }}',
        listingsSearchUrl: '{{ route('buyer.rfqs.listings.search') }}',
        listingsPrefillUrl: '{{ url('/buyer/rfqs/listings') }}',
        title: {{ json_encode(old('title', $rfq->title ?? '')) }},
        description: {{ json_encode(old('description', $rfq->description ?? '')) }},
        currencyCode: {{ json_encode(old('currency_code', $rfq->currency_code ?? '')) }},
        budgetMin: {{ json_encode(old('budget_min', $rfq->budget_min ?? '')) }},
        budgetMax: {{ json_encode(old('budget_max', $rfq->budget_max ?? '')) }},
        deliveryAddress: {{ json_encode(old('delivery_address', $rfq->delivery_address ?? '')) }},
        quotationDeadline: {{ json_encode(old('quotation_deadline', optional($rfq->quotation_deadline)->format('Y-m-d H:i') ?? '')) }},
        qnaDeadline: {{ json_encode(old('qna_deadline', optional($rfq->qna_deadline)->format('Y-m-d H:i') ?? '')) }},
        expectedDeliveryDate: {{ json_encode(old('expected_delivery_date', optional($rfq->expected_delivery_date)->format('Y-m-d') ?? '')) }},
        allowPartialQuotation: {{ old('allow_partial_quotation', $rfq->allow_partial_quotation ?? true) ? 'true' : 'false' }},
        allowAlternativeProducts: {{ old('allow_alternative_products', $rfq->allow_alternative_products ?? true) ? 'true' : 'false' }},
        additionalAddresses: {{ $initialAdditionalAddresses->toJson() }},
        countryOptions: {{ \App\Models\Country::active()->get(['id', 'name'])->toJson() }},
        categoryMap: {{ $categories->pluck('name', 'id')->toJson() }},
        rfqId: {{ $isEdit ? $rfq->id : 'null' }},
        isEditingPublished: {{ $isEditingPublished ? 'true' : 'false' }},
        autosaveCreateUrl: '{{ route('buyer.rfqs.autosave.create') }}',
        autosaveUpdateUrlBase: '{{ url('/buyer/rfqs') }}',
        csrfToken: '{{ csrf_token() }}',
        initialStep: {{ $initialStep }},
    })"
    x-init="init()"
>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="rfq_id" :value="rfqId">
    <input type="hidden" name="current_step" :value="currentStep">

    {{-- ═══ Admin-style tab-bar step indicator ═══ --}}
    @php
        $rfqSteps = [
            1 => ['label' => 'Items',                 'icon' => 'fa-box-open'],
            2 => ['label' => 'Details & Delivery',    'icon' => 'fa-clipboard-list'],
            3 => ['label' => 'Deadlines & Suppliers', 'icon' => 'fa-clock'],
            4 => ['label' => 'Review & Submit',       'icon' => 'fa-circle-check'],
        ];
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 mb-4 overflow-hidden">
        {{-- Tab bar --}}
        <div class="border-b border-gray-200 px-2">
            <nav class="flex gap-0 -mb-px overflow-x-auto" aria-label="RFQ creation steps">
                @foreach($rfqSteps as $num => $step)
                    <button type="button" @click="setStep({{ $num }})"
                            class="flex items-center gap-2 px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors focus:outline-none
                                   {{ $num < count($rfqSteps) ? '' : '' }}"
                            :class="currentStep === {{ $num }}
                                ? 'border-indigo-600 text-indigo-600'
                                : (stepValid({{ $num }}) ? 'border-emerald-500 text-emerald-600 hover:text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300')">
                        {{-- Numbered badge --}}
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 transition-colors"
                              :class="stepValid({{ $num }})
                                  ? 'bg-emerald-500 text-white'
                                  : (currentStep === {{ $num }} ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500')">
                            <i class="fa-solid fa-check" x-show="stepValid({{ $num }})" x-cloak style="font-size:8px"></i>
                            <span x-show="!stepValid({{ $num }})">{{ $num }}</span>
                        </span>
                        {{-- Icon --}}
                        <i class="fa-solid {{ $step['icon'] }} text-xs"></i>
                        {{-- Label --}}
                        <span>{{ $step['label'] }}</span>
                    </button>
                @endforeach

                {{-- Auto-save pill pushed to right --}}
                <div class="flex-1 flex items-center justify-end px-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-50 border border-gray-200" x-show="rfqId || isSaving" x-cloak>
                        <span class="w-1.5 h-1.5 rounded-full" :class="isSaving ? 'bg-amber-400 animate-pulse' : 'bg-emerald-500'"></span>
                        <span x-text="isSaving ? 'Saving…' : 'Draft saved'"></span>
                    </span>
                    <p x-show="saveError" x-cloak class="text-xs text-red-600 ml-3" x-text="saveError"></p>
                </div>
            </nav>
        </div>
    </div>

    {{-- ═══════ STEP 1 — Items ═══════ --}}
    <div x-show="currentStep === 1" x-cloak class="flex flex-col" style="height:calc(100vh - 320px);min-height:460px;">
        <div class="flex-1 overflow-y-auto pr-1 space-y-4">

            {{-- Page header for step 1 --}}
            <div class="bg-white rounded-xl border border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-box-open text-indigo-500"></i>
                        RFQ Items
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Add every product or service you want suppliers to quote on. Linked marketplace products are pre-filled — just confirm the quantity.</p>
                </div>
            </div>

            {{-- Item cards --}}
            <template x-for="(item, index) in items" :key="index">
                @include('backend.buyer.procurement.rfqs.partials._item')
            </template>

            {{-- Action buttons in one single row after items --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="button" @click="addMarketplaceItem()"
                        class="inline-flex items-center justify-center gap-2 text-xs font-semibold px-4 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors">
                    <i class="fa-solid fa-store"></i> Add from Marketplace
                </button>
                <button type="button" @click="addCustomItem()"
                        class="inline-flex items-center justify-center gap-2 text-xs font-semibold px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 shadow-sm transition-colors">
                    <i class="fa-solid fa-pen-to-square"></i> Add Custom Item
                </button>
            </div>

            <p x-show="showStepError && !stepValid(1)" x-cloak class="text-xs text-red-600 px-1">Every item needs a name and a quantity greater than zero.</p>
        </div>

        <div class="pt-3 mt-2 border-t border-gray-100 flex justify-between items-center shrink-0">
            @if($isEditingPublished)
                <p class="text-xs text-gray-400">Editing a published RFQ — save changes to notify suppliers.</p>
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="setStep(2)" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                        Next: Details &amp; Delivery <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                    <button type="submit" class="btn-primary text-sm font-semibold px-5 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </div>
            @else
                <div></div>
                <button type="button" @click="goNext(1)" :disabled="isSaving" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60">
                    <span x-show="!isSaving">Next: Details &amp; Delivery <i class="fa-solid fa-arrow-right"></i></span>
                    <span x-show="isSaving" x-cloak>Saving…</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ═══════ STEP 2 — Details & Delivery ═══════ --}}
    <div x-show="currentStep === 2" x-cloak class="flex flex-col" style="height:calc(100vh - 360px);min-height:420px;">
        <div class="flex-1 overflow-y-auto pr-1 space-y-4">
            <x-backend.form-card title="Basic Information">
                <div class="space-y-4">
                    <div>
                        <x-backend.input name="title" label="RFQ Title" required x-model="title" placeholder="e.g. 500 units of A4 exercise books" />
                        <p x-show="showStepError && !title.trim()" x-cloak class="text-xs text-red-600 mt-1">Give this RFQ a title before continuing.</p>
                    </div>
                    <x-backend.textarea name="description" label="Description" x-model="description" hint="Explain what you need — specifications, use case, quality requirements." />
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-backend.select name="currency_code" label="Currency" placeholder="Select currency" x-model="currencyCode">
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->code }}">{{ $currency->code }} — {{ $currency->name }}</option>
                            @endforeach
                        </x-backend.select>
                        <x-backend.input type="number" name="budget_min" label="Budget Min" x-model="budgetMin" />
                        <x-backend.input type="number" name="budget_max" label="Budget Max" x-model="budgetMax" />
                    </div>
                </div>
            </x-backend.form-card>

            <x-backend.form-card title="Delivery" description="Add one or more delivery locations for this RFQ.">
                <div class="space-y-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Address 1</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                            <select name="delivery_country_id" x-model.number="country" @change="onCountryChange()" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select country</option>
                                @foreach(\App\Models\Country::active()->get(['id','name']) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                            <select name="delivery_state_id" x-model.number="state" @change="onStateChange()" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select state</option>
                                <template x-for="s in states" :key="s.id">
                                    <option :value="s.id" x-text="s.name" :selected="s.id === state"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                            <select name="delivery_city_id" x-model.number="city" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select city</option>
                                <template x-for="c in cities" :key="c.id">
                                    <option :value="c.id" x-text="c.name" :selected="c.id === city"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <x-backend.textarea name="delivery_address" label="Delivery Address" x-model="deliveryAddress" rows="2" />

                    <template x-for="(addr, idx) in additionalAddresses" :key="idx">
                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Address <span x-text="idx + 2"></span></p>
                                <button type="button" @click="removeAddress(idx)" class="text-xs text-red-500 hover:text-red-700"><i class="fa-solid fa-trash"></i> Remove</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                                    <select :name="'additional_addresses['+idx+'][country_id]'" x-model.number="addr.country_id" @change="onAddressCountryChange(addr)" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                        <option value="0">Select country</option>
                                        <template x-for="c in countryOptions" :key="c.id">
                                            <option :value="c.id" x-text="c.name" :selected="c.id === addr.country_id"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                                    <select :name="'additional_addresses['+idx+'][state_id]'" x-model.number="addr.state_id" @change="onAddressStateChange(addr)" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                        <option value="0">Select state</option>
                                        <template x-for="s in addr._states" :key="s.id">
                                            <option :value="s.id" x-text="s.name" :selected="s.id === addr.state_id"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                                    <select :name="'additional_addresses['+idx+'][city_id]'" x-model.number="addr.city_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                        <option value="0">Select city</option>
                                        <template x-for="c in addr._cities" :key="c.id">
                                            <option :value="c.id" x-text="c.name" :selected="c.id === addr.city_id"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Address</label>
                                <textarea :name="'additional_addresses['+idx+'][address]'" x-model="addr.address" rows="2" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"></textarea>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addAddress()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Add Address
                    </button>
                </div>
            </x-backend.form-card>
        </div>

        <div class="pt-3 mt-2 border-t border-gray-100 flex justify-between shrink-0">
            <button type="button" @click="setStep(1)" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </button>
            @if($isEditingPublished)
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="setStep(3)" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                        Next: Deadlines &amp; Suppliers <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                    <button type="submit" class="btn-primary text-sm font-semibold px-5 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </div>
            @else
                <button type="button" @click="goNext(2)" :disabled="isSaving" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60">
                    <span x-show="!isSaving">Next: Deadlines &amp; Suppliers <i class="fa-solid fa-arrow-right"></i></span>
                    <span x-show="isSaving" x-cloak>Saving…</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ═══════ STEP 3 — Deadlines & Suppliers ═══════ --}}
    <div x-show="currentStep === 3" x-cloak class="flex flex-col" style="height:calc(100vh - 360px);min-height:420px;">
        <div class="flex-1 overflow-y-auto pr-1">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-backend.form-card title="Deadlines & Options">
                    <div class="space-y-4">
                        <div>
                            <x-backend.input type="text" name="quotation_deadline" label="Quotation Deadline" required
                                             x-model="quotationDeadline" x-ref="quotationDeadlineInput"
                                             autocomplete="off" placeholder="Select date &amp; time" />
                            <p x-show="showStepError && !quotationDeadline" x-cloak class="text-xs text-red-600 mt-1">Set a quotation deadline before continuing.</p>
                        </div>
                        <div>
                            <x-backend.input type="text" name="qna_deadline" label="Q&A Deadline"
                                             x-model="qnaDeadline" x-ref="qnaDeadlineInput"
                                             autocomplete="off" placeholder="Select date &amp; time" />
                            <p x-show="qnaDeadlineInvalid()" x-cloak class="text-xs text-red-600 mt-1">Q&amp;A deadline must be before the quotation deadline.</p>
                        </div>
                        <x-backend.input type="text" name="expected_delivery_date" label="Expected Delivery Date"
                                         x-model="expectedDeliveryDate" x-ref="expectedDeliveryInput"
                                         autocomplete="off" placeholder="Select date" />

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="allow_partial_quotation" value="0">
                            <input type="checkbox" name="allow_partial_quotation" value="1" x-model="allowPartialQuotation" class="rounded border-gray-300" style="accent-color:var(--theme-primary)">
                            Allow partial quotations
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="allow_alternative_products" value="0">
                            <input type="checkbox" name="allow_alternative_products" value="1" x-model="allowAlternativeProducts" class="rounded border-gray-300" style="accent-color:var(--theme-primary)">
                            Allow alternative products
                        </label>
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Supplier Targeting & Visibility" description="Choose how you want to reach suppliers for this RFQ.">
                    {{-- Notice banner when switching options with previous data --}}
                    <div x-show="switchNotice" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mb-3.5 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="fa-solid fa-circle-info text-amber-500 text-sm shrink-0"></i>
                            <span x-text="switchNotice" class="font-medium leading-snug"></span>
                        </div>
                        <button type="button" @click="switchNotice = ''" class="text-amber-500 hover:text-amber-800 p-1 shrink-0" title="Dismiss">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Hidden inputs for form submission --}}
                    <input type="hidden" name="visibility_type_id" :value="visibilityTypeId">
                    <template x-if="getVisibilityCode() === 'direct' && directSupplier">
                        <input type="hidden" name="selected_supplier_ids[]" :value="directSupplier.id">
                    </template>
                    <template x-if="getVisibilityCode() === 'invited'">
                        <template x-for="s in multipleSuppliers" :key="s.id">
                            <input type="hidden" name="selected_supplier_ids[]" :value="s.id">
                        </template>
                    </template>

                    <div class="space-y-3">
                        {{-- ─────────────────────────────────────────────────────────────
                             OPTION 1: Specific Supplier (direct)
                             ───────────────────────────────────────────────────────────── --}}
                        @php($vtDirect = $visibilityTypes->firstWhere('code', 'direct'))
                        @if($vtDirect)
                            <div class="rounded-xl border transition-all"
                                 :class="getVisibilityCode() === 'direct'
                                     ? 'border-indigo-500 bg-white ring-1 ring-indigo-500/30 shadow-xs'
                                     : 'border-gray-200 bg-white hover:border-gray-300'">

                                {{-- Radio Accordion Header --}}
                                <div @click="selectVisibilityType({{ $vtDirect->id }})"
                                     class="flex items-center justify-between p-3.5 cursor-pointer select-none transition-colors"
                                     :class="getVisibilityCode() === 'direct' ? 'bg-indigo-50/40 border-b border-indigo-100/60 rounded-t-xl' : 'bg-white hover:bg-gray-50/60 rounded-xl'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="radio" name="_vt_radio" :checked="getVisibilityCode() === 'direct'"
                                               @change="selectVisibilityType({{ $vtDirect->id }})"
                                               class="focus-accent text-indigo-600 cursor-pointer">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-gray-900">Specific Supplier</span>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Single Target</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5">Send RFQ to one supplier only.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2.5 shrink-0 ml-3">
                                        <span x-show="directSupplier" class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                                            <i class="fa-solid fa-check text-[9px]"></i>
                                            <span class="max-w-[120px] truncate" x-text="directSupplier ? directSupplier.name : ''"></span>
                                        </span>
                                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                                           :class="getVisibilityCode() === 'direct' ? 'rotate-180 text-indigo-600' : ''"></i>
                                    </div>
                                </div>

                                {{-- Accordion Body --}}
                                <div x-show="getVisibilityCode() === 'direct'" x-cloak class="p-4 bg-white space-y-3 rounded-b-xl">
                                    {{-- Selected Supplier Preview Card --}}
                                    <div x-show="directSupplier" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                                                <i class="fa-solid fa-store"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-900 truncate" x-text="directSupplier ? directSupplier.name : ''"></p>
                                                <p class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Targeted Supplier
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button" @click="changeDirectSupplier()"
                                                class="text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 px-2.5 py-1 rounded-lg transition-colors shrink-0">
                                            <i class="fa-solid fa-xmark mr-1"></i> Change
                                        </button>
                                    </div>

                                    {{-- Search Input & In-flow Results (Never Hidden/Clipped) --}}
                                    <div x-show="!directSupplier" class="space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-semibold text-gray-700">Search &amp; Select Supplier <span class="text-red-500">*</span></label>
                                            <button type="button" x-show="_savedDirectSupplier" @click="cancelChangeDirectSupplier()"
                                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                Keep Current Supplier
                                            </button>
                                        </div>

                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </span>
                                            <input type="text" id="directSupplierInput" x-model="directSupplierQuery" @input.debounce.250ms="searchDirectSupplier()"
                                                   placeholder="Type supplier name to search..."
                                                   class="focus-accent w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">
                                        </div>

                                        {{-- In-flow Results Box (expands container smoothly, 100% visible) --}}
                                        <div x-show="directSupplierQuery.trim().length > 0" class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs max-h-56 overflow-y-auto">
                                            <template x-if="isSearchingDirectSupplier">
                                                <p class="px-3 py-2.5 text-xs text-gray-400 flex items-center gap-2">
                                                    <i class="fa-solid fa-spinner fa-spin text-indigo-500"></i> Searching suppliers…
                                                </p>
                                            </template>
                                            <template x-if="!isSearchingDirectSupplier && directSupplierResults.length === 0">
                                                <p class="px-3 py-2.5 text-xs text-gray-400">No matching suppliers found.</p>
                                            </template>
                                            <template x-if="!isSearchingDirectSupplier && directSupplierResults.length > 0">
                                                <div>
                                                    <template x-for="s in directSupplierResults" :key="s.id">
                                                        <button type="button" @click="selectDirectSupplier(s)"
                                                                class="w-full text-left px-3 py-2.5 text-xs hover:bg-indigo-50 flex items-center justify-between gap-2 border-b border-gray-100 last:border-0 transition-colors">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                <i class="fa-solid fa-store text-indigo-500 text-[11px] shrink-0"></i>
                                                                <span class="font-medium text-gray-900 truncate" x-text="s.name"></span>
                                                            </div>
                                                            <span class="text-[11px] text-indigo-600 font-semibold px-2 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 shrink-0">Select</span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>

                                        <p x-show="showStepError && !directSupplier" x-cloak class="text-xs text-red-600">Select a supplier before continuing.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ─────────────────────────────────────────────────────────────
                             OPTION 2: Selected Suppliers (invited)
                             ───────────────────────────────────────────────────────────── --}}
                        @php($vtInvited = $visibilityTypes->firstWhere('code', 'invited'))
                        @if($vtInvited)
                            <div class="rounded-xl border transition-all"
                                 :class="getVisibilityCode() === 'invited'
                                     ? 'border-indigo-500 bg-white ring-1 ring-indigo-500/30 shadow-xs'
                                     : 'border-gray-200 bg-white hover:border-gray-300'">

                                {{-- Radio Accordion Header --}}
                                <div @click="selectVisibilityType({{ $vtInvited->id }})"
                                     class="flex items-center justify-between p-3.5 cursor-pointer select-none transition-colors"
                                     :class="getVisibilityCode() === 'invited' ? 'bg-indigo-50/40 border-b border-indigo-100/60 rounded-t-xl' : 'bg-white hover:bg-gray-50/60 rounded-xl'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="radio" name="_vt_radio" :checked="getVisibilityCode() === 'invited'"
                                               @change="selectVisibilityType({{ $vtInvited->id }})"
                                               class="focus-accent text-indigo-600 cursor-pointer">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-gray-900">Selected Suppliers</span>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">Shortlist</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5">Invite multiple suppliers manually.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2.5 shrink-0 ml-3">
                                        <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
                                              :class="multipleSuppliers.length > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-500'"
                                              x-text="multipleSuppliers.length + ' selected'"></span>
                                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                                           :class="getVisibilityCode() === 'invited' ? 'rotate-180 text-indigo-600' : ''"></i>
                                    </div>
                                </div>

                                {{-- Accordion Body --}}
                                <div x-show="getVisibilityCode() === 'invited'" x-cloak class="p-4 bg-white space-y-3.5 rounded-b-xl">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-xs font-semibold text-gray-700">Add Suppliers <span class="text-red-500">*</span></label>
                                            <span class="text-[11px] text-gray-400" x-show="multipleSuppliers.length > 0" x-text="multipleSuppliers.length + ' supplier(s) in shortlist'"></span>
                                        </div>

                                        {{-- Search Input & In-flow Results --}}
                                        <div class="space-y-2">
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                </span>
                                                <input type="text" x-model="invitedSupplierQuery" @input.debounce.250ms="searchInvitedSuppliers()"
                                                       placeholder="Type a supplier name to search and add..."
                                                       class="focus-accent w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">
                                            </div>

                                            <div x-show="invitedSupplierQuery.trim().length > 0" class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs max-h-52 overflow-y-auto">
                                                <template x-if="isSearchingInvitedSuppliers">
                                                    <p class="px-3 py-2.5 text-xs text-gray-400 flex items-center gap-2">
                                                        <i class="fa-solid fa-spinner fa-spin text-indigo-500"></i> Searching suppliers…
                                                    </p>
                                                </template>
                                                <template x-if="!isSearchingInvitedSuppliers && invitedSupplierResults.length === 0">
                                                    <p class="px-3 py-2.5 text-xs text-gray-400">No matching suppliers found.</p>
                                                </template>
                                                <template x-if="!isSearchingInvitedSuppliers && invitedSupplierResults.length > 0">
                                                    <div>
                                                        <template x-for="s in invitedSupplierResults" :key="s.id">
                                                            <label @mousedown.prevent="toggleInvitedSupplier(s)"
                                                                   class="w-full text-left px-3 py-2.5 text-xs flex items-center gap-2.5 border-b border-gray-100 last:border-0 transition-colors cursor-pointer"
                                                                   :class="isInvitedSupplierSelected(s.id) ? 'bg-indigo-50/70' : 'hover:bg-indigo-50'">
                                                                <input type="checkbox" :checked="isInvitedSupplierSelected(s.id)" tabindex="-1"
                                                                       class="focus-accent rounded text-indigo-600 pointer-events-none shrink-0">
                                                                <i class="fa-solid fa-store text-indigo-500 text-[11px] shrink-0"></i>
                                                                <span class="font-medium text-gray-900 truncate flex-1" x-text="s.name"></span>
                                                                <span class="text-[10px] font-semibold shrink-0"
                                                                      :class="isInvitedSupplierSelected(s.id) ? 'text-indigo-600' : 'text-gray-400'"
                                                                      x-text="isInvitedSupplierSelected(s.id) ? 'Selected' : 'Add'"></span>
                                                            </label>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                            <p x-show="invitedSupplierLimitNotice" x-cloak class="text-xs text-amber-600" x-text="invitedSupplierLimitNotice"></p>
                                        </div>
                                    </div>

                                    {{-- Selected Supplier Tags --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Selected Shortlist</span>
                                            <button type="button" x-show="multipleSuppliers.length > 1" @click="clearAllInvitedSuppliers()"
                                                    class="text-[11px] text-red-500 hover:text-red-700 font-medium">
                                                Clear All
                                            </button>
                                        </div>

                                        <div x-show="multipleSuppliers.length > 0" class="flex flex-wrap gap-2">
                                            <template x-for="(s, idx) in multipleSuppliers" :key="s.id">
                                                <span class="inline-flex items-center gap-1.5 text-xs font-medium pl-3 pr-2 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    <i class="fa-solid fa-store text-[10px] text-indigo-500"></i>
                                                    <span x-text="s.name"></span>
                                                    <button type="button" @click="removeInvitedSupplier(idx)" class="text-indigo-400 hover:text-red-600 ml-1 p-0.5 rounded transition-colors" title="Remove supplier">
                                                        <i class="fa-solid fa-xmark text-xs"></i>
                                                    </button>
                                                </span>
                                            </template>
                                        </div>

                                        <div x-show="multipleSuppliers.length === 0" class="p-3 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-center">
                                            <p class="text-xs text-gray-400">No suppliers in shortlist. Search above to add suppliers.</p>
                                        </div>

                                        <p x-show="showStepError && multipleSuppliers.length === 0" x-cloak class="text-xs text-red-600 mt-2">
                                            Select at least one supplier to invite.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ─────────────────────────────────────────────────────────────
                             OPTION 3: All Eligible Suppliers (open_matching)
                             ───────────────────────────────────────────────────────────── --}}
                        @php($vtOpen = $visibilityTypes->firstWhere('code', 'open_matching'))
                        @if($vtOpen)
                            <div class="rounded-xl border transition-all"
                                 :class="getVisibilityCode() === 'open_matching'
                                     ? 'border-indigo-500 bg-white ring-1 ring-indigo-500/30 shadow-xs'
                                     : 'border-gray-200 bg-white hover:border-gray-300'">

                                {{-- Radio Accordion Header --}}
                                <div @click="selectVisibilityType({{ $vtOpen->id }})"
                                     class="flex items-center justify-between p-3.5 cursor-pointer select-none transition-colors"
                                     :class="getVisibilityCode() === 'open_matching' ? 'bg-indigo-50/40 border-b border-indigo-100/60 rounded-t-xl' : 'bg-white hover:bg-gray-50/60 rounded-xl'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="radio" name="_vt_radio" :checked="getVisibilityCode() === 'open_matching'"
                                               @change="selectVisibilityType({{ $vtOpen->id }})"
                                               class="focus-accent text-indigo-600 cursor-pointer">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-gray-900">All Eligible Suppliers</span>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Automated</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5">Automatically match suppliers based on category and location.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2.5 shrink-0 ml-3">
                                        <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Open Matching
                                        </span>
                                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                                           :class="getVisibilityCode() === 'open_matching' ? 'rotate-180 text-indigo-600' : ''"></i>
                                    </div>
                                </div>

                                {{-- Accordion Body: Matching Rules --}}
                                <div x-show="getVisibilityCode() === 'open_matching'" x-cloak class="p-4 bg-white space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Match by Category</label>
                                        <select name="target_filter[category_id]" x-model="targetFilter.category_id"
                                                class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                            <option value="">Any category (all eligible suppliers)</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-[11px] text-gray-400 mt-1">Recommended — restricts matching to suppliers registered in this category.</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Match by Supplier Location</label>
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            @foreach(['none' => 'Anywhere', 'country' => 'Country', 'state' => 'State', 'city' => 'City'] as $level => $levelLabel)
                                                <label class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border cursor-pointer transition-colors"
                                                       :class="targetFilter.location_match_level === '{{ $level }}'
                                                           ? 'border-indigo-600 bg-indigo-50/60 text-indigo-700 font-semibold ring-1 ring-indigo-500'
                                                           : 'border-gray-200 text-gray-600 hover:bg-gray-50 bg-white'">
                                                    <input type="radio" name="target_filter[location_match_level]" value="{{ $level }}" x-model="targetFilter.location_match_level" class="sr-only">
                                                    {{ $levelLabel }}
                                                </label>
                                            @endforeach
                                        </div>

                                        {{-- Location Dropdowns --}}
                                        <div x-show="targetFilter.location_match_level !== 'none'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-2 pt-2 border-t border-gray-100">
                                            <select name="target_filter[country_id]" x-model.number="targetFilter.country_id" @change="onTargetCountryChange()"
                                                    class="focus-accent text-xs rounded-lg border border-gray-300 px-2 py-2 bg-white">
                                                <option value="0">Select country</option>
                                                @foreach(\App\Models\Country::active()->get(['id','name']) as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                            <select name="target_filter[state_id]" x-model.number="targetFilter.state_id" @change="onTargetStateChange()"
                                                    x-show="targetFilter.location_match_level === 'state' || targetFilter.location_match_level === 'city'"
                                                    class="focus-accent text-xs rounded-lg border border-gray-300 px-2 py-2 bg-white">
                                                <option value="0">Select state</option>
                                                <template x-for="s in targetStates" :key="s.id">
                                                    <option :value="s.id" x-text="s.name" :selected="s.id === targetFilter.state_id"></option>
                                                </template>
                                            </select>
                                            <select name="target_filter[city_id]" x-model.number="targetFilter.city_id"
                                                    x-show="targetFilter.location_match_level === 'city'"
                                                    class="focus-accent text-xs rounded-lg border border-gray-300 px-2 py-2 bg-white">
                                                <option value="0">Select city</option>
                                                <template x-for="c in targetCities" :key="c.id">
                                                    <option :value="c.id" x-text="c.name" :selected="c.id === targetFilter.city_id"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <p x-show="showStepError && targetFilter.location_match_level !== 'none' && !targetFilterLocationSelected()" x-cloak class="text-xs text-red-600 mt-1">
                                            Select a <span x-text="targetFilter.location_match_level"></span>, or switch back to "Anywhere".
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-backend.form-card>
            </div>
        </div>

        <div class="pt-3 mt-2 border-t border-gray-100 flex justify-between shrink-0">
            <button type="button" @click="setStep(2)" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </button>
            @if($isEditingPublished)
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="setStep(4)" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                        Next: Review &amp; Submit <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                    <button type="submit" class="btn-primary text-sm font-semibold px-5 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </div>
            @else
                <button type="button" @click="goNext(3)" :disabled="isSaving" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60">
                    <span x-show="!isSaving">Next: Review &amp; Submit <i class="fa-solid fa-arrow-right"></i></span>
                    <span x-show="isSaving" x-cloak>Saving…</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ═══════ STEP 4 — Review & Submit ═══════ --}}
    <div x-show="currentStep === 4" x-cloak class="flex flex-col" style="height:calc(100vh - 360px);min-height:420px;">
        <div class="flex-1 overflow-y-auto pr-1">
            <x-backend.form-card title="Review Your RFQ" description="Double-check the details below, then save.">
                @include('backend.buyer.procurement.rfqs.partials._review-summary')
            </x-backend.form-card>
        </div>

        <div class="pt-3 mt-2 border-t border-gray-100 shrink-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <button type="button" @click="setStep(3)" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Back
                </button>
                <div class="flex items-center gap-2">
                    <a href="{{ $isEdit ? route('buyer.rfqs.show', $rfq) : route('buyer.rfqs.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                    @if($isEditingPublished)
                        <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
                            <i class="fa-solid fa-check"></i> Save Changes
                        </button>
                    @else
                        <button type="button" @click="$dispatch('open-modal-rfq-preview')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            <i class="fa-regular fa-eye mr-1"></i> Preview
                        </button>
                        <button type="submit" name="action" value="draft" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Save Draft
                        </button>
                        <button type="submit" name="action" value="publish" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Final Submit
                        </button>
                    @endif
                </div>
            </div>
            @if($isEditingPublished)
                <p class="text-xs text-gray-400 text-center mt-2">This RFQ is already published — saving will record a new version and notify suppliers with a live quotation if the changes are material.</p>
            @else
                <p class="text-xs text-gray-400 text-center mt-2">Every step is saved as a draft automatically — you can safely leave and continue later from "My RFQs."</p>
            @endif
        </div>
    </div>

    @unless($isEditingPublished)
        <x-backend.modal id="rfq-preview" title="Preview Your RFQ" width="max-w-2xl">
            @include('backend.buyer.procurement.rfqs.partials._review-summary')
            <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
                <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Continue Editing</button>
                <button type="submit" name="action" value="publish" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Final Submit</button>
            </div>
        </x-backend.modal>
    @endunless
</form>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rfqForm', (config) => ({
            items: config.items,
            suppliers: config.suppliers,
            visibilityTypeId: config.visibilityTypeId,
            visibilityTypes: config.visibilityTypes,
            country: config.country,
            state: config.state,
            city: config.city,
            categoryMap: config.categoryMap || {},
            states: [],
            cities: [],
            supplierQuery: '',
            supplierResults: [],

            // Radio accordion state & background persistence
            directSupplier: null,
            _savedDirectSupplier: null,
            isSearchingDirectSupplier: false,
            multipleSuppliers: [],
            directSupplierQuery: '',
            directSupplierResults: [],
            invitedSupplierQuery: '',
            invitedSupplierResults: [],
            isSearchingInvitedSuppliers: false,
            invitedSupplierLimitNotice: '',
            switchNotice: '',
            _switchNoticeTimer: null,

            targetFilter: config.targetFilter,
            targetStates: [],
            targetCities: [],

            title: config.title,
            description: config.description,
            currencyCode: config.currencyCode,
            budgetMin: config.budgetMin,
            budgetMax: config.budgetMax,
            deliveryAddress: config.deliveryAddress,
            quotationDeadline: config.quotationDeadline,
            qnaDeadline: config.qnaDeadline,
            expectedDeliveryDate: config.expectedDeliveryDate,
            allowPartialQuotation: config.allowPartialQuotation,
            allowAlternativeProducts: config.allowAlternativeProducts,

            additionalAddresses: config.additionalAddresses,
            countryOptions: config.countryOptions,

            rfqId: config.rfqId,
            isEditingPublished: config.isEditingPublished,
            isSaving: false,
            saveError: null,

            currentStep: config.initialStep || 1,
            showStepError: false,
            stepDefs: [
                { num: 1, label: 'Items' },
                { num: 2, label: 'Details & Delivery' },
                { num: 3, label: 'Deadlines & Suppliers' },
                { num: 4, label: 'Review & Submit' },
            ],

            stepValid(n) {
                if (n === 1) {
                    return this.items.length > 0 && this.items.every(i =>
                        (i.item_name || '').toString().trim() !== '' && parseFloat(i.quantity) > 0
                    );
                }
                if (n === 2) {
                    return (this.title || '').toString().trim() !== '';
                }
                if (n === 3) {
                    if (!this.quotationDeadline) return false;
                    if (this.qnaDeadlineInvalid()) return false;
                    const code = this.getVisibilityCode();
                    if (code === 'direct') {
                        return !!this.directSupplier;
                    }
                    if (code === 'invited') {
                        return this.multipleSuppliers.length > 0;
                    }
                    if (code === 'open_matching') {
                        return this.targetFilterLocationSelected();
                    }
                    return true;
                }
                if (n === 4) {
                    return this.stepValid(1) && this.stepValid(2) && this.stepValid(3);
                }
                return true;
            },
            targetFilterLocationSelected() {
                const lvl = this.targetFilter.location_match_level;
                if (lvl === 'none') return true;
                if (lvl === 'country') return !!this.targetFilter.country_id;
                if (lvl === 'state') return !!this.targetFilter.state_id;
                if (lvl === 'city') return !!this.targetFilter.city_id;
                return true;
            },
            /*
             * Q&A deadline is optional, but the backend rejects it (422, no
             * field highlighted client-side) when it isn't strictly BEFORE
             * the quotation deadline (Illuminate's `before:` rule). Both
             * values are native datetime-local strings ("YYYY-MM-DDTHH:MM"),
             * which sort lexicographically the same as chronologically.
             */
            qnaDeadlineInvalid() {
                return !!(this.qnaDeadline && this.quotationDeadline && this.qnaDeadline >= this.quotationDeadline);
            },

            buildPayload() {
                return {
                    current_step: this.currentStep,
                    title: this.title,
                    description: this.description,
                    currency_code: this.currencyCode,
                    budget_min: this.budgetMin,
                    budget_max: this.budgetMax,
                    delivery_country_id: this.country,
                    delivery_state_id: this.state,
                    delivery_city_id: this.city,
                    delivery_address: this.deliveryAddress,
                    allow_partial_quotation: this.allowPartialQuotation,
                    allow_alternative_products: this.allowAlternativeProducts,
                    quotation_deadline: this.quotationDeadline,
                    qna_deadline: this.qnaDeadline,
                    expected_delivery_date: this.expectedDeliveryDate,
                    visibility_type_id: this.visibilityTypeId,
                    selected_supplier_ids: this.getActiveSupplierIds(),
                    target_filter: this.targetFilter,
                    additional_addresses: this.additionalAddresses.map(a => ({
                        country_id: a.country_id || null, state_id: a.state_id || null, city_id: a.city_id || null, address: a.address,
                    })),
                    items: this.items.map(i => ({
                        id: i.id, item_type: i.item_type, listing_id: i.listing_id, category_id: i.category_id,
                        item_name: i.item_name, description: i.description, quantity: i.quantity,
                        unit_id: i.unit_id, custom_unit: i.custom_unit, estimated_unit_price: i.estimated_unit_price,
                        attribute_values: i.attribute_values,
                        custom_attributes: i.custom_attributes || [],
                        specs: i.custom_attributes || [],
                    })),
                };
            },
            syncUrl() {
                if (!window.history || !window.history.replaceState) return;
                try {
                    let pathname = window.location.pathname;
                    if (this.rfqId) {
                        const origin = window.location.origin;
                        const base = config.autosaveUpdateUrlBase.startsWith(origin)
                            ? config.autosaveUpdateUrlBase.slice(origin.length)
                            : config.autosaveUpdateUrlBase;
                        pathname = `${base}/${this.rfqId}/edit`;
                    }
                    const url = new URL(window.location.href);
                    url.pathname = pathname;
                    url.searchParams.set('step', this.currentStep);
                    if (this.rfqId) {
                        url.searchParams.delete('listing');
                    }
                    window.history.replaceState({ step: this.currentStep, rfqId: this.rfqId }, '', url.toString());
                } catch (e) {
                    // Ignore URL manipulation failures
                }
            },
            async autosave() {
                if (this.isEditingPublished) {
                    return true;
                }
                this.isSaving = true;
                this.saveError = null;
                try {
                    const url = this.rfqId ? (config.autosaveUpdateUrlBase + '/' + this.rfqId + '/autosave') : config.autosaveCreateUrl;
                    const res = await fetch(url, {
                        method: this.rfqId ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify(this.buildPayload()),
                    });
                    if (!res.ok) {
                        // Surface the actual field error(s) instead of a dead-end generic
                        // message — nothing in this form is highlighted from a 422 alone,
                        // so without this the buyer has no way to tell what's wrong.
                        let detail = '';
                        try {
                            const body = await res.json();
                            if (body && body.errors) {
                                detail = Object.values(body.errors).flat().join(' ');
                            } else if (body && body.message) {
                                detail = body.message;
                            }
                        } catch (e) {
                            // Non-JSON error body — fall back to the generic message below.
                        }
                        this.saveError = detail || 'Could not save your progress — please check the highlighted fields.';
                        return false;
                    }
                    const data = await res.json();
                    if (!this.rfqId && data.id) {
                        this.rfqId = data.id;
                    }
                    this.syncUrl();
                    return true;
                } catch (e) {
                    this.saveError = 'Could not save your progress — check your connection and try again.';
                    return false;
                } finally {
                    this.isSaving = false;
                }
            },
            setStep(step) {
                if (this.currentStep === step) return;
                this.currentStep = step;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (this.rfqId && !this.isEditingPublished) {
                    this.autosave();
                }
            },
            async goNext(n) {
                if (n < 4 && !this.stepValid(n)) {
                    this.showStepError = true;
                    return;
                }
                this.showStepError = false;
                this.currentStep = n + 1;
                const saved = await this.autosave();
                if (!saved) {
                    this.currentStep = n;
                    return;
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            isInvitedMode() {
                const vt = this.visibilityTypes.find(t => t.id == this.visibilityTypeId);
                return vt ? vt.engine_type === 'invited' : false;
            },
            isOpenMatchingMode() {
                const vt = this.visibilityTypes.find(t => t.id == this.visibilityTypeId);
                return vt ? vt.code === 'open_matching' : false;
            },
            maxSuppliers() {
                const vt = this.visibilityTypes.find(t => t.id == this.visibilityTypeId);
                return vt ? vt.max_suppliers : null;
            },
            canAddSupplier() {
                const limit = this.maxSuppliers();
                return !limit || this.suppliers.length < limit;
            },

            init() {
                if (this.country) this.loadStates(this.country, false);
                if (this.state) this.loadCities(this.state, false);
                if (this.targetFilter.country_id) this.loadTargetStates(this.targetFilter.country_id);
                if (this.targetFilter.state_id) this.loadTargetCities(this.targetFilter.state_id);
                this.items.forEach(item => {
                    if (item.category_id) {
                        // Remember the category these attribute_values actually belong to,
                        // so onItemCategoryChange() can tell a genuine category switch
                        // apart from a spurious/duplicate change event on page load and
                        // never wipe already-filled-in (e.g. listing-prefilled) answers.
                        item._attrCategoryId = item.category_id;
                        this.fetchItemAttributes(item);
                    }
                });
                this.additionalAddresses.forEach(addr => this.initAddressRow(addr));

                // Initialize directSupplier and multipleSuppliers
                if (config.suppliers && config.suppliers.length > 0) {
                    this.directSupplier = { ...config.suppliers[0] };
                    this._savedDirectSupplier = { ...config.suppliers[0] };
                    this.multipleSuppliers = config.suppliers.map(s => ({ ...s }));
                }

                // If open_matching and category not set, prefill from first item with a category
                if (!this.targetFilter.category_id && this.items.length > 0 && this.items[0].category_id) {
                    this.targetFilter.category_id = this.items[0].category_id;
                }

                // Keep URL query string ?step= synchronized with currentStep
                this.$watch('currentStep', () => {
                    this.syncUrl();
                });
                if (this.rfqId) {
                    this.syncUrl();
                }
                window.addEventListener('popstate', () => {
                    const urlParams = new URLSearchParams(window.location.search);
                    const stepParam = parseInt(urlParams.get('step'), 10);
                    if (stepParam && stepParam >= 1 && stepParam <= 4 && stepParam !== this.currentStep) {
                        this.currentStep = stepParam;
                    }
                });

                this.$nextTick(() => this.initDatePickers());
            },
            /*
             * Flatpickr replaces the native datetime-local/date inputs (which
             * render as a different, dated-looking widget in every browser)
             * with one consistent calendar UI. It fires real 'input'/'change'
             * events on the underlying text input, so x-model keeps working
             * unchanged — this only adds the calendar UI and the cross-field
             * max-date link between the two deadlines.
             */
            initDatePickers() {
                if (typeof flatpickr === 'undefined') return;

                // `maxDate`/`defaultDate` must be OMITTED entirely (not passed as
                // null) when there's no real value — flatpickr's constructor does
                // not treat an explicit `null` the same as "no restriction" the
                // way its own .set() calls do, so passing null here was capping
                // every field at today (new Date(null) == the Unix epoch, which
                // flatpickr's fallback then read back as "now").
                const qnaConfig = { enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i' };
                if (this.qnaDeadline) qnaConfig.defaultDate = this.qnaDeadline;
                if (this.quotationDeadline) qnaConfig.maxDate = this.quotationDeadline;
                const qnaFp = flatpickr(this.$refs.qnaDeadlineInput, qnaConfig);

                const quotationConfig = {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i',
                    onChange: (selectedDates, dateStr) => {
                        if (dateStr) {
                            qnaFp.set('maxDate', dateStr);
                        } else {
                            qnaFp.set('maxDate', '');
                        }
                    },
                };
                if (this.quotationDeadline) quotationConfig.defaultDate = this.quotationDeadline;
                flatpickr(this.$refs.quotationDeadlineInput, quotationConfig);

                const deliveryConfig = { dateFormat: 'Y-m-d' };
                if (this.expectedDeliveryDate) deliveryConfig.defaultDate = this.expectedDeliveryDate;
                flatpickr(this.$refs.expectedDeliveryInput, deliveryConfig);
            },

            onTargetCountryChange() {
                this.targetFilter.state_id = 0; this.targetFilter.city_id = 0; this.targetCities = [];
                this.loadTargetStates(this.targetFilter.country_id);
            },
            onTargetStateChange() {
                this.targetFilter.city_id = 0;
                this.loadTargetCities(this.targetFilter.state_id);
            },
            loadTargetStates(countryId) {
                if (!countryId) { this.targetStates = []; return; }
                fetch(config.statesUrl + '/' + countryId + '/states').then(r => r.json()).then(d => { this.targetStates = d; });
            },
            loadTargetCities(stateId) {
                if (!stateId) { this.targetCities = []; return; }
                fetch(config.citiesUrl + '/' + stateId + '/cities').then(r => r.json()).then(d => { this.targetCities = d; });
            },

            addAddress() {
                this.additionalAddresses.push({ country_id: 0, state_id: 0, city_id: 0, address: '', _states: [], _cities: [] });
            },
            removeAddress(idx) {
                this.additionalAddresses.splice(idx, 1);
            },
            initAddressRow(addr) {
                if (addr.country_id) {
                    fetch(config.statesUrl + '/' + addr.country_id + '/states').then(r => r.json()).then(d => { addr._states = d; });
                }
                if (addr.state_id) {
                    fetch(config.citiesUrl + '/' + addr.state_id + '/cities').then(r => r.json()).then(d => { addr._cities = d; });
                }
            },
            onAddressCountryChange(addr) {
                addr.state_id = 0; addr.city_id = 0; addr._cities = [];
                if (!addr.country_id) { addr._states = []; return; }
                fetch(config.statesUrl + '/' + addr.country_id + '/states').then(r => r.json()).then(d => { addr._states = d; });
            },
            onAddressStateChange(addr) {
                addr.city_id = 0;
                if (!addr.state_id) { addr._cities = []; return; }
                fetch(config.citiesUrl + '/' + addr.state_id + '/cities').then(r => r.json()).then(d => { addr._cities = d; });
            },

            addItem() {
                this.addCustomItem();
            },
            addCustomItem() {
                this.items.push({
                    id: null, item_type: 'product', listing_id: null, category_id: null, category_name: null,
                    item_name: '', description: '',
                    quantity: '1', unit_id: null, custom_unit: null, estimated_unit_price: null,
                    attribute_values: {}, custom_attributes: [],
                    _attrLoading: false, _attrGroups: [], _listingQuery: '', _listingResults: [],
                    _mode: 'custom',
                    _specsOpen: true,
                    listing_image_url: null,
                });
            },
            removeItem(index) {
                this.items.splice(index, 1);
                if (this.items.length === 0) {
                    this.addCustomItem();
                }
            },

            sourceTypeLabel(item) {
                if (item._mode === 'initial_marketplace') return 'Marketplace Product';
                if (item._mode === 'marketplace' || item.listing_id) return 'Marketplace Product';
                return 'Custom Item';
            },

            // Adds a new blank item and marks it as a marketplace-linked item so the
            // left panel's search box is contextually highlighted for the buyer.
            addMarketplaceItem() {
                this.items.push({
                    id: null, item_type: 'product', listing_id: null, category_id: null, category_name: null,
                    item_name: '', description: '',
                    quantity: '1', unit_id: null, custom_unit: null, estimated_unit_price: null,
                    attribute_values: {}, custom_attributes: [],
                    _attrLoading: false, _attrGroups: [],
                    _listingQuery: '', _listingResults: [],
                    _mode: 'marketplace',
                    _specsOpen: true, // open defaultly
                    listing_image_url: null,
                    _focusSearch: true,
                });
                this.$nextTick(() => {
                    const inputs = document.querySelectorAll('[placeholder*="marketplace"]');
                    if (inputs.length > 0) inputs[inputs.length - 1].focus();
                });
            },

            addCustomAttribute(item) {
                if (!item.custom_attributes) {
                    item.custom_attributes = [];
                }
                item.custom_attributes.push({ name: '', value: '' });
                item._specsOpen = true;
            },
            removeCustomAttribute(item, cIdx) {
                if (item.custom_attributes) {
                    item.custom_attributes.splice(cIdx, 1);
                }
            },
            getCategoryName(catId) {
                return (this.categoryMap && this.categoryMap[catId]) ? this.categoryMap[catId] : '';
            },

            // Returns a human-readable display string for an attribute value.
            // Used by the left-panel Spec Summary to show pre-filled values as text.
            getAttrDisplayValue(item, attr) {
                const val = (item.attribute_values || {})[attr.id];
                if (!val) return null;
                const type = attr.input_type;
                if (type === 'boolean') {
                    if (val.value_boolean === 1 || val.value_boolean === '1') return 'Yes';
                    if (val.value_boolean === 0 || val.value_boolean === '0') return 'No';
                    return null;
                }
                if (type === 'multi_select') {
                    const arr = Array.isArray(val.value_json) ? val.value_json : [];
                    return arr.length > 0 ? arr.join(', ') : (val.custom_value || null);
                }
                if (type === 'select' || type === 'color') {
                    if (val.attribute_value_id && val.attribute_value_id !== '__other__') {
                        const opt = (attr.values || []).find(o => o.id == val.attribute_value_id);
                        return opt ? opt.value : String(val.attribute_value_id);
                    }
                    return val.custom_value || null;
                }
                if (type === 'number') return val.value_number != null ? String(val.value_number) : null;
                if (type === 'date') return val.value_date || null;
                return val.value_text || val.custom_value || null;
            },

            onItemCategoryChange(item) {
                // Only clear existing specification answers when the category is
                // ACTUALLY changing to something different from what they were
                // filled in for — a duplicate/spurious 'change' event (or this
                // firing again for a category that's already set, e.g. right
                // after a marketplace listing prefill) must never silently wipe
                // already-answered specifications.
                if (item._attrCategoryId === item.category_id) {
                    this.fetchItemAttributes(item);
                    return;
                }
                item._attrCategoryId = item.category_id;
                item.attribute_values = {};
                this.fetchItemAttributes(item);
            },
            fetchItemAttributes(item) {
                if (!item.category_id) { item._attrGroups = []; return; }
                item._attrLoading = true;
                // Editing an existing item that already has a value for an
                // attribute since deactivated — keep it visible instead of
                // silently dropping it (item.attribute_values is reset to {}
                // before this is called on a fresh category selection, so
                // this is naturally empty for that case).
                let url = config.categoryAttributesUrl + '/' + item.category_id + '/attributes';
                const keepIds = Object.keys(item.attribute_values || {});
                if (keepIds.length > 0) {
                    url += '?keep_attribute_ids=' + keepIds.join(',');
                }
                fetch(url)
                    .then(r => r.json())
                    .then(data => { item._attrGroups = data.groups || []; })
                    .finally(() => { item._attrLoading = false; });
            },

            searchListingsForItem(item) {
                if (item._listingQuery.trim().length < 2) { item._listingResults = []; return; }
                fetch(config.listingsSearchUrl + '?q=' + encodeURIComponent(item._listingQuery))
                    .then(r => r.json())
                    .then(data => { item._listingResults = data; });
            },
            selectListingForItem(item, listing) {
                item._listingQuery = '';
                item._listingResults = [];
                fetch(config.listingsPrefillUrl + '/' + listing.id + '/prefill')
                    .then(r => r.json())
                    .then(data => {
                        Object.assign(item, data.item);
                        item.quantity = String(data.item.quantity || 1);
                        item.attribute_values = data.attribute_values || {};
                        item.custom_attributes = data.item.custom_attributes || [];
                        item._attrGroups = data.category_attributes ? (data.category_attributes.groups || []) : [];
                        item._attrCategoryId = item.category_id;
                        item._specsOpen = true;
                        item._mode = 'marketplace';
                    });
            },
            clearListingForItem(item) {
                item.listing_id = null;
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

            onCountryChange() {
                this.state = 0; this.city = 0; this.cities = [];
                this.loadStates(this.country, true);
            },
            onStateChange() {
                this.city = 0;
                this.loadCities(this.state, true);
            },
            loadStates(countryId, reset) {
                if (!countryId) { this.states = []; return; }
                fetch(config.statesUrl + '/' + countryId + '/states')
                    .then(r => r.json())
                    .then(data => { this.states = data; });
            },
            loadCities(stateId, reset) {
                if (!stateId) { this.cities = []; return; }
                fetch(config.citiesUrl + '/' + stateId + '/cities')
                    .then(r => r.json())
                    .then(data => { this.cities = data; });
            },

            // Radio accordion visibility helpers
            getVisibilityCode() {
                const vt = this.visibilityTypes.find(t => t.id == this.visibilityTypeId);
                return vt ? vt.code : '';
            },
            selectVisibilityType(vtId) {
                if (this.visibilityTypeId == vtId) return;

                const prevCode = this.getVisibilityCode();
                let hadData = false;
                if (prevCode === 'direct' && this.directSupplier) {
                    hadData = true;
                } else if (prevCode === 'invited' && this.multipleSuppliers.length > 0) {
                    hadData = true;
                } else if (prevCode === 'open_matching' && (this.targetFilter.category_id || (this.targetFilter.location_match_level && this.targetFilter.location_match_level !== 'none'))) {
                    hadData = true;
                }

                if (hadData) {
                    this.switchNotice = 'Your previous supplier selection will be saved but not used for this RFQ.';
                    if (this._switchNoticeTimer) clearTimeout(this._switchNoticeTimer);
                    this._switchNoticeTimer = setTimeout(() => {
                        this.switchNotice = '';
                    }, 7000);
                } else {
                    this.switchNotice = '';
                }

                this.visibilityTypeId = vtId;
                this.showStepError = false;
            },

            // Option 1: Specific Supplier methods
            changeDirectSupplier() {
                this._savedDirectSupplier = this.directSupplier ? { ...this.directSupplier } : null;
                this.directSupplier = null;
                this.directSupplierQuery = '';
                this.directSupplierResults = [];
                this.isSearchingDirectSupplier = false;
                this.$nextTick(() => {
                    const el = document.getElementById('directSupplierInput');
                    if (el) el.focus();
                });
            },
            cancelChangeDirectSupplier() {
                if (this._savedDirectSupplier) {
                    this.directSupplier = { ...this._savedDirectSupplier };
                }
                this.directSupplierQuery = '';
                this.directSupplierResults = [];
                this.isSearchingDirectSupplier = false;
            },
            searchDirectSupplier() {
                if (this.directSupplierQuery.trim().length < 1) {
                    this.directSupplierResults = [];
                    this.isSearchingDirectSupplier = false;
                    return;
                }
                this.isSearchingDirectSupplier = true;
                fetch(config.searchUrl + '?q=' + encodeURIComponent(this.directSupplierQuery))
                    .then(r => r.json())
                    .then(data => {
                        this.directSupplierResults = data || [];
                    })
                    .catch(() => {
                        this.directSupplierResults = [];
                    })
                    .finally(() => {
                        this.isSearchingDirectSupplier = false;
                    });
            },
            selectDirectSupplier(s) {
                this.directSupplier = { id: s.id, name: s.name };
                this._savedDirectSupplier = { id: s.id, name: s.name };
                this.directSupplierQuery = '';
                this.directSupplierResults = [];
                this.isSearchingDirectSupplier = false;
            },
            clearDirectSupplier() {
                this.changeDirectSupplier();
            },

            // Option 2: Selected Suppliers methods
            searchInvitedSuppliers() {
                this.invitedSupplierLimitNotice = '';
                if (this.invitedSupplierQuery.trim().length < 1) {
                    this.invitedSupplierResults = [];
                    this.isSearchingInvitedSuppliers = false;
                    return;
                }
                this.isSearchingInvitedSuppliers = true;
                // Kept in the list even once selected (checkbox reflects state) so the
                // buyer can check off several matches from one query without the list
                // shifting under them or having to re-search after every pick.
                fetch(config.searchUrl + '?q=' + encodeURIComponent(this.invitedSupplierQuery))
                    .then(r => r.json())
                    .then(data => {
                        this.invitedSupplierResults = data || [];
                    })
                    .catch(() => {
                        this.invitedSupplierResults = [];
                    })
                    .finally(() => {
                        this.isSearchingInvitedSuppliers = false;
                    });
            },
            isInvitedSupplierSelected(id) {
                return this.multipleSuppliers.some(item => item.id === id);
            },
            toggleInvitedSupplier(s) {
                const idx = this.multipleSuppliers.findIndex(item => item.id === s.id);
                if (idx !== -1) {
                    this.multipleSuppliers.splice(idx, 1);
                    this.invitedSupplierLimitNotice = '';
                    return;
                }
                const limit = this.maxSuppliers();
                if (limit && this.multipleSuppliers.length >= limit) {
                    this.invitedSupplierLimitNotice = `You can invite up to ${limit} suppliers for this visibility option.`;
                    return;
                }
                this.invitedSupplierLimitNotice = '';
                this.multipleSuppliers.push({ id: s.id, name: s.name });
            },
            removeInvitedSupplier(idx) {
                this.multipleSuppliers.splice(idx, 1);
                this.invitedSupplierLimitNotice = '';
            },
            clearAllInvitedSuppliers() {
                this.multipleSuppliers = [];
                this.invitedSupplierLimitNotice = '';
            },

            getActiveSupplierIds() {
                const code = this.getVisibilityCode();
                if (code === 'direct') {
                    return this.directSupplier ? [this.directSupplier.id] : [];
                }
                if (code === 'invited') {
                    return this.multipleSuppliers.map(s => s.id);
                }
                return [];
            },
            activeSuppliersSummary() {
                const code = this.getVisibilityCode();
                if (code === 'direct') {
                    return this.directSupplier ? this.directSupplier.name : '1 specific supplier (not selected)';
                }
                if (code === 'invited') {
                    const count = this.multipleSuppliers.length;
                    return count > 0 ? (count + ' supplier' + (count === 1 ? '' : 's') + ' invited') : 'No suppliers selected';
                }
                if (code === 'open_matching') {
                    return 'All eligible suppliers (automated matching)';
                }
                return '—';
            },

            // Backward compatibility aliases
            searchSuppliers() { this.searchInvitedSuppliers(); },
            selectSupplier(s) { this.toggleInvitedSupplier(s); },
            removeSupplier(index) { this.removeInvitedSupplier(index); },
        }));
    });
</script>
@endpush
