@extends('backend.layouts.supplier')

@section('title', 'Company Information')
@section('breadcrumb', 'Business Profile / Company Information')

@section('body')

    <x-backend.page-header title="Company Information" subtitle="Manage your public supplier profile, branding, and contact details." />

    <form method="POST" action="{{ route('supplier.company.profile.update') }}" enctype="multipart/form-data"
          x-data="{
              country: {{ (int) old('country_id', $profile?->country_id ?? 0) }},
              state: {{ (int) old('state_id', $profile?->state_id ?? 0) }},
              city: {{ (int) old('city_id', $profile?->city_id ?? 0) }},
              states: [], cities: [],
              init() {
                  if (this.country) this.loadStates();
                  if (this.state) this.loadCities();
              },
              loadStates() {
                  fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r => r.json()).then(d => this.states = d);
              },
              loadCities() {
                  fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r => r.json()).then(d => this.cities = d);
              },
          }" x-init="init()">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-8 space-y-6">

                <x-backend.form-card title="Company Details">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-backend.input name="display_name" label="Display / Trading Name" required :value="old('display_name', $profile?->display_name ?? $account->display_name)" />
                            <x-backend.input name="legal_name" label="Legal Business Name" :value="old('legal_name', $profile?->legal_name)" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-backend.input name="company_type" label="Company Type (e.g. LLC, Pvt Ltd)" :value="old('company_type', $profile?->company_type)" />
                            <x-backend.input type="number" name="founded_year" label="Founded Year" :value="old('founded_year', $profile?->founded_year)" />
                            <x-backend.input type="number" name="employees" label="Number of Employees" :value="old('employees', $profile?->employees)" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier Business Type(s)</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($supplierTypes as $type)
                                    <label class="inline-flex items-center gap-1.5 text-xs font-medium border border-gray-200 rounded-full px-3 py-1.5 cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox" name="supplier_type_ids[]" value="{{ $type->id }}" @checked($selectedTypeIds->contains($type->id)) style="accent-color:var(--theme-primary)">
                                        {{ $type->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <x-backend.textarea name="description" label="Company Overview / Description" :value="old('description', $profile?->description)" hint="Introduce your business to educational buyers." />
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Contact Information">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-backend.input name="contact_person" label="Primary Contact Person" required :value="old('contact_person', $profile?->contact_person)" />
                        <x-backend.input type="email" name="contact_email" label="Contact Email" required :value="old('contact_email', $profile?->contact_email)" />
                        <x-backend.input name="contact_phone" label="Contact Phone" :value="old('contact_phone', $profile?->contact_phone)" />
                        <x-backend.input name="whatsapp" label="WhatsApp Number" :value="old('whatsapp', $profile?->whatsapp)" />
                        <x-backend.input type="email" name="support_email" label="Support Email" :value="old('support_email', $profile?->support_email)" />
                        <x-backend.input name="website" label="Website URL" :value="old('website', $profile?->website)" placeholder="https://" />
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Registered Address">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                            <select name="country_id" x-model.number="country" @change="state=0; city=0; loadStates()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="0">Select country</option>
                                @foreach(\App\Models\Country::where('is_active', true)->get(['id','name']) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">State / Province</label>
                            <select name="state_id" x-model.number="state" @change="city=0; loadCities()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="0">Select state</option>
                                <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name" :selected="s.id===state"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                            <select name="city_id" x-model.number="city" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="0">Select city</option>
                                <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name" :selected="c.id===city"></option></template>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-backend.textarea name="address" label="Street Address" required :value="old('address', $profile?->address)" />
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-4 space-y-6">

                <x-backend.form-card title="Company Logo">
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=0D9488&color=fff' }}" class="w-16 h-16 rounded-xl object-contain bg-white border border-gray-200" alt="">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-gray-700 truncate">Logo</p>
                            <p class="text-[11px] text-gray-400">Square format recommended</p>
                        </div>
                    </div>
                    <input type="file" name="logo" accept="image/*" class="text-xs text-gray-500">
                </x-backend.form-card>

                <x-backend.form-card title="Banner Image">
                    @if($profile?->banner)
                        <img src="{{ asset('storage/'.$profile->banner) }}" class="w-full h-24 rounded-lg object-cover bg-gray-100 mb-3" alt="">
                    @endif
                    <input type="file" name="banner" accept="image/*" class="text-xs text-gray-500">
                    <p class="mt-1 text-[11px] text-gray-400">1200x300 recommended, up to 4MB.</p>
                </x-backend.form-card>

                <x-backend.form-card title="Profile Photo / Representative">
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ $profile?->profile_photo ? asset('storage/'.$profile->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0D9488&color=fff' }}" class="w-12 h-12 rounded-full object-cover bg-gray-100" alt="">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-gray-700 truncate">Contact Person Photo</p>
                            <p class="text-[11px] text-gray-400">Up to 2MB</p>
                        </div>
                    </div>
                    <input type="file" name="profile_photo" accept="image/*" class="text-xs text-gray-500">
                </x-backend.form-card>

                {{-- Performance Metrics (Read Only) --}}
                <x-backend.form-card title="Performance Metrics">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">Rating</span>
                            <span class="font-semibold text-gray-800"><i class="fa-solid fa-star text-amber-400 mr-1"></i>{{ number_format($profile?->rating ?? 0, 1) }} ({{ $profile?->reviews_count ?? 0 }} reviews)</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">Response Rate</span>
                            <span class="font-semibold text-gray-800">{{ number_format($profile?->quotation_response_rate ?? 0, 0) }}%</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500">Avg. Response Time</span>
                            <span class="font-semibold text-gray-800">{{ $profile?->average_response_minutes ? round($profile->average_response_minutes / 60, 1) . ' hrs' : 'N/A' }}</span>
                        </div>
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-12 flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
                <button type="submit" class="btn-primary text-sm font-medium px-5 py-2.5 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </div>
    </form>

@endsection
