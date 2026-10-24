{{-- ── 5. Locations & Service Areas ── --}}
<div id="sp-section-locations" data-profile-section="Locations &amp; Service Areas" data-filled="{{ ($locations->isNotEmpty() || $serviceAreas->isNotEmpty()) ? 1 : 0 }}" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('locations', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-locations', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-map-location-dot text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Locations & Service Areas</p>
                <p class="text-xs text-gray-400">
                    {{ $locations->count() }} {{ Str::plural('physical location', $locations->count()) }} &bull; {{ $serviceAreas->count() }} {{ Str::plural('service area', $serviceAreas->count()) }}
                </p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6"
         x-data="{
             activeTab: localStorage.getItem('sp-loc-tab') || 'locations',
             setTab(tab) { this.activeTab = tab; localStorage.setItem('sp-loc-tab', tab); }
         }">

        @include('backend.supplier.company.partials._section-errors', ['section' => 'locations'])

        {{-- ── Two Navigation Tabs ── --}}
        <div class="flex border-b border-gray-200 mb-6 gap-2">
            <button type="button" @click="setTab('locations')"
                    :class="activeTab === 'locations' ? 'border-emerald-600 text-emerald-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="flex items-center gap-2 py-3 px-4 border-b-2 text-sm transition-colors cursor-pointer">
                <i class="fa-solid fa-building text-sm"></i>
                <span>Company Physical Locations</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                      :class="activeTab === 'locations' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                    {{ $locations->count() }}
                </span>
            </button>
            <button type="button" @click="setTab('service_areas')"
                    :class="activeTab === 'service_areas' ? 'border-emerald-600 text-emerald-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'"
                    class="flex items-center gap-2 py-3 px-4 border-b-2 text-sm transition-colors cursor-pointer">
                <i class="fa-solid fa-truck-fast text-sm"></i>
                <span>Service & Delivery Areas</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                      :class="activeTab === 'service_areas' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                    {{ $serviceAreas->count() }}
                </span>
            </button>
        </div>

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: COMPANY PHYSICAL LOCATIONS                                  --}}
        {{-- ════════════════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'locations'" x-transition>
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-gray-800">Physical Facilities & Addresses</h4>
                    <p class="text-xs text-gray-400">Head offices, warehouses, showrooms, and branches where your operations are based.</p>
                </div>
            </div>

            @forelse($locations as $location)
                <div class="mb-4 border border-gray-200 rounded-xl overflow-hidden" x-data="{ editing: false }">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                        <div class="flex items-center gap-2 min-w-0">
                            @php
                                $typeLabels = [
                                    'head_office' => 'Head Office',
                                    'registered_office' => 'Registered Office',
                                    'branch' => 'Branch',
                                    'warehouse' => 'Warehouse',
                                    'showroom' => 'Showroom',
                                    'billing' => 'Billing Address',
                                    'delivery' => 'Delivery Address',
                                ];
                                $typeLabel = $typeLabels[$location->location_type] ?? ucwords(str_replace('_', ' ', $location->location_type));
                            @endphp
                            @if($location->is_primary)
                                <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">Primary</span>
                            @endif
                            @if(! $location->is_active)
                                <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">Inactive</span>
                            @endif
                            <span class="flex-shrink-0 text-[11px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $typeLabel }}
                            </span>
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $location->label ?: $location->address_line_1 }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                            @unless($location->is_primary)
                                <form method="POST" action="{{ route('supplier.company.profile.locations.primary', $location) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-emerald-600 font-medium hover:underline cursor-pointer">Set Primary</button>
                                </form>
                            @endunless
                            <button type="button" @click="editing = !editing" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition cursor-pointer">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('supplier.company.profile.locations.destroy', $location) }}" onsubmit="return confirmSwal(this, 'Remove this physical location?', '', 'warning', 'Yes, remove')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Details view when not editing --}}
                    <div x-show="!editing" class="px-4 py-3 bg-white text-xs text-gray-600 space-y-1">
                        <p class="text-sm text-gray-800">
                            <i class="fa-solid fa-location-dot text-indigo-500 mr-1.5"></i>
                            {{ $location->address_line_1 }}@if($location->address_line_2), {{ $location->address_line_2 }}@endif
                        </p>
                        <p class="text-gray-500">
                            {{ collect([$location->city?->name, $location->state?->name, $location->country?->name, $location->postal_code])->filter()->implode(', ') }}
                        </p>
                        @if($location->contact_name || $location->phone)
                            <div class="flex flex-wrap items-center gap-4 pt-1 text-gray-500 border-t border-gray-100 mt-2">
                                @if($location->contact_name)
                                    <span><i class="fa-solid fa-user text-gray-400 mr-1"></i> {{ $location->contact_name }}</span>
                                @endif
                                @if($location->phone)
                                    <span><i class="fa-solid fa-phone text-gray-400 mr-1"></i> {{ $location->phone }}</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Edit Form --}}
                    <div x-show="editing" x-transition x-cloak class="p-4 border-t border-gray-100 bg-gray-50/50"
                         x-data="{
                             country: {{ (int) $location->country_id }},
                             state: {{ (int) ($location->state_id ?? 0) }},
                             states: {{ Js::from($location->loc_states->map(fn($s) => ['id' => $s->id, 'name' => $s->name])) }},
                             cities: {{ Js::from($location->loc_cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name])) }},
                             loadStates() { fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d); },
                             loadCities() { fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d); },
                         }">
                        <form method="POST" action="{{ route('supplier.company.profile.locations.update', $location) }}">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Type <span class="text-red-500">*</span></label>
                                    <select name="location_type" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                        @foreach(['head_office' => 'Head Office', 'registered_office' => 'Registered Office', 'branch' => 'Branch', 'warehouse' => 'Warehouse', 'showroom' => 'Showroom', 'billing' => 'Billing Address', 'delivery' => 'Delivery Address'] as $typeKey => $typeName)
                                            <option value="{{ $typeKey }}" {{ $location->location_type === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Label / Facility Name</label>
                                    <input name="label" value="{{ $location->label }}" type="text" placeholder="e.g. Main Plant, Warehouse B" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Contact Person</label>
                                    <input name="contact_name" value="{{ $location->contact_name }}" type="text" placeholder="Contact person name" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                    <input name="phone" value="{{ $location->phone }}" type="text" placeholder="+123456789" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                                    <select name="country_id" x-model.number="country" @change="state=0;loadStates()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                        <option value="0">Select country</option>
                                        @foreach($countries as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">State / Province</label>
                                    <select name="state_id" x-model.number="state" @change="loadCities()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                        <option value="0">Select State</option>
                                        <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name" :selected="s.id === state"></option></template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                    <select name="city_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                        <option value="0">Select City</option>
                                        <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name" :selected="c.id === {{ (int) ($location->city_id ?? 0) }}"></option></template>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                                    <input name="address_line_1" value="{{ $location->address_line_1 }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 2</label>
                                    <input name="address_line_2" value="{{ $location->address_line_2 }}" type="text" placeholder="Suite, unit, floor, etc." class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 items-center">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Postal Code</label>
                                    <input name="postal_code" value="{{ $location->postal_code }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                                </div>
                                <label class="flex items-center gap-2 mt-4 text-sm text-gray-700 cursor-pointer">
                                    <input name="is_primary" value="1" type="checkbox" {{ $location->is_primary ? 'checked' : '' }} class="rounded" style="accent-color:var(--theme-primary)">
                                    Primary physical location
                                </label>
                                <label class="flex items-center gap-2 mt-4 text-sm text-gray-700 cursor-pointer">
                                    <input name="is_active" value="1" type="checkbox" {{ $location->is_active ? 'checked' : '' }} class="rounded" style="accent-color:var(--theme-primary)">
                                    Active
                                </label>
                            </div>

                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" @click="editing = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                                <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                    <i class="fa-solid fa-floppy-disk"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center border border-dashed border-gray-300 rounded-xl mb-4 bg-gray-50/50">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                        <i class="fa-solid fa-building text-base"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-800">No physical locations added yet</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Add your company's offices, warehouses, or facilities to give buyers confidence in your physical presence.</p>
                </div>
            @endforelse

            {{-- Add Physical Location Form --}}
            <div class="mt-2" x-data="{ openLocationForm: false }">
                <button type="button" @click="openLocationForm = !openLocationForm" class="flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-800 transition mb-4 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add Physical Location
                </button>

                <div x-show="openLocationForm" x-transition x-cloak class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-5 mb-4"
                     x-data="{
                         country: 0, state: 0, states: [], cities: [],
                         loadStates() { fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d); },
                         loadCities() { fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d); },
                     }">
                    <form method="POST" action="{{ route('supplier.company.profile.locations.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Location Type <span class="text-red-500">*</span></label>
                                <select name="location_type" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="head_office">Head Office</option>
                                    <option value="registered_office">Registered Office</option>
                                    <option value="branch">Branch</option>
                                    <option value="warehouse">Warehouse</option>
                                    <option value="showroom">Showroom</option>
                                    <option value="billing">Billing Address</option>
                                    <option value="delivery">Delivery Address</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Label / Facility Name (Optional)</label>
                                <input name="label" type="text" placeholder="e.g. Headquarters, Plant 1" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Contact Name (Optional)</label>
                                <input name="contact_name" type="text" placeholder="Site manager / contact" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone (Optional)</label>
                                <input name="phone" type="text" placeholder="+1 555 123 4567" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                                <select name="country_id" x-model.number="country" @change="state=0;loadStates()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="0">Select country</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">State / Province</label>
                                <select name="state_id" x-model.number="state" @change="loadCities()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="0">Select State</option>
                                    <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                <select name="city_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="0">Select City</option>
                                    <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Street Address <span class="text-red-500">*</span></label>
                                <input name="address_line_1" type="text" placeholder="Building, Street, Area" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none" required>
                                @error('address_line_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 2 (Optional)</label>
                                <input name="address_line_2" type="text" placeholder="Suite, unit, floor, etc." class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 items-center">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Postal Code (Optional)</label>
                                <input name="postal_code" type="text" placeholder="e.g. 10001" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                            </div>
                            <label class="flex items-center gap-2 mt-5 text-sm text-gray-700 cursor-pointer">
                                <input name="is_primary" value="1" type="checkbox" class="rounded" style="accent-color:var(--theme-primary)">
                                Set as primary physical location
                            </label>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="openLocationForm = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                <i class="fa-solid fa-plus"></i> Add Physical Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: SERVICE & DELIVERY AREAS                                    --}}
        {{-- ════════════════════════════════════════════════════════════════════ --}}
        <div x-show="activeTab === 'service_areas'" x-transition>
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-gray-800">Coverage & Delivery Logistics</h4>
                    <p class="text-xs text-gray-400">Territories, cities, and radius zones your company serves for customer delivery and RFQs.</p>
                </div>
            </div>

            @forelse($serviceAreas as $area)
                <div class="mb-4 border border-gray-200 rounded-xl overflow-hidden" x-data="{ editing: false }">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                        <div class="flex items-center gap-2 min-w-0">
                            @if($area->area_level === 'global')
                                <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fa-solid fa-earth-americas mr-1"></i> Worldwide
                                </span>
                            @endif
                            @if($area->is_primary)
                                <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">
                                    {{ $area->area_level === 'global' ? 'Primary' : 'Primary Hub' }}
                                </span>
                            @endif
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                @if($area->area_level === 'global')
                                    Worldwide (Global Delivery - All Countries)
                                @else
                                    {{ collect([$area->country?->name, $area->state?->name, $area->city?->name])->filter()->implode(', ') }}
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 hidden sm:block">
                                @if($area->area_level === 'global')
                                    Global export and supply coverage
                                @else
                                    {{ $area->radius_km ? 'Within '.$area->radius_km.' km' : ($area->city ? 'City-wide' : ($area->state ? 'State-wide' : 'Nationwide')) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                            @unless($area->is_primary)
                                <form method="POST" action="{{ route('supplier.company.profile.service-areas.primary', $area) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-emerald-600 font-medium hover:underline cursor-pointer">Set Primary</button>
                                </form>
                            @endunless
                            <button type="button" @click="editing = !editing" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition cursor-pointer">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('supplier.company.profile.service-areas.destroy', $area) }}" onsubmit="return confirmSwal(this, 'Remove this service area?', '', 'warning', 'Yes, remove')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div x-show="editing" x-transition x-cloak class="p-4 border-t border-gray-100"
                         x-data="{
                             country: '{{ $area->area_level === 'global' ? 'global' : (int) $area->country_id }}',
                             state: {{ (int) ($area->state_id ?? 0) }},
                             states: {{ Js::from($area->area_states->map(fn($s) => ['id' => $s->id, 'name' => $s->name])) }},
                             cities: {{ Js::from($area->area_cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name])) }},
                             loadStates() {
                                 if (this.country === 'global' || this.country === '0' || !this.country) { this.states = []; this.cities = []; return; }
                                 fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d);
                             },
                             loadCities() {
                                 if (this.country === 'global' || !this.state) { this.cities = []; return; }
                                 fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d);
                             },
                         }">
                        <form method="POST" action="{{ route('supplier.company.profile.service-areas.update', $area) }}">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Coverage Scope / Country <span class="text-red-500">*</span></label>
                                    <select name="country_id" x-model="country" @change="state=0;loadStates()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                        <option value="0">Select country</option>
                                        <option value="global" class="font-bold text-indigo-700">🌍 Worldwide (Global - All Countries)</option>
                                        @foreach($countries as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div x-show="country !== 'global'">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">State / Province</label>
                                    <select name="state_id" :disabled="country === 'global'" x-model.number="state" @change="loadCities()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                        <option value="">All States</option>
                                        <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name" :selected="s.id === state"></option></template>
                                    </select>
                                </div>
                                <div x-show="country !== 'global'">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                    <select name="city_id" :disabled="country === 'global'" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                        <option value="">All Cities</option>
                                        <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name" :selected="c.id === {{ (int) ($area->city_id ?? 0) }}"></option></template>
                                    </select>
                                </div>
                            </div>
                            <div x-show="country === 'global'" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-blue-500"></i>
                                <span>Worldwide coverage allows your company to receive RFQs and orders from buyers in any country across the globe.</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3" x-show="country !== 'global'">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Delivery Radius (km)</label>
                                    <input name="radius_km" :disabled="country === 'global'" value="{{ $area->radius_km }}" type="number" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                                </div>
                            </div>
                            <label class="flex items-center gap-2 mt-4 text-sm text-gray-700 cursor-pointer">
                                <input name="is_primary" value="1" type="checkbox" {{ $area->is_primary ? 'checked' : '' }} class="rounded" style="accent-color:var(--theme-primary)">
                                Primary service territory
                            </label>
                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" @click="editing = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                                <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                    <i class="fa-solid fa-floppy-disk"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center border border-dashed border-gray-300 rounded-xl mb-4 bg-gray-50/50">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2">
                        <i class="fa-solid fa-truck-fast text-base"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-800">No service areas defined yet</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Define the regions or radius zones where you offer delivery or services so relevant buyers can find you.</p>
                </div>
            @endforelse

            {{-- Add Service Area Form --}}
            <div class="mt-2" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-800 transition mb-4 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add Service Area
                </button>

                <div x-show="open" x-transition x-cloak class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-5"
                     x-data="{
                         country: '0', state: 0, states: [], cities: [],
                         loadStates() {
                             if (this.country === 'global' || this.country === '0' || !this.country) { this.states = []; this.cities = []; return; }
                             fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d);
                         },
                         loadCities() {
                             if (this.country === 'global' || !this.state) { this.cities = []; return; }
                             fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d);
                         },
                     }">
                    <form method="POST" action="{{ route('supplier.company.profile.service-areas.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Coverage Scope / Country <span class="text-red-500">*</span></label>
                                <select name="country_id" x-model="country" @change="state=0;loadStates()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="0">Select country</option>
                                    <option value="global" class="font-bold text-indigo-700">🌍 Worldwide (Global - All Countries)</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div x-show="country !== 'global'">
                                <label class="block text-xs font-medium text-gray-600 mb-1">State / Province</label>
                                <select name="state_id" :disabled="country === 'global'" x-model.number="state" @change="loadCities()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="">All States (Nationwide)</option>
                                    <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                                 </select>
                            </div>
                            <div x-show="country !== 'global'">
                                <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                <select name="city_id" :disabled="country === 'global'" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="">All Cities</option>
                                    <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
                                </select>
                            </div>
                        </div>

                        <div x-show="country === 'global'" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700 flex items-center gap-2">
                            <i class="fa-solid fa-earth-americas text-blue-600"></i>
                            <span>Worldwide coverage allows your company to be matched with buyers and RFQs from any country across the globe. You can still add specific countries or cities as preferred hubs!</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3" x-show="country !== 'global'">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Delivery Radius (km, optional)</label>
                                <input name="radius_km" :disabled="country === 'global'" type="number" placeholder="e.g. 50" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                            </div>
                        </div>

                        <label class="flex items-center gap-2 mt-4 text-sm text-gray-700 cursor-pointer">
                            <input name="is_primary" value="1" type="checkbox" class="rounded" style="accent-color:var(--theme-primary)">
                            Set as primary service territory
                        </label>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                            <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                <i class="fa-solid fa-plus"></i> Add Service Area
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
