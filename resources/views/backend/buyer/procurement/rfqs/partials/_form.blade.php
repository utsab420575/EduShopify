@php
    $isEdit = $rfq->exists;
    $action = $isEdit ? route('buyer.rfqs.update', $rfq) : route('buyer.rfqs.store');
    $itemAttributeValues = $itemAttributeValues ?? [];
    $targetFilter = $targetFilter ?? null;

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
    })"
    x-init="init()"
>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="Basic Information">
                <div class="space-y-4">
                    <x-backend.input name="title" label="RFQ Title" required :value="old('title', $rfq->title)" placeholder="e.g. 500 units of A4 exercise books" />
                    <x-backend.textarea name="description" label="Description" :value="old('description', $rfq->description)" hint="Explain what you need — specifications, use case, quality requirements." />
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <x-backend.select name="currency_code" label="Currency" placeholder="Select currency">
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->code }}" @selected(old('currency_code', $rfq->currency_code) === $currency->code)>{{ $currency->code }} — {{ $currency->name }}</option>
                            @endforeach
                        </x-backend.select>
                        <x-backend.input type="number" name="budget_min" label="Budget Min" :value="old('budget_min', $rfq->budget_min)" />
                        <x-backend.input type="number" name="budget_max" label="Budget Max" :value="old('budget_max', $rfq->budget_max)" />
                    </div>
                </div>
            </x-backend.form-card>

            <x-backend.form-card title="RFQ Items" description="Add every product or service you want suppliers to quote on.">
                <template x-for="(item, index) in items" :key="index">
                    @include('backend.buyer.procurement.rfqs.partials._item')
                </template>

                <button type="button" @click="addItem()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Item
                </button>
            </x-backend.form-card>

            <x-backend.form-card title="Delivery">
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
                <div class="mt-4">
                    <x-backend.textarea name="delivery_address" label="Delivery Address" :value="old('delivery_address', $rfq->delivery_address)" rows="2" />
                </div>
            </x-backend.form-card>

        </div>

        <div class="xl:col-span-4 space-y-6">

            <x-backend.form-card title="Deadlines &amp; Options">
                <div class="space-y-4">
                    <x-backend.input type="datetime-local" name="quotation_deadline" label="Quotation Deadline" required :value="old('quotation_deadline', optional($rfq->quotation_deadline)->format('Y-m-d\TH:i'))" />
                    <x-backend.input type="datetime-local" name="qna_deadline" label="Q&amp;A Deadline" :value="old('qna_deadline', optional($rfq->qna_deadline)->format('Y-m-d\TH:i'))" />
                    <x-backend.input type="date" name="expected_delivery_date" label="Expected Delivery Date" :value="old('expected_delivery_date', optional($rfq->expected_delivery_date)->format('Y-m-d'))" />

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="allow_partial_quotation" value="0">
                        <input type="checkbox" name="allow_partial_quotation" value="1" @checked(old('allow_partial_quotation', $rfq->allow_partial_quotation ?? true)) class="rounded border-gray-300" style="accent-color:var(--theme-primary)">
                        Allow partial quotations
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="allow_alternative_products" value="0">
                        <input type="checkbox" name="allow_alternative_products" value="1" @checked(old('allow_alternative_products', $rfq->allow_alternative_products ?? true)) class="rounded border-gray-300" style="accent-color:var(--theme-primary)">
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
                            <p class="text-[11px] text-gray-400 mt-1">This is about where matched suppliers are based — independent of your delivery address below.</p>
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

                        <div class="relative" x-show="canAddSupplier()">
                            <input type="text" x-model="supplierQuery" @input.debounce.400ms="searchSuppliers()" placeholder="Search suppliers by name..."
                                   class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                            <div x-show="supplierResults.length > 0" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                                <template x-for="s in supplierResults" :key="s.id">
                                    <button type="button" @click="selectSupplier(s)" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50" x-text="s.name"></button>
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
                    </div>
                </div>
            </x-backend.form-card>

        </div>

        <div class="xl:col-span-12 flex flex-wrap items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ $isEdit ? route('buyer.rfqs.show', $rfq) : route('buyer.rfqs.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            @if($isEdit && $rfq->status !== 'draft')
                <p class="text-xs text-gray-400 mr-auto">This RFQ is already published — saving will record a new version and notify suppliers with a live quotation if the changes are material.</p>
                <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            @else
                <p class="text-xs text-gray-400 mr-auto">Saved as a draft first — review everything and publish from the RFQ page when you're ready.</p>
                <button type="submit" name="action" value="draft" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save Draft
                </button>
            @endif
        </div>
    </div>
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

            addItem() {
                this.items.push({
                    id: null, item_type: 'product', listing_id: null, category_id: null, item_name: '', description: '',
                    quantity: '1', unit_id: null, custom_unit: null, estimated_unit_price: null,
                    attribute_values: {}, _attrLoading: false, _attrGroups: [], _listingQuery: '', _listingResults: [],
                });
            },
            removeItem(index) {
                if (this.items.length > 1) this.items.splice(index, 1);
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
                fetch(config.categoryAttributesUrl + '/' + item.category_id + '/attributes')
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
                if (this.supplierQuery.trim().length < 2) { this.supplierResults = []; return; }
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
