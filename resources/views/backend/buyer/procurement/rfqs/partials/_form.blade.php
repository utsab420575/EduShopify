@php
    $isEdit = $rfq->exists;
    $action = $isEdit ? route('buyer.rfqs.update', $rfq) : route('buyer.rfqs.store');
    $itemAttributeValues = $itemAttributeValues ?? [];
    $targetFilter = $targetFilter ?? null;
    $isEditingPublished = $isEdit && $rfq->status !== 'draft';

    $initialItems = $items->isNotEmpty() ? $items->values()->map(fn ($i, $idx) => [
        'id' => $i->id, 'item_type' => $i->item_type, 'listing_id' => $i->listing_id ?? null, 'category_id' => $i->category_id,
        'item_name' => $i->item_name, 'description' => $i->description, 'quantity' => (string) $i->quantity,
        'unit_id' => $i->unit_id, 'custom_unit' => $i->custom_unit, 'estimated_unit_price' => $i->estimated_unit_price,
        'attribute_values' => (object) ($itemAttributeValues[$idx] ?? []),
        '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [],
    ])->values() : collect([[
        'id' => null, 'item_type' => 'product', 'listing_id' => null, 'category_id' => null, 'item_name' => '', 'description' => '',
        'quantity' => '1', 'unit_id' => null, 'custom_unit' => null, 'estimated_unit_price' => null,
        'attribute_values' => (object) ($itemAttributeValues[0] ?? []),
        '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [],
    ]]);

    $initialSuppliers = $invitedSuppliers->map(fn ($a) => ['id' => $a->id, 'name' => $a->supplierProfile?->display_name ?? $a->display_name])->values();
    $defaultVtId = $rfq->visibility_type_id ?? (isset($visibilityTypes) && $visibilityTypes->first() ? $visibilityTypes->first()->id : 0);

    $buyerVisibilityCodes = ['direct', 'invited', 'open_matching'];
    $buyerVisibilityLabels = [
        'direct' => ['label' => 'This Supplier', 'desc' => 'Send this RFQ to one specific supplier only.'],
        'invited' => ['label' => 'Selected Suppliers', 'desc' => 'Invite a shortlist of suppliers you choose.'],
        'open_matching' => ['label' => 'Open to Eligible Suppliers', 'desc' => 'Automatically matched to suppliers who serve the selected category and location.'],
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
        $initialStep = match (true) {
            $items->isEmpty() => 1,
            empty(old('title', $rfq->title)) => 2,
            ! old('quotation_deadline', $rfq->quotation_deadline) || ($rfq->isInvited() && $invitedSuppliers->isEmpty()) => 3,
            default => 4,
        };
    }
