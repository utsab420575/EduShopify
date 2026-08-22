@php
    $isEdit = $rfq->exists;
    $action = $isEdit ? route('buyer.rfqs.update', $rfq) : route('buyer.rfqs.store');

    $initialItems = $items->isNotEmpty() ? $items->map(fn ($i) => [
        'id' => $i->id, 'item_type' => $i->item_type, 'listing_id' => $i->listing_id ?? null, 'category_id' => $i->category_id,
        'item_name' => $i->item_name, 'description' => $i->description, 'quantity' => (string) $i->quantity,
        'unit_id' => $i->unit_id, 'custom_unit' => $i->custom_unit, 'estimated_unit_price' => $i->estimated_unit_price,
    ])->values() : collect([[
        'id' => null, 'item_type' => 'product', 'listing_id' => null, 'category_id' => null, 'item_name' => '', 'description' => '',
        'quantity' => '1', 'unit_id' => null, 'custom_unit' => null, 'estimated_unit_price' => null,
    ]]);

    $initialSuppliers = $invitedSuppliers->map(fn ($a) => ['id' => $a->id, 'name' => $a->supplierProfile?->display_name ?? $a->display_name])->values();
@endphp

<form
    method="POST"
    action="{{ $action }}"
    x-data="rfqForm({
        items: {{ $initialItems->toJson() }},
        suppliers: {{ $initialSuppliers->toJson() }},
        visibility: '{{ old('visibility_type', $rfq->visibility_type ?? 'global') }}',
        country: {{ (int) old('delivery_country_id', $rfq->delivery_country_id ?? 0) }},
        state: {{ (int) old('delivery_state_id', $rfq->delivery_state_id ?? 0) }},
        city: {{ (int) old('delivery_city_id', $rfq->delivery_city_id ?? 0) }},
        statesUrl: '{{ url('/lookup/countries') }}',
        citiesUrl: '{{ url('/lookup/states') }}',
        searchUrl: '{{ route('buyer.rfqs.supplier-search') }}',
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
                    <div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-500">Item <span x-text="index + 1"></span></span>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-red-500 hover:text-red-700 text-xs">
                                <i class="fa-solid fa-trash"></i> Remove
                            </button>
                        </div>

                        <input type="hidden" :name="'items['+index+'][id]'" :value="item.id ?? ''">
                        <input type="hidden" :name="'items['+index+'][listing_id]'" :value="item.listing_id ?? ''">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                                <select :name="'items['+index+'][item_type]'" x-model="item.item_type" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="product">Product</option>
                                    <option value="service">Service</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                                <select :name="'items['+index+'][category_id]'" x-model="item.category_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Item Name</label>
                            <input type="text" :name="'items['+index+'][item_name]'" x-model="item.item_name" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                        </div>

                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Specifications / Description</label>
                            <textarea :name="'items['+index+'][description]'" x-model="item.description" rows="2" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity</label>
                                <input type="number" step="0.001" min="0.001" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit</label>
                                <select :name="'items['+index+'][unit_id]'" x-model="item.unit_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                    <option value="">Select unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }} @if($unit->symbol)({{ $unit->symbol }})@endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Est. Unit Price</label>
                                <input type="number" step="0.01" min="0" :name="'items['+index+'][estimated_unit_price]'" x-model="item.estimated_unit_price" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
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

            <x-backend.form-card title="Supplier Targeting">
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="visibility_type" value="global" x-model="visibility" style="accent-color:var(--theme-primary)">
                        Global — open to all eligible suppliers
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="visibility_type" value="selected_suppliers" x-model="visibility" style="accent-color:var(--theme-primary)">
                        Selected suppliers only
                    </label>

                    <div x-show="visibility === 'selected_suppliers'" x-cloak class="pt-2">
                        <div class="relative">
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
                <button type="submit" name="action" value="draft" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Save Draft</button>
                <button type="submit" name="action" value="publish" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Save &amp; Publish
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
            visibility: config.visibility,
            country: config.country,
            state: config.state,
            city: config.city,
            states: [],
            cities: [],
            supplierQuery: '',
            supplierResults: [],

            init() {
                if (this.country) this.loadStates(this.country, false);
                if (this.state) this.loadCities(this.state, false);
            },

            addItem() {
                this.items.push({ id: null, item_type: 'product', listing_id: null, category_id: null, item_name: '', description: '', quantity: '1', unit_id: null, custom_unit: null, estimated_unit_price: null });
            },
            removeItem(index) {
                if (this.items.length > 1) this.items.splice(index, 1);
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
