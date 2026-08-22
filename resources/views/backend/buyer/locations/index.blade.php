@extends('backend.layouts.buyer')

@section('title', 'Locations')
@section('breadcrumb', 'Buyer Profile / Locations')

@section('body')

    <div x-data="{
        showForm: false,
        country: 0, state: 0, city: 0, states: [], cities: [],
        loadStates() { fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d); },
        loadCities() { fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d); },
    }">
        <x-backend.page-header title="Locations" subtitle="Reusable addresses for RFQ delivery and invoicing.">
            <x-slot:actions>
                <button @click="showForm = !showForm" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Location
                </button>
            </x-slot:actions>
        </x-backend.page-header>

        <div x-show="showForm" x-transition x-cloak class="mb-6">
            <x-backend.form-card title="New Location">
                <form method="POST" action="{{ route('buyer.locations.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-backend.select name="location_type" label="Location Type" required :options="[
                            'primary' => 'Head Office', 'registered_office' => 'Registered Office', 'branch' => 'Branch',
                            'warehouse' => 'Warehouse', 'showroom' => 'Showroom', 'billing' => 'Billing Address', 'delivery' => 'Delivery Address',
                        ]" />
                        <x-backend.input name="label" label="Label (optional)" />
                        <x-backend.input name="contact_name" label="Contact Name" />
                        <x-backend.input name="phone" label="Phone" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                            <select name="country_id" x-model.number="country" @change="state=0;city=0;loadStates()" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select country</option>
                                @foreach(\App\Models\Country::active()->get(['id','name']) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                            <select name="state_id" x-model.number="state" @change="city=0;loadCities()" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select state</option>
                                <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                            <select name="city_id" x-model.number="city" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select city</option>
                                <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
                            </select>
                        </div>
                    </div>

                    <x-backend.input name="address_line_1" label="Address Line 1" required />
                    <x-backend.input name="address_line_2" label="Address Line 2" />
                    <x-backend.input name="postal_code" label="Postal Code" />

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_primary" value="1" style="accent-color:var(--theme-primary)"> Set as primary location
                    </label>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showForm = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Save Location</button>
                    </div>
                </form>
            </x-backend.form-card>
        </div>
    </div>

    @if($locations->isEmpty())
        <x-backend.empty-state icon="fa-location-dot" title="No locations yet" description="Add a location to reuse it when creating RFQs." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($locations as $location)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $location->label ?: ucwords(str_replace('_', ' ', $location->location_type)) }}</p>
                            <p class="text-xs text-gray-400">{{ ucwords(str_replace('_', ' ', $location->location_type)) }}</p>
                        </div>
                        @if($location->is_primary)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">Primary</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 mt-2">{{ $location->address_line_1 }}@if($location->address_line_2), {{ $location->address_line_2 }}@endif</p>
                    <p class="text-xs text-gray-400 mt-1">{{ collect([$location->city?->name, $location->state?->name, $location->country?->name])->filter()->implode(', ') }}</p>

                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                        @unless($location->is_primary)
                            <form method="POST" action="{{ route('buyer.locations.primary', $location) }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium" style="color:var(--theme-primary)">Set Primary</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('buyer.locations.destroy', $location) }}" class="ml-auto">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-600">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
