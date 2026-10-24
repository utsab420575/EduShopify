{{-- ── 2. Contact Information ── --}}
<div id="sp-section-contact" data-profile-section="Contact Information" data-filled="{{ (filled($profile?->contact_person) && (filled($profile?->contact_email) || filled($profile?->contact_phone))) ? 1 : 0 }}" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden"
     x-data="{ open: {{ $spAccOpen('contact', false) }} }"
     x-init="$watch('open', v => localStorage.setItem('sp-acc-contact', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                <i class="fa-solid fa-address-card text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Contact Information</p>
                <p class="text-xs text-gray-400">{{ $profile?->contact_person ?: 'Person, email, phone, support channels' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">
        <form method="POST" action="{{ route('supplier.company.profile.contact.update') }}">
            @csrf
            @method('PUT')

            @include('backend.supplier.company.partials._section-errors', ['section' => 'contact'])

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                    <input name="contact_person" value="{{ old('contact_person', $profile?->contact_person) }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition" required>
                    @error('contact_person') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Email <span class="text-red-500">*</span></label>
                    <input name="contact_email" value="{{ old('contact_email', $profile?->contact_email) }}" type="email" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition" required>
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

            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-500 flex items-center justify-between gap-2">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot text-emerald-600"></i>
                    Physical facilities, head office, and delivery service areas are managed in <strong>Locations & Service Areas</strong>.
                </span>
                <a href="{{ route('supplier.company.profile', ['section' => 'locations']) }}" class="text-emerald-700 font-semibold hover:underline flex-shrink-0">Go to Locations &rarr;</a>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i> Save Contact
                </button>
            </div>
        </form>
    </div>
</div>
