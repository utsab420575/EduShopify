{{-- ── 2. Contact Information ── --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden"
     x-data="{
         open: {{ $spAccOpen('contact', false) }},
         country: {{ (int) old('country_id', $profile?->country_id ?? 0) }},
         state: {{ (int) old('state_id', $profile?->state_id ?? 0) }},
         states: {{ Js::from($states->map(fn($s) => ['id' => $s->id, 'name' => $s->name])) }},
         cities: {{ Js::from($cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name])) }},
         loadStates() { fetch('{{ url('/lookup/countries') }}/' + this.country + '/states').then(r=>r.json()).then(d=>this.states=d); },
         loadCities() { fetch('{{ url('/lookup/states') }}/' + this.state + '/cities').then(r=>r.json()).then(d=>this.cities=d); },
     }"
     x-init="$watch('open', v => localStorage.setItem('sp-acc-contact', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                <i class="fa-solid fa-address-card text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Contact Information</p>
                <p class="text-xs text-gray-400">{{ $profile?->contact_person ?: 'Person, email, phone, registered address' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">
        <form method="POST" action="{{ route('supplier.company.profile.contact.update') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                    <input name="contact_person" value="{{ old('contact_person', $profile?->contact_person) }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                    @error('contact_person') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Email <span class="text-red-500">*</span></label>
                    <input name="contact_email" value="{{ old('contact_email', $profile?->contact_email) }}" type="email" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                    @error('contact_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Phone</label>
                    <input name="contact_phone" value="{{ old('contact_phone', $profile?->contact_phone) }}" type="tel" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp Number</label>
                    <input name="whatsapp" value="{{ old('whatsapp', $profile?->whatsapp) }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Support Email</label>
                    <input name="support_email" value="{{ old('support_email', $profile?->support_email) }}" type="email" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Website URL</label>
                    <input name="website" value="{{ old('website', $profile?->website) }}" type="url" placeholder="https://..." class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                    @error('website') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                    <select name="country_id" x-model.number="country" @change="state=0;loadStates()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-sky-300 outline-none">
                        <option value="0">Select country</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('country_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">State / Province</label>
                    <select name="state_id" x-model.number="state" @change="loadCities()" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-sky-300 outline-none">
                        <option value="0">Select state</option>
                        <template x-for="s in states" :key="s.id"><option :value="s.id" x-text="s.name" :selected="s.id === state"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                    <select name="city_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-sky-300 outline-none">
                        <option value="0">Select city</option>
                        <template x-for="c in cities" :key="c.id"><option :value="c.id" x-text="c.name" :selected="c.id === {{ (int) old('city_id', $profile?->city_id ?? 0) }}"></option></template>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Street Address <span class="text-red-500">*</span></label>
                <textarea name="address" rows="2" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition resize-none">{{ old('address', $profile?->address) }}</textarea>
                @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i> Save Contact
                </button>
            </div>
        </form>
    </div>
</div>
