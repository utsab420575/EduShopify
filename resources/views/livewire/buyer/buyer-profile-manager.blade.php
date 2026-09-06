<div>

{{-- Flash notification (inline since this is a Livewire component inside a layout) --}}
@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-5 right-5 z-[200] flex items-center gap-3 bg-white border border-green-200 text-green-800 text-sm font-medium rounded-xl shadow-lg px-5 py-3">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Page Header --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your buyer profile, media, and locations.</p>
    </div>
    @if($profile?->isComplete())
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-green-50 text-green-700 border border-green-200">
            <i class="fa-solid fa-circle-check"></i> Profile Complete
        </span>
    @else
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
            <i class="fa-solid fa-clock"></i> Draft
        </span>
    @endif
</div>

{{-- Profile Summary Card --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-4">
        {{-- Avatar / Logo --}}
        <div class="relative flex-shrink-0">
            <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'B').'&background=e0e7ff&color=4f46e5&size=80' }}"
                 class="w-16 h-16 rounded-2xl object-cover border border-gray-200 shadow-sm" alt="Logo">
            @if($profile?->profile_photo)
                <img src="{{ asset('storage/'.$profile->profile_photo) }}"
                     class="w-7 h-7 rounded-full border-2 border-white absolute -bottom-1 -right-1 object-cover shadow" alt="">
            @endif
        </div>

        {{-- Name / email / location --}}
        <div class="flex-1 min-w-0">
            <p class="text-lg font-bold text-gray-900 truncate">{{ $profile?->display_name ?? 'Your Business Name' }}</p>
            <p class="text-sm text-gray-500 truncate">{{ $profile?->email ?? $account->primaryOwner?->email }}</p>
            @if($profile?->country_id)
                <p class="text-xs text-gray-400 mt-0.5">
                    <i class="fa-solid fa-location-dot mr-1 text-indigo-400"></i>
                    {{ collect([$profile->city?->name, $profile->country?->name])->filter()->implode(', ') }}
                </p>
            @endif
        </div>

        {{-- Stats --}}
        <div class="hidden sm:flex items-center gap-5 flex-shrink-0 border-l border-gray-100 pl-5">
            <div class="text-center">
                <p class="text-xl font-bold text-indigo-600">{{ $existingGallery->count() }}</p>
                <p class="text-xs text-gray-400">Gallery</p>
            </div>
            <div class="text-center">
                <p class="text-xl font-bold text-emerald-600">{{ count($locations) }}</p>
                <p class="text-xs text-gray-400">Locations</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════ ACCORDION ═══════════════════════════════════ --}}
