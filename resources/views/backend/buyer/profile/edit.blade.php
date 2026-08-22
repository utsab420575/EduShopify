@extends('backend.layouts.buyer')

@section('title', 'Profile Information')
@section('breadcrumb', 'Buyer Profile / Profile Information')

@section('body')

    <x-backend.page-header title="Profile Information" subtitle="Manage your buyer profile and business details." />

    <form method="POST" action="{{ route('buyer.profile.update') }}" enctype="multipart/form-data"
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

                <x-backend.form-card title="Organisation">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Buyer Type(s)</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($buyerTypes as $buyerType)
                                    <label class="inline-flex items-center gap-1.5 text-xs font-medium border border-gray-200 rounded-full px-3 py-1.5 cursor-pointer">
                                        <input type="checkbox" name="buyer_type_ids[]" value="{{ $buyerType->id }}" @checked($selectedBuyerTypeIds->contains($buyerType->id)) style="accent-color:var(--theme-primary)">
                                        {{ $buyerType->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <x-backend.input name="display_name" label="Display Name" required :value="old('display_name', $profile?->display_name)" />
                        @if($account->isOrganization())
                            <x-backend.input name="organization_name" label="Organisation Name" :value="old('organization_name', $profile?->organization_name)" />
                        @endif
                        <x-backend.textarea name="bio" label="About / Bio" :value="old('bio', $profile?->bio)" />
                        <x-backend.textarea name="procurement_info" label="Procurement Notes" :value="old('procurement_info', $profile?->procurement_info)" hint="Help suppliers understand what you typically procure." />
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Contact">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-backend.input name="contact_person" label="Contact Person" required :value="old('contact_person', $profile?->contact_person)" />
                        <x-backend.input name="position" label="Position" :value="old('position', $profile?->position)" />
                        <x-backend.input type="email" name="email" label="Email" required :value="old('email', $profile?->email)" />
                        <x-backend.input name="phone" label="Phone" :value="old('phone', $profile?->phone)" />
                        <x-backend.input name="website" label="Website" :value="old('website', $profile?->website)" />
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Location">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                            <select name="country_id" x-model.number="country" @change="state=0; city=0; loadStates()" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select country</option>
                                @foreach(\App\Models\Country::active()->get(['id','name']) as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                            <select name="state_id" x-model.number="state" @change="city=0; loadCities()" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select state</option>
                                <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name" :selected="s.id===state"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                            <select name="city_id" x-model.number="city" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                                <option value="0">Select city</option>
                                <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name" :selected="c.id===city"></option></template>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-backend.textarea name="address" label="Address" required :value="old('address', $profile?->address)" />
                    </div>
                    <div class="mt-4">
                        <x-backend.input name="tax_id" label="Tax ID" :value="old('tax_id', $profile?->tax_id)" />
                    </div>
                </x-backend.form-card>
            </div>

            <div class="xl:col-span-4 space-y-6">
                <x-backend.form-card title="Logo">
                    <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'B').'&background=eef2ff&color=4f46e5' }}" class="w-16 h-16 rounded-xl object-contain bg-white border border-gray-100 mb-3" alt="">
                    <input type="file" name="logo" accept="image/*" class="text-sm">
                    <p class="mt-1.5 text-xs text-gray-400">PNG, JPG or WEBP — up to 2MB.</p>
                </x-backend.form-card>
            </div>

            <div class="xl:col-span-12 flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
                <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </div>
    </form>

@endsection
