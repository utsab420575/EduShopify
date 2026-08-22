@extends('backend.layouts.supplier')

@section('title', 'Locations & Service Areas')
@section('breadcrumb', 'Business Profile / Locations & Service Areas')

@section('body')

    <x-backend.page-header title="Locations & Service Areas" subtitle="Define physical business locations and geographical areas where your company provides products and services." />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Add Service Area Form --}}
        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Add Service Area">
                <form method="POST" action="{{ route('supplier.company.service-areas.store') }}"
                      x-data="{
                          country: 0, state: 0, city: 0,
                          states: [], cities: [],
                          loadStates() {
                              if (!this.country) return;
                              fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r => r.json()).then(d => this.states = d);
                          },
                          loadCities() {
                              if (!this.state) return;
                              fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r => r.json()).then(d => this.cities = d);
                          },
                      }" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                        <select name="country_id" required x-model.number="country" @change="state=0; city=0; loadStates()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select country</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">State / Province (optional)</label>
                        <select name="state_id" x-model.number="state" @change="city=0; loadCities()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="0">All States (Nationwide)</option>
                            <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">City (optional)</label>
                        <select name="city_id" x-model.number="city" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="0">All Cities</option>
                            <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
                        </select>
                    </div>

                    <x-backend.input type="number" name="radius_km" label="Delivery Radius (km, optional)" placeholder="e.g. 50" />

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_primary" id="is_primary" value="1" style="accent-color:var(--theme-primary)">
                        <label for="is_primary" class="text-sm font-medium text-gray-700">Primary service territory</label>
                    </div>

                    <button type="submit" class="btn-primary w-full text-sm font-medium py-2.5 rounded-lg flex items-center justify-center gap-2 mt-4">
                        <i class="fa-solid fa-plus"></i> Add Service Area
                    </button>
                </form>
            </x-backend.form-card>
        </div>

        {{-- Existing service areas list --}}
        <div class="xl:col-span-8 space-y-6">
            <x-backend.form-card title="Covered Service Areas">
                @if($serviceAreas->isEmpty())
                    <x-backend.empty-state icon="fa-map-location-dot" title="No service areas added" description="Add territories and locations where your company delivers products or services." />
                @else
                    <div class="overflow-x-auto -mx-5 -mb-5">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 bg-gray-50 border-y border-gray-100">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Territory</th>
                                    <th class="px-3 py-3 font-semibold">Scope</th>
                                    <th class="px-3 py-3 font-semibold">Primary</th>
                                    <th class="px-3 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($serviceAreas as $area)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 font-medium text-gray-900">
                                            {{ $area->country?->name }}
                                            @if($area->state) &middot; {{ $area->state->name }} @endif
                                            @if($area->city) &middot; {{ $area->city->name }} @endif
                                        </td>
                                        <td class="px-3 py-3 text-xs text-gray-600">
                                            @if($area->radius_km)
                                                Within {{ $area->radius_km }} km
                                            @elseif($area->city_id)
                                                City-wide
                                            @elseif($area->state_id)
                                                State-wide
                                            @else
                                                Nationwide
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            @if($area->is_primary)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-100 text-indigo-800">Primary</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $area->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $area->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <form method="POST" action="{{ route('supplier.company.service-areas.destroy', $area) }}" onsubmit="return confirm('Remove this service area?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-backend.form-card>
        </div>

    </div>

@endsection