<div class="space-y-3">

    {{-- ── 1. Company Information ── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: true }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-building text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Company Information</p>
                    <p class="text-xs text-gray-400">{{ $display_name ?: 'Name, type, bio, procurement notes' }}</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-company">
            @error('display_name') <p class="text-xs text-red-600 mb-3">{{ $message }}</p> @enderror
            @error('buyer_type_ids') <p class="text-xs text-red-600 mb-3">{{ $message }}</p> @enderror

            <div class="space-y-5">
                {{-- Buyer Types --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buyer Type(s) <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($buyerTypes as $bt)
                            <label class="inline-flex items-center gap-1.5 text-xs font-medium border rounded-full px-3 py-1.5 cursor-pointer transition-colors {{ in_array($bt->id, $buyer_type_ids) ? 'border-indigo-400 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                <input type="checkbox" wire:model.live="buyer_type_ids" value="{{ $bt->id }}">
                                {{ $bt->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                        <input wire:model="display_name" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition" placeholder="e.g. Acme Corp">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Organisation Name</label>
                        <input wire:model="organization_name" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition" placeholder="Legal entity name">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">About / Bio</label>
                    <textarea wire:model="bio" rows="3" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition resize-none" placeholder="Tell suppliers about your organisation..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Procurement Notes</label>
                    <textarea wire:model="procurement_info" rows="2" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition resize-none" placeholder="What do you typically procure?"></textarea>
                </div>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button wire:click="saveCompany" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveCompany">Save Company Info</span>
                    <span wire:loading wire:target="saveCompany">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── 2. Contact Information ── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                    <i class="fa-solid fa-address-card text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Contact Information</p>
                    <p class="text-xs text-gray-400">{{ $contact_person ?: 'Person, email, phone, website' }}</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-contact">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                    <input wire:model="contact_person" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                    @error('contact_person') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Position / Title</label>
                    <input wire:model="position" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input wire:model="phone" type="tel" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Website</label>
                    <input wire:model="website" type="url" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition" placeholder="https://...">
                    @error('website') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tax ID / VAT</label>
                    <input wire:model="tax_id" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-300 outline-none transition">
                </div>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button wire:click="saveContact" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveContact">Save Contact</span>
                    <span wire:loading wire:target="saveContact">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── 3. Media & Branding ── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600">
                    <i class="fa-solid fa-images text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Media & Branding</p>
                    <p class="text-xs text-gray-400">Logo, profile photo, gallery images</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-media">

            {{-- Logo & Profile Photo --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Logo</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ $profile?->logo ? asset('storage/'.$profile->logo) : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'B').'&background=e0e7ff&color=4f46e5' }}"
                             class="w-16 h-16 rounded-xl object-contain border border-gray-200 bg-gray-50" alt="Logo">
                        <div class="flex-1">
                            <input wire:model="logo_upload" type="file" accept="image/*"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-400">PNG/JPG/WEBP, max 5MB</p>
                            @error('logo_upload') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @if($logo_upload)
                                <img src="{{ $logo_upload->temporaryUrl() }}" class="mt-2 w-12 h-12 rounded-lg object-cover border border-indigo-200">
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Profile Photo</label>
                    <div class="flex items-center gap-4">
                        @if($profile?->profile_photo)
                            <img src="{{ asset('storage/'.$profile->profile_photo) }}"
                                 class="w-16 h-16 rounded-full object-cover border border-gray-200" alt="Photo">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">
                                <i class="fa-solid fa-user text-xl"></i>
                            </div>
                        @endif
                        <div class="flex-1">
                            <input wire:model="profile_photo_upload" type="file" accept="image/*"
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                            <p class="mt-1 text-xs text-gray-400">PNG/JPG/WEBP, max 5MB</p>
                            @error('profile_photo_upload') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @if($profile_photo_upload)
                                <img src="{{ $profile_photo_upload->temporaryUrl() }}" class="mt-2 w-12 h-12 rounded-full object-cover border border-violet-200">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gallery --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Gallery Images</label>

                {{-- Existing Gallery --}}
                @if($existingGallery->isNotEmpty())
                    <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-7 gap-2 mb-4">
                        @foreach($existingGallery as $img)
                            <div class="relative group aspect-square">
                                <img src="{{ asset('storage/'.$img->image_path) }}"
                                     class="w-full h-full object-cover rounded-xl border border-gray-200" alt="">
                                <button wire:click="removeExistingGalleryImage({{ $img->id }})"
                                        wire:confirm="Remove this image?"
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[9px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- New staged uploads preview --}}
                @if(count($gallery_files) > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-7 gap-2 mb-4">
                        @foreach($gallery_files as $i => $file)
                            <div class="relative group aspect-square">
                                <img src="{{ $file->temporaryUrl() }}"
                                     class="w-full h-full object-cover rounded-xl border-2 border-indigo-300" alt="">
                                <button wire:click="removeGalleryFile({{ $i }})"
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[9px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow cursor-pointer">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <span class="absolute bottom-1 left-1 text-[8px] font-bold text-white bg-indigo-500 px-1 rounded">NEW</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <label class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-colors">
                    <i class="fa-solid fa-cloud-arrow-up text-gray-400 text-xl"></i>
                    <span class="text-sm text-gray-500">Click to add gallery images (max 20)</span>
                    <input wire:model="new_gallery_files" type="file" accept="image/*" multiple class="hidden">
                </label>
                <p class="mt-1 text-xs text-gray-400">JPG, PNG or WEBP • Up to 5MB each • Click "Save Media" to upload</p>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                <button wire:click="saveMedia" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveMedia">Save Media</span>
                    <span wire:loading wire:target="saveMedia">Uploading...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── 4. Social Links ── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-pink-50 flex items-center justify-center text-pink-600">
                    <i class="fa-solid fa-share-nodes text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Social Links</p>
                    <p class="text-xs text-gray-400">{{ count($social_links) > 0 ? count($social_links).' link'.( count($social_links) > 1 ? 's' : '').' added' : 'LinkedIn, Twitter, Facebook, etc.' }}</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-social">

            @if(count($social_links) === 0)
                <p class="text-sm text-gray-400 text-center py-4">No social links added yet.</p>
            @endif

            <div class="space-y-3">
                @foreach($social_links as $i => $link)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100" wire:key="sl-{{ $i }}">
                        <select wire:model="social_links.{{ $i }}.platform_id"
                                class="text-sm rounded-lg border border-gray-300 px-2 py-2 bg-white focus:ring-2 focus:ring-pink-300 outline-none w-36 flex-shrink-0">
                            <option value="">Platform</option>
                            @foreach($socialPlatforms as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                            @endforeach
                        </select>
                        <input wire:model="social_links.{{ $i }}.url" type="url"
                               class="flex-1 text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none transition"
                               placeholder="https://...">
                        <input wire:model="social_links.{{ $i }}.label" type="text"
                               class="w-28 text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none transition hidden sm:block"
                               placeholder="Label">
                        <button wire:click="removeSocialLink({{ $i }})"
                                class="w-8 h-8 flex-shrink-0 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                    @error("social_links.$i.url") <p class="text-xs text-red-600 -mt-2 ml-3">{{ $message }}</p> @enderror
                @endforeach
            </div>

            <button wire:click="addSocialLink"
                    class="mt-4 flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition cursor-pointer">
                <i class="fa-solid fa-plus"></i> Add Link
            </button>

            <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                <button wire:click="saveSocialLinks" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span wire:loading.remove wire:target="saveSocialLinks">Save Links</span>
                    <span wire:loading wire:target="saveSocialLinks">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── 5. Locations ── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-location-dot text-sm"></i>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-900">Locations</p>
                    <p class="text-xs text-gray-400">{{ count($locations) > 0 ? count($locations).' location'.( count($locations) > 1 ? 's' : '') : 'Offices, warehouses, delivery addresses' }}</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>

        <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6" wire:key="section-locations">

            {{-- Existing Locations --}}
            @foreach($locations as $i => $loc)
                <div class="mb-4 border border-gray-200 rounded-xl overflow-hidden" wire:key="loc-{{ $loc['id'] }}"
                     x-data="{ editing: false }">
                    {{-- Location Card Header --}}
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                        <div class="flex items-center gap-2 min-w-0">
                            @if($loc['is_primary'])
                                <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-200">Primary</span>
                            @endif
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $loc['label'] ?: ucwords(str_replace('_', ' ', $loc['location_type'])) }}
                            </p>
                            <p class="text-xs text-gray-500 hidden sm:block">
                                {{ collect([$loc['address_line_1'], $loc['city_name'], $loc['country_name']])->filter()->implode(', ') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                            @if(! $loc['is_primary'])
                                <button wire:click="makePrimaryLocation({{ $loc['id'] }})"
                                        class="text-xs text-emerald-600 font-medium hover:underline">Set Primary</button>
                            @endif
                            <button @click="editing = !editing"
                                    class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </button>
                            <button wire:click="removeLocation({{ $loc['id'] }})"
                                    wire:confirm="Remove this location?"
                                    class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Inline Edit Form --}}
                    <div x-show="editing" x-transition x-cloak class="p-4 border-t border-gray-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select wire:model="locations.{{ $i }}.location_type"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none bg-white">
                                    <option value="primary">Head Office</option>
                                    <option value="registered_office">Registered Office</option>
                                    <option value="branch">Branch</option>
                                    <option value="warehouse">Warehouse</option>
                                    <option value="showroom">Showroom</option>
                                    <option value="billing">Billing Address</option>
                                    <option value="delivery">Delivery Address</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Label (optional)</label>
                                <input wire:model="locations.{{ $i }}.label" type="text"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                            </div>

                            {{-- Country / State / City --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                                <select wire:model="locations.{{ $i }}.country_id"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-300 outline-none">
                                    <option value="">Select country</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error("locations.$i.country_id") <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                                <select wire:model="locations.{{ $i }}.state_id"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-300 outline-none">
                                    <option value="">Select state</option>
                                    @foreach($loc['states'] as $s)
                                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                <select wire:model="locations.{{ $i }}.city_id"
                                        class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-300 outline-none">
                                    <option value="">Select city</option>
                                    @foreach($loc['cities'] as $ci)
                                        <option value="{{ $ci['id'] }}">{{ $ci['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Postal Code</label>
                                <input wire:model="locations.{{ $i }}.postal_code" type="text"
                                       class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                            <input wire:model="locations.{{ $i }}.address_line_1" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                            @error("locations.$i.address_line_1") <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 2</label>
                            <input wire:model="locations.{{ $i }}.address_line_2" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button @click="editing = false"
                                    class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                            <button wire:click="updateLocation({{ $loc['id'] }})" @click="editing = false"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg text-white transition" style="background:var(--theme-primary)">
                                <i class="fa-solid fa-floppy-disk"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Add New Location Form --}}
            <div class="mt-2" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-2 text-sm font-medium text-emerald-600 hover:text-emerald-800 transition mb-4">
                    <i class="fa-solid fa-plus"></i> Add New Location
                </button>

                <div x-show="open" x-transition x-cloak
                     class="border border-emerald-200 bg-emerald-50/40 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">New Location</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Location Type <span class="text-red-500">*</span></label>
                            <select wire:model="new_location.location_type"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                <option value="primary">Head Office</option>
                                <option value="registered_office">Registered Office</option>
                                <option value="branch">Branch</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="showroom">Showroom</option>
                                <option value="billing">Billing Address</option>
                                <option value="delivery">Delivery Address</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Label (optional)</label>
                            <input wire:model="new_location.label" type="text" placeholder="e.g. Main Office"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Contact Name</label>
                            <input wire:model="new_location.contact_name" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                            <input wire:model="new_location.phone" type="tel"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                            <select wire:model="new_location.country_id"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none">
                                <option value="">Select country</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('new_location.country_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">State</label>
                            <select wire:model="new_location.state_id"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none"
                                    @if(empty($new_location['states'])) disabled @endif>
                                <option value="">Select state</option>
                                @foreach($new_location['states'] ?? [] as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                            <select wire:model="new_location.city_id"
                                    class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-300 outline-none"
                                    @if(empty($new_location['cities'])) disabled @endif>
                                <option value="">Select city</option>
                                @foreach($new_location['cities'] ?? [] as $ci)
                                    <option value="{{ $ci['id'] }}">{{ $ci['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Postal Code</label>
                            <input wire:model="new_location.postal_code" type="text"
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                        <input wire:model="new_location.address_line_1" type="text"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                        @error('new_location.address_line_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Address Line 2</label>
                        <input wire:model="new_location.address_line_2" type="text"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-emerald-300 outline-none">
                    </div>

                    <label class="flex items-center gap-2 mt-3 text-sm text-gray-700 cursor-pointer">
                        <input wire:model="new_location.is_primary" type="checkbox" class="rounded" style="accent-color:var(--theme-primary)">
                        Set as primary location
                    </label>

                    <div class="flex justify-end gap-2 mt-4">
                        <button @click="open = false"
                                class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                        <button wire:click="addLocation" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition" style="background:var(--theme-primary)">
                            <i class="fa-solid fa-plus"></i>
                            <span wire:loading.remove wire:target="addLocation">Add Location</span>
                            <span wire:loading wire:target="addLocation">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end accordion --}}

</div>{{-- end root --}}