@endphp

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
        quotationDeadline: {{ json_encode(old('quotation_deadline', optional($rfq->quotation_deadline)->format('Y-m-d\TH:i') ?? '')) }},
        qnaDeadline: {{ json_encode(old('qna_deadline', optional($rfq->qna_deadline)->format('Y-m-d\TH:i') ?? '')) }},
        expectedDeliveryDate: {{ json_encode(old('expected_delivery_date', optional($rfq->expected_delivery_date)->format('Y-m-d') ?? '')) }},
        allowPartialQuotation: {{ old('allow_partial_quotation', $rfq->allow_partial_quotation ?? true) ? 'true' : 'false' }},
        allowAlternativeProducts: {{ old('allow_alternative_products', $rfq->allow_alternative_products ?? true) ? 'true' : 'false' }},
        additionalAddresses: {{ $initialAdditionalAddresses->toJson() }},
        countryOptions: {{ \App\Models\Country::active()->get(['id', 'name'])->toJson() }},
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

    {{-- Connected-circle progress stepper --}}
    <div class="bg-white rounded-xl border border-gray-200 px-6 py-5 mb-4">
        <div class="flex items-start">
            @foreach([1 => 'Items', 2 => 'Details & Delivery', 3 => 'Deadlines & Suppliers', 4 => 'Review & Submit'] as $num => $label)
                <button type="button" @click="currentStep = {{ $num }}" class="flex flex-col items-center gap-1.5 shrink-0 {{ $num < 4 ? 'w-1/4' : '' }}">
                    <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-colors"
                          :class="stepValid({{ $num }}) ? 'bg-emerald-500 border-emerald-500 text-white' : (currentStep === {{ $num }} ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-300 text-gray-400')">
                        <i class="fa-solid fa-check text-xs" x-show="stepValid({{ $num }})" x-cloak></i>
                        <span x-show="!stepValid({{ $num }})">{{ $num }}</span>
                    </span>
                    <span class="text-xs font-medium text-center whitespace-nowrap" :class="currentStep === {{ $num }} ? 'text-indigo-600' : 'text-gray-500'">{{ $label }}</span>
                </button>
                @if($num < 4)
                    <div class="flex-1 h-0.5 mt-4 mx-1" :class="stepValid({{ $num }}) ? 'bg-emerald-400' : 'bg-gray-200'"></div>
                @endif
            @endforeach
        </div>

        <div class="flex items-center justify-between mt-3">
            <p x-show="saveError" x-cloak class="text-xs text-red-600" x-text="saveError"></p>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-50 border border-gray-200 ml-auto" x-show="rfqId || isSaving">
                <span class="w-1.5 h-1.5 rounded-full" :class="isSaving ? 'bg-amber-400 animate-pulse' : 'bg-emerald-500'"></span>
                <span x-text="isSaving ? 'Saving…' : 'Draft saved'"></span>
            </span>
        </div>
    </div>

    {{-- ═══════ STEP 1 — Items ═══════ --}}
    <div x-show="currentStep === 1" x-cloak class="flex flex-col" style="height:calc(100vh - 360px);min-height:420px;">
        <div class="flex-1 overflow-y-auto pr-1 space-y-4">
            <x-backend.form-card title="RFQ Items" description="Add every product or service you want suppliers to quote on.">
                <template x-for="(item, index) in items" :key="index">
                    @include('backend.buyer.procurement.rfqs.partials._item')
                </template>

                <button type="button" @click="addItem()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Item
                </button>

                <p x-show="showStepError && !stepValid(1)" x-cloak class="text-xs text-red-600 mt-3">Every item needs a name and a quantity greater than zero.</p>
            </x-backend.form-card>
        </div>

        <div class="pt-3 mt-2 border-t border-gray-100 flex justify-end shrink-0">
            <button type="button" @click="goNext(1)" :disabled="isSaving" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60">
                <span x-show="!isSaving">Next: Details &amp; Delivery <i class="fa-solid fa-arrow-right"></i></span>
                <span x-show="isSaving" x-cloak>Saving…</span>
            </button>
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
            <button type="button" @click="currentStep = 1" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </button>
            <button type="button" @click="goNext(2)" :disabled="isSaving" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60">
                <span x-show="!isSaving">Next: Deadlines &amp; Suppliers <i class="fa-solid fa-arrow-right"></i></span>
                <span x-show="isSaving" x-cloak>Saving…</span>
            </button>
        </div>
    </div>

    {{-- ═══════ STEP 3 — Deadlines & Suppliers ═══════ --}}
    <div x-show="currentStep === 3" x-cloak class="flex flex-col" style="height:calc(100vh - 360px);min-height:420px;">
        <div class="flex-1 overflow-y-auto pr-1">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-backend.form-card title="Deadlines & Options">
                    <div class="space-y-4">
                        <div>
                            <x-backend.input type="datetime-local" name="quotation_deadline" label="Quotation Deadline" required x-model="quotationDeadline" />
                            <p x-show="showStepError && !quotationDeadline" x-cloak class="text-xs text-red-600 mt-1">Set a quotation deadline before continuing.</p>
                        </div>
                        <x-backend.input type="datetime-local" name="qna_deadline" label="Q&A Deadline" x-model="qnaDeadline" />
                        <x-backend.input type="date" name="expected_delivery_date" label="Expected Delivery Date" x-model="expectedDeliveryDate" />

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

                <x-backend.form-card title="Supplier Targeting & Visibility">
                    <div class="space-y-3">
                        @foreach($visibilityTypes->whereIn('code', $buyerVisibilityCodes) as $vt)
                            <label class="flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer"
                                   :class="visibilityTypeId == {{ $vt->id }} ? 'border-indigo-500 bg-indigo-50/40 ring-1 ring-indigo-500' : 'border-gray-200 hover:border-gray-300 bg-white'">
                                <input type="radio" name="visibility_type_id" value="{{ $vt->id }}" x-model="visibilityTypeId"
                                       class="mt-0.5" style="accent-color:var(--theme-primary)">
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-semibold text-gray-900">{{ $buyerVisibilityLabels[$vt->code]['label'] ?? $vt->name }}</span>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $buyerVisibilityLabels[$vt->code]['desc'] ?? $vt->description }}</p>
                                </div>
                            </label>
                        @endforeach

                        <div x-show="isOpenMatchingMode()" x-cloak class="pt-3 border-t border-gray-100 space-y-3">
                            <p class="text-xs font-semibold text-gray-700">Match Suppliers By</p>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                                <select name="target_filter[category_id]" x-model="targetFilter.category_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    <option value="">Any category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1">Optional but recommended — narrows the RFQ to suppliers who list this category as something they supply.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Supplier Location</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['none' => 'Anywhere', 'country' => 'Country', 'state' => 'State', 'city' => 'City'] as $level => $levelLabel)
                                        <label class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border cursor-pointer"
                                               :class="targetFilter.location_match_level === '{{ $level }}' ? 'border-indigo-500 bg-indigo-50/50 text-indigo-700 font-semibold' : 'border-gray-200 text-gray-600'">
                                            <input type="radio" name="target_filter[location_match_level]" value="{{ $level }}" x-model="targetFilter.location_match_level" class="sr-only">
                                            {{ $levelLabel }}
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">This is about where matched suppliers are based — independent of your delivery address in step 2.</p>
                                <p x-show="showStepError && targetFilter.location_match_level !== 'none' && !targetFilterLocationSelected()" x-cloak class="text-xs text-red-600 mt-1">
                                    Select a <span x-text="targetFilter.location_match_level"></span>, or switch back to "Anywhere".
                                </p>
                            </div>
                            <div x-show="targetFilter.location_match_level !== 'none'" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <select name="target_filter[country_id]" x-model.number="targetFilter.country_id" @change="onTargetCountryChange()" class="focus-accent text-xs rounded-lg border border-gray-300 px-2 py-2 bg-white">
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
                        </div>

                        <div x-show="isInvitedMode()" x-cloak class="pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-gray-700">Invited Suppliers <span class="text-red-500">*</span></label>
                                <span class="text-[11px] text-gray-400" x-show="maxSuppliers()" x-text="'Max limit: ' + maxSuppliers() + ' supplier(s)'"></span>
                            </div>

                            <div class="relative" x-show="canAddSupplier()" x-data="{ focused: false }">
                                <input type="text" x-model="supplierQuery" @input.debounce.300ms="searchSuppliers()"
                                       @focus="focused = true" @blur="setTimeout(() => focused = false, 150)"
                                       placeholder="Type a supplier name to search..."
                                       class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                                <div x-show="focused" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                    <template x-if="supplierQuery.trim().length === 0">
                                        <p class="px-3 py-2 text-xs text-gray-400">Type to search suppliers…</p>
                                    </template>
                                    <template x-if="supplierQuery.trim().length > 0 && supplierResults.length === 0">
                                        <p class="px-3 py-2 text-xs text-gray-400">No matching suppliers.</p>
                                    </template>
                                    <template x-for="s in supplierResults" :key="s.id">
                                        <button type="button" @mousedown.prevent="selectSupplier(s)" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50" x-text="s.name"></button>
                                    </template>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mt-3">
                                <template x-for="(s, idx) in suppliers" :key="s.id">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium pl-2.5 pr-1.5 py-1 rounded-full" style="background:var(--theme-primary-soft);color:var(--theme-primary)">
                                        <input type="hidden" name="selected_supplier_ids[]" :value="s.id">
                                        <span x-text="s.name"></span>
                                        <button type="button" @click="removeSupplier(idx)"><i class="fa-solid fa-xmark"></i></button>
                                    </span>
                                </template>
                            </div>
                            <p x-show="showStepError && suppliers.length === 0" x-cloak class="text-xs text-red-600 mt-2">Select at least one supplier to invite.</p>
                        </div>
                    </div>
                </x-backend.form-card>
            </div>
        </div>

        <div class="pt-3 mt-2 border-t border-gray-100 flex justify-between shrink-0">
            <button type="button" @click="currentStep = 2" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </button>
            <button type="button" @click="goNext(3)" :disabled="isSaving" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2 disabled:opacity-60">
                <span x-show="!isSaving">Next: Review &amp; Submit <i class="fa-solid fa-arrow-right"></i></span>
                <span x-show="isSaving" x-cloak>Saving…</span>
            </button>
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
                <button type="button" @click="currentStep = 3" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
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
            states: [],
            cities: [],
            supplierQuery: '',
            supplierResults: [],

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
                    const suppliersOk = this.isOpenMatchingMode() || this.suppliers.length > 0;
                    const locationOk = !this.isOpenMatchingMode() || this.targetFilterLocationSelected();
                    return !!this.quotationDeadline && suppliersOk && locationOk;
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

            buildPayload() {
                return {
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
                    selected_supplier_ids: this.suppliers.map(s => s.id),
                    target_filter: this.targetFilter,
                    additional_addresses: this.additionalAddresses.map(a => ({
                        country_id: a.country_id || null, state_id: a.state_id || null, city_id: a.city_id || null, address: a.address,
                    })),
                    items: this.items.map(i => ({
                        id: i.id, item_type: i.item_type, listing_id: i.listing_id, category_id: i.category_id,
                        item_name: i.item_name, description: i.description, quantity: i.quantity,
                        unit_id: i.unit_id, custom_unit: i.custom_unit, estimated_unit_price: i.estimated_unit_price,
                        attribute_values: i.attribute_values,
                    })),
                };
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
                        this.saveError = 'Could not save your progress — please check the highlighted fields.';
                        return false;
                    }
                    const data = await res.json();
                    if (!this.rfqId && data.id) {
                        this.rfqId = data.id;
                    }
                    return true;
                } catch (e) {
                    this.saveError = 'Could not save your progress — check your connection and try again.';
                    return false;
                } finally {
                    this.isSaving = false;
                }
            },
            async goNext(n) {
                if (n < 4 && !this.stepValid(n)) {
                    this.showStepError = true;
                    return;
                }
                this.showStepError = false;
                const saved = await this.autosave();
                if (!saved) return;
                this.currentStep = n + 1;
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
                this.items.forEach(item => { if (item.category_id) this.fetchItemAttributes(item); });
                this.additionalAddresses.forEach(addr => this.initAddressRow(addr));
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
                this.items.push({
                    id: null, item_type: 'product', listing_id: null, category_id: null, item_name: '', description: '',
                    quantity: '1', unit_id: null, custom_unit: null, estimated_unit_price: null,
                    attribute_values: {}, _attrLoading: false, _attrGroups: [], _listingQuery: '', _listingResults: [],
                });
            },
            removeItem(index) {
                this.items.splice(index, 1);
                if (this.items.length === 0) {
                    this.addItem();
                }
            },

            sourceTypeLabel(item) {
                return item.listing_id ? 'Marketplace Product' : 'Custom Requirement';
            },

            onItemCategoryChange(item) {
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
                        item.quantity = String(data.item.quantity);
                        item.attribute_values = data.attribute_values || {};
                        item._attrGroups = data.category_attributes ? (data.category_attributes.groups || []) : [];
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

            searchSuppliers() {
                if (this.supplierQuery.trim().length < 1) { this.supplierResults = []; return; }
                const selectedIds = this.suppliers.map(s => s.id);
                fetch(config.searchUrl + '?q=' + encodeURIComponent(this.supplierQuery))
                    .then(r => r.json())
                    .then(data => { this.supplierResults = data.filter(s => !selectedIds.includes(s.id)); });
            },
            selectSupplier(s) {
                this.suppliers.push(s);
                this.supplierQuery = '';
                this.supplierResults = [];
            },
            removeSupplier(index) {
                this.suppliers.splice(index, 1);
            },
        }));
    });
</script>
@endpush
