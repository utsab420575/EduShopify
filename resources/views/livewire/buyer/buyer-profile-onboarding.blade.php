<div x-data="{ addOpen: false, previewOpen: false }" @open-add-document.window="addOpen = true" @close-add-document.window="addOpen = false">

    {{-- Progress Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buyer Application</h1>
                <p class="text-xs text-gray-500 mt-0.5">Step {{ $step }} of {{ $totalSteps }}</p>
            </div>
            <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-200">
                {{ round(($step / $totalSteps) * 100) }}% complete
            </span>
        </div>

        <div class="flex gap-1 mt-3">
            @for($i = 1; $i <= $totalSteps; $i++)
                <button type="button" wire:click="goToStep({{ $i }})" {{ $i > $maxStepReached ? 'disabled' : '' }}
                    class="flex-1 h-1.5 rounded-full transition-all duration-300
                        {{ $i <= $step ? 'bg-indigo-500' : 'bg-gray-200' }}
                        {{ $i <= $maxStepReached ? 'cursor-pointer hover:opacity-80' : 'cursor-not-allowed' }}"></button>
            @endfor
        </div>

        <div class="flex justify-between mt-1.5">
            @foreach(($totalSteps === 3 ? ['Company Info','Branding','Documents'] : ['Company Info','Branding']) as $i => $label)
                <button type="button" wire:click="goToStep({{ $i + 1 }})" {{ ($i + 1) > $maxStepReached ? 'disabled' : '' }}
                    class="text-[10px] {{ ($i + 1) <= $step ? 'text-indigo-600 font-semibold' : 'text-gray-400' }}
                        {{ ($i + 1) <= $maxStepReached ? 'cursor-pointer hover:text-indigo-500' : 'cursor-not-allowed' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- ═══════════ STEP 1: Company Information ═══════════ --}}
    @if($step === 1)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
            Company Information
        </h2>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Display Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model.blur="display_name"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('display_name') border-red-400 @enderror">
                @error('display_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Organization Name @if($account->isOrganization())<span class="text-red-500">*</span>@endif</label>
                <input type="text" wire:model.blur="organization_name"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('organization_name') border-red-400 @enderror">
                @error('organization_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Buyer Type <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($buyerTypes as $type)
                    @php($checked = in_array($type->id, array_map('intval', $buyer_type_ids)))
                    <label class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border-2 cursor-pointer transition-all duration-150
                        {{ $checked ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300' }}">
                        <input type="checkbox" wire:click="toggleBuyerType({{ $type->id }})" @checked($checked) class="sr-only">
                        <div class="w-4 h-4 rounded border-2 flex-shrink-0 flex items-center justify-center {{ $checked ? 'border-indigo-500 bg-indigo-500' : 'border-gray-300' }}">
                            @if($checked)<svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>@endif
                        </div>
                        <span class="text-sm {{ $checked ? 'text-indigo-800 font-medium' : 'text-gray-700' }}">{{ $type->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('buyer_type_ids') <p class="mt-2 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Contact Person <span class="text-red-500">*</span></label>
                <input type="text" wire:model.blur="contact_person"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_person') border-red-400 @enderror">
                @error('contact_person') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Position</label>
                <input type="text" wire:model.blur="position" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Contact Email <span class="text-red-500">*</span></label>
                <input type="email" wire:model.blur="email"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-400 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Phone</label>
                <input type="tel" wire:model.blur="phone" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">Website</label>
            <input type="text" wire:model.blur="website" placeholder="https://yourcompany.com"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        {{-- Locations — repeatable: at least one, add/remove more freely --}}
        <div class="mb-4 pt-2">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-semibold text-gray-700">Locations <span class="text-red-500">*</span></label>
                <button type="button" wire:click="addLocation" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Location
                </button>
            </div>

            <div class="space-y-3">
                @foreach($locations as $i => $loc)
                    <div class="p-3 border border-gray-200 rounded-xl relative">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $i === 0 ? 'Primary Location' : 'Additional Location' }}</span>
                            @if(count($locations) > 1)
                                <button type="button" wire:click="removeLocation({{ $i }})" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Country <span class="text-red-500">*</span></label>
                                <select wire:model.live="locations.{{ $i }}.country_id"
                                    class="w-full px-2.5 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white @error('locations.'.$i.'.country_id') border-red-400 @enderror">
                                    <option value="">Select…</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" @selected($loc['country_id'] == $country->id)>{{ $country->flag }} {{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('locations.'.$i.'.country_id') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">State / Region</label>
                                <select wire:model.live="locations.{{ $i }}.state_id"
                                    class="w-full px-2.5 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white" {{ empty($loc['states']) ? 'disabled' : '' }}>
                                    <option value="">{{ empty($loc['states']) ? 'Select country first' : 'Select…' }}</option>
                                    @foreach($loc['states'] as $state)
                                        <option value="{{ $state['id'] }}" @selected($loc['state_id'] == $state['id'])>{{ $state['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">City</label>
                                <select wire:model="locations.{{ $i }}.city_id"
                                    class="w-full px-2.5 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white" {{ empty($loc['cities']) ? 'disabled' : '' }}>
                                    <option value="">{{ empty($loc['cities']) ? 'Select first' : 'Select…' }}</option>
                                    @foreach($loc['cities'] as $city)
                                        <option value="{{ $city['id'] }}" @selected($loc['city_id'] == $city['id'])>{{ $city['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-1">Address <span class="text-red-500">*</span></label>
                            <textarea wire:model.blur="locations.{{ $i }}.address" rows="2"
                                class="w-full px-2.5 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('locations.'.$i.'.address') border-red-400 @enderror"
                                placeholder="Street, building, area…">{{ $loc['address'] }}</textarea>
                            @error('locations.'.$i.'.address') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Tax ID</label>
                <input type="text" wire:model.blur="tax_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Bio</label>
                <textarea wire:model.blur="bio" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Procurement Info</label>
                <textarea wire:model.blur="procurement_info" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════ STEP 2: Branding & Media ═══════════ --}}
    @if($step === 2)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
            Branding & Media
        </h2>

        <div class="flex flex-col md:flex-row items-stretch gap-4 mb-4">
            {{-- Profile Photo (mandatory) --}}
            <div class="flex-shrink-0 w-full md:w-auto">
                <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                    <span>Profile Photo <span class="text-red-500">*</span></span>
                    <span class="text-[9px] text-gray-400 font-normal">(500x500 · Max 5MB)</span>
                </label>
                <label class="relative h-36 aspect-square w-full md:w-36 overflow-hidden flex flex-col items-center justify-center border-2 border-dashed rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition group bg-white @error('profile_photo') border-red-400 @else border-gray-300 @enderror">
                    @if($profile_photo)
                        <img src="{{ $profile_photo->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover">
                    @elseif($existingProfilePhoto)
                        <img src="{{ Storage::url($existingProfilePhoto) }}" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <svg class="w-7 h-7 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                    @endif
                    <input type="file" wire:model="profile_photo" class="hidden" accept="image/*">
                </label>
                @error('profile_photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Logo --}}
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                    <span>Company Logo</span>
                    <span class="text-[9px] text-gray-400 font-normal">(512x512 · Max 5MB)</span>
                </label>
                <label class="relative h-36 w-full overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition group bg-white">
                    @if($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover">
                    @elseif($existingLogo)
                        <img src="{{ Storage::url($existingLogo) }}" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <svg class="w-7 h-7 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                    @endif
                    <input type="file" wire:model="logo" class="hidden" accept="image/*">
                </label>
                @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Gallery --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Gallery Images <span class="text-gray-400">(up to 10 · Max 5MB each)</span></label>

            @if($existingGalleryImages->isNotEmpty())
                <div class="grid grid-cols-5 gap-2 mb-2">
                    @foreach($existingGalleryImages as $image)
                        <div class="relative group">
                            <img src="{{ Storage::url($image->image_path) }}" class="w-full aspect-square object-cover rounded-lg border border-gray-200">
                            <button type="button" wire:click="removeExistingGalleryImage({{ $image->id }})" wire:confirm="Remove this gallery image?"
                                class="absolute top-1 right-1 w-5 h-5 flex items-center justify-center rounded-full bg-gray-900/70 text-white opacity-0 group-hover:opacity-100 transition hover:bg-red-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <label class="flex items-center gap-2 px-3 py-2.5 border border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition text-sm text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Click to add gallery images
                <input type="file" wire:model="gallery_files" multiple class="hidden" accept="image/*">
            </label>
            @if($gallery_files)
                <div class="mt-2 grid grid-cols-5 gap-2">
                    @foreach($gallery_files as $i => $file)
                        <div class="relative group">
                            <img src="{{ $file->temporaryUrl() }}" class="w-full aspect-square object-cover rounded-lg border border-gray-200">
                            <button type="button" wire:click="removeGalleryFile({{ $i }})"
                                class="absolute top-1 right-1 w-5 h-5 flex items-center justify-center rounded-full bg-gray-900/70 text-white opacity-0 group-hover:opacity-100 transition hover:bg-red-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
            @error('gallery_files') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('gallery_files.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Social Media (optional, repeatable) --}}
        <div class="pt-4 mt-4 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-medium text-gray-700">Social Media <span class="text-gray-400 font-normal">(optional)</span></label>
                <button type="button" wire:click="addSocialLink" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Social Media
                </button>
            </div>

            @if(empty($social_links))
                <p class="text-xs text-gray-400">No social media links added yet.</p>
            @endif

            <div class="space-y-2">
                @foreach($social_links as $i => $row)
                    <div class="flex items-start gap-2">
                        <select wire:model="social_links.{{ $i }}.platform_id"
                            class="w-40 shrink-0 px-2.5 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white @error('social_links.'.$i.'.platform_id') border-red-400 @enderror">
                            <option value="">Select platform…</option>
                            @foreach($socialPlatforms as $platform)
                                <option value="{{ $platform->id }}" @selected($row['platform_id'] == $platform->id)>{{ $platform->name }}</option>
                            @endforeach
                        </select>
                        <div class="flex-1 space-y-1.5">
                            @if($row['platform_id'] == $otherSocialPlatformId)
                                <div>
                                    <input type="text" wire:model.blur="social_links.{{ $i }}.label" placeholder="Platform name (e.g. Pinterest, WhatsApp Channel)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('social_links.'.$i.'.label') border-red-400 @enderror">
                                    @error('social_links.'.$i.'.label') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @endif
                            <div>
                                <input type="url" wire:model.blur="social_links.{{ $i }}.url" placeholder="https://…"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('social_links.'.$i.'.url') border-red-400 @enderror">
                                @error('social_links.'.$i.'.url') <p class="mt-1 text-[10px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <button type="button" wire:click="removeSocialLink({{ $i }})" class="shrink-0 text-gray-400 hover:text-red-500 mt-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        @includeWhen($totalSteps === 2, 'livewire.buyer.partials.review-and-submit')
    </div>
    @endif

    {{-- ═══════════ STEP 3: Verification Documents (conditional) ═══════════ --}}
    @if($step === 3 && $totalSteps === 3)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
            Verification Documents
        </h2>
        <p class="text-xs text-gray-500 mb-4">Required documents are listed below. Add any optional documents from the picker.</p>
        @error('documents') <p class="mb-3 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ $message }}</p> @enderror

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-sm font-bold text-gray-900">Required Documents</h3>
                <button type="button" wire:click="openAddDocument" class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Add Document
                </button>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($requiredDocumentTypes as $type)
                    @php($doc = $currentDocs->get($type->id))
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 shrink-0 w-7 h-7 rounded-full flex items-center justify-center {{ $doc ? ($doc->status === 'verified' ? 'bg-green-100' : ($doc->status === 'rejected' ? 'bg-red-100' : 'bg-amber-100')) : 'bg-gray-100' }}">
                                @if(! $doc)
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @elseif($doc->status === 'verified')
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @elseif($doc->status === 'rejected')
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $type->name }} <span class="text-red-500">*</span></h3>
                                    @if($doc)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $doc->status === 'verified' ? 'bg-green-100 text-green-800' : ($doc->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ $doc->status === 'verified' ? 'Verified' : ($doc->status === 'rejected' ? 'Rejected' : 'Under Review') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Not uploaded</span>
                                    @endif
                                </div>
                                @if($doc)
                                    <p class="text-xs text-gray-600 mt-1 truncate">
                                        <span class="font-medium text-gray-800">{{ $doc->original_name }}</span>
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline font-medium ml-1">View</a>
                                    </p>
                                    @if($doc->status === 'rejected' && $doc->rejection_reason)
                                        <p class="mt-1.5 p-2 bg-red-50 border border-red-100 rounded-lg text-[11px] text-red-700"><span class="font-bold">Rejection reason:</span> {{ $doc->rejection_reason }}</p>
                                    @endif
                                @else
                                    <p class="text-xs text-gray-400 mt-0.5">Not uploaded yet — click Add Document.</p>
                                @endif
                            </div>
                            <button type="button" wire:click="openAddDocument({{ $type->id }})" class="shrink-0 self-center px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg transition">
                                {{ $doc ? 'Re-upload' : 'Upload' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-gray-400">No required documents configured.</div>
                @endforelse

                @foreach($customDocs as $doc)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 shrink-0 w-7 h-7 rounded-full flex items-center justify-center {{ $doc->status === 'verified' ? 'bg-green-100' : ($doc->status === 'rejected' ? 'bg-red-100' : 'bg-amber-100') }}">
                                <svg class="w-4 h-4 {{ $doc->status === 'verified' ? 'text-green-600' : ($doc->status === 'rejected' ? 'text-red-600' : 'text-amber-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $doc->custom_name ?? 'Custom Document' }}</h3>
                                <p class="text-xs text-gray-600 mt-1 truncate">
                                    <span class="font-medium text-gray-800">{{ $doc->original_name }}</span>
                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline font-medium ml-1">View</a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($optionalDocumentTypes->isNotEmpty())
                    <div class="p-4 bg-gray-50/40">
                        <p class="text-xs text-gray-500">Have other supporting documents? <button type="button" wire:click="openAddDocument" class="text-indigo-600 hover:underline font-medium">Add an optional document</button></p>
                    </div>
                @endif
            </div>
        </div>

        @include('livewire.buyer.partials.review-and-submit')
    </div>
    @endif

    {{-- ═══════════ Navigation Buttons ═══════════ --}}
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
        <div>
            @if($step > 1)
                <button type="button" wire:click="prevStep" class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @if($step < $totalSteps)
                <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 disabled:opacity-60 text-gray-700 font-medium rounded-xl transition text-sm">
                    Save Draft
                </button>
            @endif
            <span class="text-xs text-gray-400">{{ $step }} / {{ $totalSteps }}</span>
            @if($step < $totalSteps)
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition flex items-center gap-2 shadow-sm">
                    <span wire:loading.remove wire:target="nextStep">Continue</span>
                    <span wire:loading wire:target="nextStep">Saving…</span>
                    <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Add Document Modal --}}
    @if($totalSteps === 3)
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6" style="display: none;">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="addOpen = false"></div>
        <div x-show="addOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-900">Add Document</h3>
                <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Document Type <span class="text-red-500">*</span></label>

                    <button type="button" @click="open = !open"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white flex items-center justify-between focus:ring-2 focus:ring-indigo-500 text-left transition cursor-pointer @error('new_document_type_id') border-red-400 @enderror">
                        <span class="flex items-center gap-2 truncate min-w-0">
                            @if($new_document_type_id === 'other')
                                <span class="font-medium text-gray-900 truncate">Other (Custom Document)</span>
                            @elseif($selectedType)
                                <span class="font-medium text-gray-900 truncate">{{ $selectedType->name }}</span>
                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded {{ $selectedType->is_required ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} shrink-0">
                                    {{ $selectedType->is_required ? 'Required' : 'Optional' }}
                                </span>
                            @else
                                <span class="text-gray-400">Select a document type…</span>
                            @endif
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0 ml-2" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" x-transition class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto p-1.5 space-y-1" style="display: none;">
                        @foreach($allDocTypesForModal as $type)
                            <button type="button" wire:click="$set('new_document_type_id', '{{ $type->id }}')" @click="open = false"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left transition cursor-pointer text-xs font-medium {{ ((string) $type->id === $new_document_type_id) ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : 'hover:bg-gray-50 text-gray-800' }}">
                                <span class="truncate">{{ $type->name }}</span>
                                <span class="flex items-center gap-1.5 shrink-0">
                                    @if($currentDocs->has($type->id))<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Uploaded</span>@endif
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $type->is_required ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">{{ $type->is_required ? 'Required' : 'Optional' }}</span>
                                </span>
                            </button>
                        @endforeach
                        <div class="border-t border-gray-100 my-1"></div>
                        <button type="button" wire:click="$set('new_document_type_id', 'other')" @click="open = false"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left transition cursor-pointer text-xs font-medium {{ $new_document_type_id === 'other' ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : 'hover:bg-gray-50 text-gray-800' }}">
                            <span class="truncate">Other (Custom Document)</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">Optional</span>
                        </button>
                    </div>
                    @error('new_document_type_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                @if($new_document_type_id === 'other')
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Document Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="new_custom_name" placeholder="e.g. ID Proof"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('new_custom_name') border-red-400 @enderror">
                        @error('new_custom_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">File <span class="text-red-500">*</span></label>
                    <input type="file" wire:model="new_file"
                        class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-300 rounded-lg @error('new_file') border-red-400 @enderror">
                    <div wire:loading wire:target="new_file" class="text-[11px] text-gray-400 mt-1">Uploading file…</div>
                    @error('new_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Expiry Date <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="date" wire:model="new_expiry" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex gap-3 px-5 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                <button type="button" @click="addOpen = false" class="flex-1 py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg transition">Cancel</button>
                <button type="button" wire:click="addDocument" wire:loading.attr="disabled" wire:target="addDocument,new_file"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="addDocument">Upload Document</span>
                    <span wire:loading wire:target="addDocument">Uploading…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        Livewire.on('document-uploaded', () => {
            window.dispatchEvent(new CustomEvent('close-add-document'));
        });
    </script>
    @endscript
</div>
