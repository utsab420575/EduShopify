{{-- ── 5. Locations & Service Areas ── --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('locations', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-locations', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i class="fa-solid fa-map-location-dot text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Locations & Service Areas</p>
                <p class="text-xs text-gray-400">{{ $serviceAreas->count() > 0 ? $serviceAreas->count().' area'.($serviceAreas->count() > 1 ? 's' : '') : 'Territories your company covers' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        @foreach($serviceAreas as $area)
            <div class="mb-4 border border-gray-200 rounded-xl overflow-hidden" x-data="{ editing: false }">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                    <div class="flex items-center gap-2 min-w-0">
                        @if($area->is_primary)
                            <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">Primary</span>
                        @endif
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ collect([$area->country?->name, $area->state?->name, $area->city?->name])->filter()->implode(', ') }}
                        </p>
                        <p class="text-xs text-gray-500 hidden sm:block">
                            {{ $area->radius_km ? 'Within '.$area->radius_km.' km' : ($area->city ? 'City-wide' : ($area->state ? 'State-wide' : 'Nationwide')) }}
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
                         country: {{ (int) $area->country_id }},
                         state: {{ (int) ($area->state_id ?? 0) }},
                         states: {{ Js::from($area->area_states->map(fn($s) => ['id' => $s->id, 'name' => $s->name])) }},
                         cities: {{ Js::from($area->area_cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name])) }},
                         loadStates() { fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d); },
                         loadCities() { fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d); },
                     }">
                    <form method="POST" action="{{ route('supplier.company.profile.service-areas.update', $area) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                                    <option value="0">All States</option>
                                    <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name" :selected="s.id === state"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                <select name="city_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                    <option value="0">All Cities</option>
                                    <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name" :selected="c.id === {{ (int) ($area->city_id ?? 0) }}"></option></template>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Delivery Radius (km)</label>
                                <input name="radius_km" value="{{ $area->radius_km }}" type="number" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-300 outline-none">
                            </div>
                            <label class="flex items-center gap-2 mt-6 text-sm text-gray-700 cursor-pointer">
                                <input name="is_primary" value="1" type="checkbox" {{ $area->is_primary ? 'checked' : '' }} class="rounded" style="accent-color:var(--theme-primary)">
                                Primary service territory
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
        @endforeach

        <div class="mt-2" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-800 transition mb-4 cursor-pointer">
                <i class="fa-solid fa-plus"></i> Add Service Area
            </button>

            <div x-show="open" x-transition x-cloak class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-5"
                 x-data="{
                     country: 0, state: 0, states: [], cities: [],
                     loadStates() { fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d); },
                     loadCities() { fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d); },
                 }">
                <form method="POST" action="{{ route('supplier.company.profile.service-areas.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                                <option value="0">All States (Nationwide)</option>
                                <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                            <select name="city_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                <option value="0">All Cities</option>
                                <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Delivery Radius (km, optional)</label>
                            <input name="radius_km" type="number" placeholder="e.g. 50" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>
                        <label class="flex items-center gap-2 mt-6 text-sm text-gray-700 cursor-pointer">
                            <input name="is_primary" value="1" type="checkbox" class="rounded" style="accent-color:var(--theme-primary)">
                            Set as primary service territory
                        </label>
                    </div>

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
