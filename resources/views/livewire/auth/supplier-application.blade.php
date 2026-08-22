<div x-data="{ step: @entangle('step') }">

    {{-- Progress Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Supplier Application</h1>
                <p class="text-xs text-gray-500 mt-0.5">Step {{ $step }} of {{ $totalSteps }}</p>
            </div>
            <span class="text-xs font-medium text-teal-700 bg-teal-50 px-3 py-1 rounded-full border border-teal-200">
                {{ round(($step / $totalSteps) * 100) }}% complete
            </span>
        </div>

        {{-- Step bar --}}
        <div class="flex gap-1 mt-3">
            @for($i = 1; $i <= $totalSteps; $i++)
                <div class="flex-1 h-1.5 rounded-full transition-all duration-300
                    {{ $i <= $step ? 'bg-teal-500' : 'bg-gray-200' }}"></div>
            @endfor
        </div>

        {{-- Step labels --}}
        <div class="flex justify-between mt-1.5">
            @foreach(['Company','Branding','Types','Exhbt.','Contact','Social','Docs','Hours','Payment'] as $i => $label)
                <span class="text-[10px] {{ ($i + 1) <= $step ? 'text-teal-600 font-semibold' : 'text-gray-400' }}">
                    {{ $label }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- ═══════════ STEP 1: Company Info ═══════════ --}}
    @if($step === 1)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
            Company Information
        </h2>

        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model.blur="company_name"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('company_name') border-red-400 @enderror"
                    placeholder="Your company name">
                @error('company_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Company Type / Legal Entity</label>
                <select wire:model.live="company_type"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-white">
                    <option value="">Select type…</option>
                    <option value="LLC">LLC</option>
                    <option value="JSC">JSC</option>
                    <option value="Sole Proprietorship">Sole Proprietorship</option>
                    <option value="Partnership">Partnership</option>
                    <option value="Corporation">Corporation</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            @if($company_type === 'Other')
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Specify Other Company Type <span class="text-red-500">*</span></label>
                <input type="text" wire:model.blur="custom_company_type"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('custom_company_type') border-red-400 @enderror"
                    placeholder="Government Entity, NGO, etc.">
                @error('custom_company_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Founded Year</label>
                <input type="number" wire:model.blur="founded_year"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                    placeholder="2010" min="1800" max="{{ date('Y') }}">
                @error('founded_year') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Country</label>
                <select wire:model.live="country_id"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-white">
                    <option value="">Select country…</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->flag }} {{ $country->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">City</label>
                <select wire:model="city_id"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent bg-white @error('city_id') border-red-400 @enderror"
                    {{ empty($cities) ? 'disabled' : '' }}>
                    <option value="">{{ empty($cities) ? 'Select country first' : 'Select city…' }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                <textarea wire:model.blur="address" rows="2"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                    placeholder="Street, building, area…"></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Website</label>
                <input type="text" wire:model.blur="website"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('website') border-red-400 @enderror"
                    placeholder="https://yourcompany.com">
                @error('website') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">No. of Employees</label>
                <input type="number" wire:model.blur="employees"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                    placeholder="50" min="1">
                @error('employees') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════ STEP 2: Branding ═══════════ --}}
    @if($step === 2)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
            Branding & Media
        </h2>

        <div class="space-y-4">
            <div class="flex flex-col md:flex-row items-stretch gap-4 mb-4">
                {{-- Logo --}}
                <div class="flex-shrink-0 w-full md:w-auto">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                        <span>Company Logo</span>
                        <span class="text-[9px] text-gray-400 font-normal">(512x512 · Max 5MB)</span>
                    </label>
                    <label class="relative h-36 aspect-square w-full md:w-36 overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-teal-400 hover:bg-teal-50 transition group bg-white">
                        @if($this->safeTemporaryUrl($logo))
                            <img src="{{ $this->safeTemporaryUrl($logo) }}" class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <svg class="w-7 h-7 text-gray-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        @endif
                        <input type="file" wire:model="logo" class="hidden" accept="image/*">
                    </label>
                    @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Profile Photo --}}
                <div class="flex-shrink-0 w-full md:w-auto">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                        <span>Profile Photo</span>
                        <span class="text-[9px] text-gray-400 font-normal">(500x500 · Max 5MB)</span>
                    </label>
                    <label class="relative h-36 aspect-square w-full md:w-36 overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-teal-400 hover:bg-teal-50 transition group bg-white">
                        @if($this->safeTemporaryUrl($profile_photo))
                            <img src="{{ $this->safeTemporaryUrl($profile_photo) }}" class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <svg class="w-7 h-7 text-gray-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        @endif
                        <input type="file" wire:model="profile_photo" class="hidden" accept="image/*">
                    </label>
                    @error('profile_photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Banner --}}
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex flex-col sm:flex-row sm:items-center justify-between gap-0.5">
                        <span>Cover Banner</span>
                        <span class="text-[9px] text-gray-400 font-normal">(1200x400 · Max 5MB)</span>
                    </label>
                    <label class="relative h-36 w-full overflow-hidden flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-teal-400 hover:bg-teal-50 transition group bg-white">
                        @if($this->safeTemporaryUrl($banner))
                            <img src="{{ $this->safeTemporaryUrl($banner) }}" class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <svg class="w-7 h-7 text-gray-400 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        @endif
                        <input type="file" wire:model="banner" class="hidden" accept="image/*">
                    </label>
                    @error('banner') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Gallery --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Gallery Images <span class="text-gray-450">(up to 10 · recommended 800x600 px · Max 5MB each)</span></label>
                <label class="flex items-center gap-2 px-3 py-2.5 border border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-teal-400 hover:bg-teal-50 transition text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Click to add gallery images
                    <input type="file" wire:model="gallery_files" multiple class="hidden" accept="image/*">
                </label>
                @if($gallery_files)
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($gallery_files as $img)
                            @if($this->safeTemporaryUrl($img))
                                <img src="{{ $this->safeTemporaryUrl($img) }}" class="w-14 h-14 object-cover rounded-lg border border-gray-200">
                            @endif
                        @endforeach
                    </div>
                @endif
                @error('gallery_files') <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                @error('gallery_files.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- YouTube Videos --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-medium text-gray-700">YouTube Video URLs <span class="text-gray-400">(up to 5)</span></label>
                    @if(count($video_urls) < 5)
                        <button type="button" wire:click="addVideoUrl" class="text-xs text-teal-600 hover:text-teal-800 font-medium">+ Add Video</button>
                    @endif
                </div>
                @foreach($video_urls as $i => $url)
                    <div class="flex gap-2 mb-2">
                        <input type="url" wire:model.blur="video_urls.{{ $i }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            placeholder="https://youtube.com/watch?v=…">
                        <button type="button" wire:click="removeVideoUrl({{ $i }})" class="text-red-400 hover:text-red-600 text-sm px-2">✕</button>
                    </div>
                @endforeach
                @if(empty($video_urls))
                    <button type="button" wire:click="addVideoUrl"
                        class="text-xs text-gray-400 hover:text-teal-600 border border-dashed border-gray-200 rounded-lg px-3 py-2 w-full transition">
                        + Add a YouTube video
                    </button>
                @endif
                @error('video_urls') <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                @error('video_urls.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════ STEP 3: Supplier Types ═══════════ --}}
    @if($step === 3)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
            Supplier Types
        </h2>
        <p class="text-xs text-gray-500 mb-4">Select up to <strong>5</strong> types that best describe your business.</p>

        @error('supplier_type_ids') <p class="mb-3 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ $message }}</p> @enderror
        @error('supplier_types')    <p class="mb-3 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ $message }}</p> @enderror

        <div class="grid grid-cols-2 gap-2">
            @foreach($supplierTypes as $type)
                <button type="button" wire:click="toggleSupplierType({{ $type->id }})"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border-2 text-sm text-left transition-all duration-150
                        {{ in_array($type->id, $supplier_type_ids)
                            ? 'border-teal-500 bg-teal-50 text-teal-800 font-medium'
                            : 'border-gray-200 hover:border-teal-300 hover:bg-teal-50 text-gray-700' }}">
                    <div class="w-4 h-4 rounded border-2 flex-shrink-0 flex items-center justify-center transition-colors
                        {{ in_array($type->id, $supplier_type_ids) ? 'border-teal-500 bg-teal-500' : 'border-gray-300' }}">
                        @if(in_array($type->id, $supplier_type_ids))
                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </div>
                    {{ $type->getTranslation('name', app()->getLocale(), false) ?: $type->name }}
                </button>
            @endforeach
        </div>

        <p class="mt-3 text-xs text-gray-400">
            Selected: <strong class="{{ count($supplier_type_ids) >= 5 ? 'text-orange-500' : 'text-teal-600' }}">{{ count($supplier_type_ids) }}/5</strong>
        </p>
    </div>
    @endif

    {{-- ═══════════ STEP 4: Exhibitions ═══════════ --}}
    @if($step === 4)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">4</span>
            Exhibitions <span class="text-xs font-normal text-gray-400 ml-1">(optional)</span>
        </h2>
        <p class="text-xs text-gray-500 mb-4">Which education trade shows do you exhibit at?</p>

        <div class="grid grid-cols-2 gap-2">
            @foreach($exhibitions as $ex)
                <label class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border-2 cursor-pointer transition-all duration-150
                    {{ in_array($ex->id, $exhibition_ids)
                        ? 'border-teal-500 bg-teal-50'
                        : 'border-gray-200 hover:border-teal-300' }}">
                    <input type="checkbox" wire:model.live="exhibition_ids" value="{{ $ex->id }}" class="sr-only">
                    <div class="w-4 h-4 rounded border-2 flex-shrink-0 flex items-center justify-center
                        {{ in_array($ex->id, $exhibition_ids) ? 'border-teal-500 bg-teal-500' : 'border-gray-300' }}">
                        @if(in_array($ex->id, $exhibition_ids))
                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </div>
                    <span class="text-sm {{ in_array($ex->id, $exhibition_ids) ? 'text-teal-800 font-medium' : 'text-gray-700' }}">
                        {{ $ex->getTranslation('name', app()->getLocale(), false) ?: $ex->name }}
                    </span>
                </label>
            @endforeach
        </div>
        @error('exhibition_ids') <p class="mt-2 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
        @error('exhibition_ids.*') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

        <p class="mt-4 text-xs text-gray-400 italic">Not exhibiting yet? That's fine — skip this step.</p>
    </div>
    @endif

    {{-- ═══════════ STEP 5: Contact ═══════════ --}}
    @if($step === 5)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">5</span>
            Contact Information
        </h2>

        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Contact Person <span class="text-red-500">*</span></label>
                <input type="text" wire:model.blur="contact_person"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('contact_person') border-red-400 @enderror"
                    placeholder="Primary contact name">
                @error('contact_person') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                <input type="tel" wire:model.blur="contact_phone"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('contact_phone') border-red-400 @enderror"
                    placeholder="+971 50 000 0000">
                @error('contact_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">WhatsApp</label>
                <input type="tel" wire:model.blur="whatsapp"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                    placeholder="+971 50 000 0000">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Contact Email <span class="text-red-500">*</span></label>
                <input type="email" wire:model.blur="contact_email"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('contact_email') border-red-400 @enderror"
                    placeholder="sales@company.com">
                @error('contact_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Support Email</label>
                <input type="email" wire:model.blur="support_email"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error('support_email') border-red-400 @enderror"
                    placeholder="support@company.com">
                @error('support_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════ STEP 6: Social ═══════════ --}}
    @if($step === 6)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">6</span>
            Social Media <span class="text-xs font-normal text-gray-400 ml-1">(optional)</span>
        </h2>

        <div class="space-y-3">
            @foreach([
                ['field' => 'linkedin',  'label' => 'LinkedIn',  'placeholder' => 'https://linkedin.com/company/…'],
                ['field' => 'facebook',  'label' => 'Facebook',  'placeholder' => 'https://facebook.com/…'],
                ['field' => 'instagram', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/…'],
                ['field' => 'youtube',   'label' => 'YouTube',   'placeholder' => 'https://youtube.com/channel/…'],
                ['field' => 'x',         'label' => 'X (formerly Twitter)', 'placeholder' => 'https://x.com/…'],
            ] as $social)
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ $social['label'] }}</label>
                    <input type="url" wire:model.blur="{{ $social['field'] }}"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent @error($social['field']) border-red-400 @enderror"
                        placeholder="{{ $social['placeholder'] }}">
                    @error($social['field']) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════ STEP 7: Documents ═══════════ --}}
    @if($step === 7)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">7</span>
            Verification Documents
        </h2>
        <p class="text-xs text-gray-500 mb-4">Upload your company documents for verification. Required documents are marked <span class="text-red-500 font-medium">*</span></p>

        <div class="space-y-3">
            @foreach($documentTypes as $docType)
                <div class="border border-gray-200 rounded-xl p-4 {{ $docType->is_required ? 'border-l-4 border-l-orange-400' : '' }}">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-sm font-medium text-gray-800">
                                {{ $docType->getTranslation('name', app()->getLocale(), false) ?: $docType->name }}
                                @if($docType->is_required) <span class="text-red-500">*</span> @else <span class="text-xs text-gray-400 font-normal ml-1">(optional)</span> @endif
                            </p>
                            @php $desc = $docType->getTranslation('description', app()->getLocale(), false) ?: $docType->description; @endphp
                            @if($desc)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
                            @endif
                        </div>
                        @if(!empty($docType->accepted_formats))
                            <div class="flex gap-1 flex-shrink-0 ml-2">
                                @foreach($docType->accepted_formats as $fmt)
                                    <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded font-mono uppercase">{{ $fmt }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <label class="flex items-center gap-2 px-3 py-2.5 border border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-teal-400 hover:bg-teal-50 transition text-sm">
                        @if(isset($documents[$docType->id]) && $documents[$docType->id])
                            <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-teal-700 font-medium text-xs">
                                {{ $documents[$docType->id]?->getClientOriginalName() }}
                            </span>
                        @else
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-gray-500 text-xs">Click to upload {{ $docType->max_size_kb ? '(max ' . round($docType->max_size_kb / 1024, 1) . ' MB)' : '' }}</span>
                        @endif
                        <input type="file" wire:model="documents.{{ $docType->id }}" class="hidden"
                            accept="{{ $docType->accept }}">
                    </label>

                    @error("documents.{$docType->id}") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════ STEP 8: Business Hours ═══════════ --}}
    @if($step === 8)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">8</span>
            Business Hours
        </h2>

        {{-- Set All Times at Once --}}
        <div class="mb-4 p-4 bg-teal-50 border border-teal-100 rounded-xl flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-xs font-semibold text-teal-850 block">Bulk Set Times</span>
                <span class="text-[10px] text-teal-600">Apply start/end time to all open days at once</span>
            </div>
            <div class="flex items-center gap-2">
                <input type="time" wire:model="default_open_time"
                    class="px-2 py-1 border border-teal-200 rounded-lg text-xs bg-white focus:ring-1 focus:ring-teal-500">
                <span class="text-xs text-teal-600">to</span>
                <input type="time" wire:model="default_close_time"
                    class="px-2 py-1 border border-teal-200 rounded-lg text-xs bg-white focus:ring-1 focus:ring-teal-500">
                <button type="button" wire:click="applyDefaultHours"
                    class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold transition shadow-sm">
                    Apply to All Open Days
                </button>
            </div>
        </div>

        <div class="space-y-2">
            @foreach($business_hours as $i => $bh)
                <div class="flex items-center gap-3 py-2.5 px-3 rounded-xl {{ $bh['is_open'] ? 'bg-teal-50 border border-teal-100' : 'bg-gray-50 border border-gray-100' }}">
                    <div class="w-20 flex-shrink-0">
                        <span class="text-xs font-semibold text-gray-700">{{ $bh['day_name'] }}</span>
                    </div>

                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" wire:model.live="business_hours.{{ $i }}.is_open"
                            class="w-4 h-4 rounded text-teal-600">
                        <span class="text-xs {{ $bh['is_open'] ? 'text-teal-700' : 'text-gray-400' }}">
                            {{ $bh['is_open'] ? 'Open' : 'Closed' }}
                        </span>
                    </label>

                    @if($bh['is_open'])
                        <div class="flex items-center gap-2 ml-auto">
                            <input type="time" wire:model="business_hours.{{ $i }}.open_time"
                                class="px-2 py-1 border border-teal-200 rounded-lg text-xs focus:ring-1 focus:ring-teal-500 bg-white">
                            <span class="text-xs text-gray-400">to</span>
                            <input type="time" wire:model="business_hours.{{ $i }}.close_time"
                                class="px-2 py-1 border border-teal-200 rounded-lg text-xs focus:ring-1 focus:ring-teal-500 bg-white">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-5 p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
            <strong>Almost done!</strong> After completing payment, our team will review your application within 1–3 business days. You'll receive an email notification once approved.
        </div>
    </div>
    @endif

    {{-- ═══════════ STEP 9: Choose Plan & Pay ═══════════ --}}
    @if($step === 9)
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <span class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs font-bold">9</span>
            Choose Your Plan
        </h2>
        <p class="text-xs text-gray-500 mb-5">Select a subscription plan to activate your supplier account. You can upgrade anytime.</p>

        {{-- Error banner --}}
        @if($paymentError)
        <div class="mb-4 flex items-start gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span>{{ $paymentError }}</span>
        </div>
        @endif

        {{-- ── Billing Toggle ── --}}
        @php
            $hasMonthly = $monthlyPlans->isNotEmpty();
            $hasYearly  = $yearlyPlans->isNotEmpty();
            $hasToggle  = $hasMonthly && $hasYearly;

            // Calculate average yearly saving %
            $avgSaving = 0; $pairCount = 0;
            if ($hasMonthly && $hasYearly) {
                $mp = $monthlyPlans->first();
                foreach ($yearlyPlans as $yp) {
                    if ($mp && $mp->price > 0 && $yp->price > 0) {
                        $mEquiv = $mp->price * 12;
                        $s = round((($mEquiv - $yp->price) / $mEquiv) * 100);
                        if ($s > 0) { $avgSaving += $s; $pairCount++; }
                    }
                }
            }
            $savingLabel = $pairCount > 0 ? round($avgSaving / $pairCount) : 0;
        @endphp

        @if($hasToggle)
        <div class="flex justify-center mb-5">
            <div class="inline-flex items-center bg-gray-100 rounded-xl p-1 gap-0.5">
                <button type="button"
                    wire:click="setBillingCycle('monthly')"
                    class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200
                        {{ $billingCycle === 'monthly' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                    Monthly
                </button>
                <button type="button"
                    wire:click="setBillingCycle('yearly')"
                    class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-1.5
                        {{ $billingCycle === 'yearly' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                    Yearly
                    @if($savingLabel > 0)
                        <span class="bg-emerald-500 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold">Save {{ $savingLabel }}%</span>
                    @endif
                </button>
            </div>
        </div>
        @endif

        {{-- ── Plan Cards ── --}}
        @php
            $visiblePlans = collect();
            if ($billingCycle === 'monthly') {
                $visiblePlans = $freePlans->merge($monthlyPlans)->sortBy('sort_order')->values();
            } else {
                $visiblePlans = $freePlans->merge($yearlyPlans)->sortBy('sort_order')->values();
            }
            if (! $hasMonthly && ! $hasYearly) {
                $visiblePlans = $freePlans->sortBy('sort_order')->values();
            }
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-{{ $visiblePlans->count() === 1 ? '1' : ($visiblePlans->count() === 2 ? '2' : '3') }} gap-3 mb-5">
            @foreach($visiblePlans as $plan)
            @php
                $isSelected  = $selectedPlanId === $plan->id;
                $isFeatured  = $plan->is_featured;
                // Yearly saving vs monthly
                $yearlySaving = null;
                if ($plan->isYearly() && $monthlyPlans->isNotEmpty()) {
                    $mp = $monthlyPlans->first();
                    if ($mp && $mp->price > 0 && $plan->price > 0) {
                        $mE = $mp->price * 12;
                        $sA = $mE - $plan->price;
                        if ($sA > 0) $yearlySaving = round(($sA / $mE) * 100);
                    }
                }
            @endphp
            <div
                wire:click="selectPlan({{ $plan->id }})"
                class="relative cursor-pointer rounded-2xl p-5 border-2 transition-all duration-200
                    {{ $isSelected
                        ? 'border-teal-500 bg-teal-50 shadow-md'
                        : ($isFeatured ? 'border-emerald-300 bg-white shadow hover:border-emerald-500' : 'border-gray-200 bg-white hover:border-teal-300 hover:bg-teal-50/30') }}"
            >
                {{-- Featured badge --}}
                @if($isFeatured && !$isSelected)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-emerald-500 text-white text-[9px] font-bold rounded-full uppercase tracking-wider shadow">
                    Best Value
                </span>
                @endif

                {{-- Selected badge --}}
                @if($isSelected)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-teal-500 text-white text-[9px] font-bold rounded-full uppercase tracking-wider shadow flex items-center gap-1">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    Selected
                </span>
                @endif

                {{-- Yearly saving badge --}}
                @if($yearlySaving)
                <span class="absolute top-2 right-2 bg-emerald-100 text-emerald-700 text-[9px] font-bold px-2 py-0.5 rounded-full">
                    Save {{ $yearlySaving }}%
                </span>
                @endif

                {{-- Plan type tag --}}
                <span class="inline-flex px-2 py-0.5 text-[9px] font-bold rounded-full uppercase tracking-wider mb-2
                    {{ $plan->isFree() ? 'bg-emerald-100 text-emerald-700' : ($plan->isYearly() ? 'bg-purple-100 text-purple-700' : 'bg-sky-100 text-sky-700') }}">
                    {{ ucfirst($plan->billing_type) }}
                </span>

                <h3 class="text-sm font-bold text-gray-900 mb-1">{{ $plan->name }}</h3>

                {{-- Price --}}
                @if($plan->isFree())
                    <div class="flex items-baseline mb-1">
                        <span class="text-2xl font-extrabold text-gray-900">FREE</span>
                    </div>
                    <p class="text-[10px] text-gray-500 mb-3">
                        {{ $plan->totalFreeDays() }} days free
                        @if($plan->bonus_days > 0)<span class="text-amber-600 font-semibold"> (+{{ $plan->bonus_days }}d bonus)</span>@endif
                    </p>
                @else
                    <div class="flex items-baseline gap-1 mb-1">
                        <span class="text-2xl font-extrabold text-gray-900">{{ $plan->formattedPrice() }}</span>
                        <span class="text-gray-400 text-xs">/{{ $plan->isYearly() ? 'yr' : 'mo' }}</span>
                    </div>
                    @if($plan->bonus_days > 0)
                        <p class="text-[10px] text-amber-600 font-semibold mb-3">+ {{ $plan->bonus_days }} bonus days</p>
                    @else
                        <div class="mb-3"></div>
                    @endif
                @endif

                {{-- Features --}}
                <ul class="space-y-1.5 border-t border-gray-100 pt-3 text-[11px] text-gray-600">
                    <li class="flex items-center gap-1.5">
                        <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
                        @if($plan->max_active_listings === 0) Unlimited Listings
                        @else Up to {{ number_format($plan->max_active_listings) }} Listing{{ $plan->max_active_listings > 1 ? 's' : '' }}
                        @endif
                    </li>
                    <li class="flex items-center gap-1.5">
                        <span class="text-emerald-500 font-bold flex-shrink-0">✓</span>
                        @if($plan->max_products === 0) Unlimited Products
                        @else Up to {{ number_format($plan->max_products) }} Product{{ $plan->max_products > 1 ? 's' : '' }}
                        @endif
                    </li>
                    <li class="flex items-center gap-1.5 {{ $plan->rfq_delay_minutes === 0 ? '' : 'opacity-50' }}">
                        <span class="{{ $plan->rfq_delay_minutes === 0 ? 'text-emerald-500' : 'text-gray-300' }} font-bold flex-shrink-0">✓</span>
                        @if($plan->rfq_delay_minutes === 0) Instant RFQ Access
                        @else RFQ <span class="text-gray-400">({{ $plan->rfq_delay_minutes >= 60 ? floor($plan->rfq_delay_minutes/60).'hr' : $plan->rfq_delay_minutes.'min' }} delay)</span>
                        @endif
                    </li>
                    <li class="flex items-center gap-1.5 {{ $plan->has_analytics ? '' : 'opacity-40' }}">
                        <span class="{{ $plan->has_analytics ? 'text-emerald-500' : 'text-gray-300' }} font-bold flex-shrink-0">{{ $plan->has_analytics ? '✓' : '✗' }}</span>
                        Analytics
                    </li>
                    <li class="flex items-center gap-1.5 {{ $plan->has_verified_badge ? '' : 'opacity-40' }}">
                        <span class="{{ $plan->has_verified_badge ? 'text-emerald-500' : 'text-gray-300' }} font-bold flex-shrink-0">{{ $plan->has_verified_badge ? '✓' : '✗' }}</span>
                        Verified Badge
                    </li>
                </ul>

                {{-- Free plan not eligible note --}}
                @if($plan->isFree() && !$isEligibleFree)
                <div class="mt-3 text-[10px] text-orange-600 bg-orange-50 border border-orange-200 rounded-lg px-2 py-1.5 text-center">
                    Not eligible — already claimed or had a paid plan
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- ── Card Payment Form (shown after selecting a paid plan) ── --}}
        @if($selectedPlanId)
        @php $chosenPlan = $visiblePlans->firstWhere('id', $selectedPlanId); @endphp

        @if($chosenPlan)
        <div class="border border-teal-200 bg-teal-50/40 rounded-2xl p-5" id="payment-section">

            {{-- Order Summary --}}
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-semibold text-gray-700">Order Summary</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">
                        {{ $chosenPlan->name }}
                        <span class="text-gray-400 font-normal text-xs">· {{ ucfirst($chosenPlan->billing_type) }}</span>
                    </p>
                </div>
                <div class="text-right">
                    @if($chosenPlan->isFree())
                        <p class="text-xl font-extrabold text-emerald-600">FREE</p>
                        <p class="text-[10px] text-gray-400">{{ $chosenPlan->totalFreeDays() }} days access</p>
                    @else
                        <p class="text-xl font-extrabold text-gray-900">{{ $chosenPlan->formattedPrice() }}</p>
                        <p class="text-[10px] text-gray-400">per {{ $chosenPlan->isYearly() ? 'year' : 'month' }}</p>
                    @endif
                </div>
            </div>

            @if($chosenPlan->isFree())
                {{-- Free plan CTA --}}
                @if($isEligibleFree)
                <button type="button" wire:click="activateFreePlan" wire:loading.attr="disabled"
                    class="w-full px-5 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                    <span wire:loading.remove wire:target="activateFreePlan">Start Free Trial</span>
                    <span wire:loading wire:target="activateFreePlan">Activating…</span>
                    <svg wire:loading.remove wire:target="activateFreePlan" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>
                @else
                <div class="w-full text-center px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-400 font-semibold">
                    Not eligible for free plan
                </div>
                @endif

            @else
                {{-- Paid plan: Stripe card form --}}
                @if(!$paymentIntentClientSecret)
                    <button type="button" wire:click="createPaymentIntent" wire:loading.attr="disabled"
                        class="w-full px-5 py-2.5 bg-teal-600 hover:bg-teal-700 disabled:opacity-60 text-white text-xs font-semibold rounded-xl transition flex items-center justify-center gap-2 mb-4">
                        <span wire:loading.remove wire:target="createPaymentIntent">Continue to Payment</span>
                        <span wire:loading wire:target="createPaymentIntent">Preparing…</span>
                        <svg wire:loading.remove wire:target="createPaymentIntent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @else
                    {{-- Card Element --}}
                    <div class="space-y-3" x-data="supplierPaymentComponent" x-init="init()">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                <svg class="inline w-3.5 h-3.5 mr-1 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Card Details
                            </label>
                            <div id="card-element"
                                wire:ignore
                                class="px-3 py-3 border border-gray-300 rounded-xl bg-white focus-within:ring-2 focus-within:ring-teal-500 focus-within:border-transparent"
                            ></div>
                            <div id="card-errors" class="mt-1.5 text-xs text-red-600" role="alert"></div>
                        </div>

                        <button
                            type="button"
                            id="pay-btn"
                            @click="payNow()"
                            class="w-full px-5 py-3 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-md flex items-center justify-center gap-2 disabled:opacity-60"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span id="pay-btn-text">Pay {{ $chosenPlan->formattedPrice() }} Securely</span>
                        </button>

                        <p class="text-center text-[10px] text-gray-400 flex items-center justify-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                            Secured by Stripe · Your card info is never stored on our servers
                        </p>
                    </div>
                @endif
            @endif
        </div>
        @endif
        @endif
    </div>
    @endif

    {{-- ═══════════ Navigation Buttons ═══════════ --}}
    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
        <div>
            @if($step > 1)
                <button type="button" wire:click="prevStep"
                    class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400">{{ $step }} / {{ $totalSteps }}</span>

            {{-- Steps 1–8: show Continue. Step 9: payment buttons live inside the step UI --}}
            @if($step < $totalSteps)
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                    class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition flex items-center gap-2 shadow-sm">
                    <span wire:loading.remove wire:target="nextStep">
                        {{ $step === 8 ? 'Save & Continue' : 'Continue' }}
                    </span>
                    <span wire:loading wire:target="nextStep">Saving…</span>
                    <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @endif
            {{-- On step 9, payment action buttons are embedded inside the step content above --}}
        </div>
    </div>

    {{-- ─── Stripe.js (loaded once via @assets, runs on every render via @script) ─── --}}
    @if($step === 9)
    {{-- Step 9 UI checks are complete above --}}
    @endif

    @assets
    <script src="https://js.stripe.com/v3/"></script>
    @endassets

    @script
    <script>
    (() => {
        if (window._supplierPaymentComponentRegistered) return;
        window._supplierPaymentComponentRegistered = true;

        const registerComponent = () => {
            if (window.Alpine.data('supplierPaymentComponent')) return;

            window.Alpine.data('supplierPaymentComponent', () => ({
                stripeKey: @js(config('services.stripe.key')),
                stripe: null,
                card: null,

                init() {
                    this.mountCard();
                },

                mountCard() {
                    const el = document.getElementById('card-element');
                    if (!el) return;

                    const secret = this.$wire.paymentIntentClientSecret;
                    if (!secret) return;

                    if (typeof Stripe === 'undefined') {
                        setTimeout(() => this.mountCard(), 50);
                        return;
                    }

                    if (el.dataset.stripeInit === '1') return;
                    el.dataset.stripeInit = '1';

                    this.stripe = Stripe(this.stripeKey);
                    const elements = this.stripe.elements();
                    this.card = elements.create('card', {
                        style: {
                            base: {
                                fontSize: '14px',
                                fontFamily: 'ui-sans-serif, system-ui, sans-serif',
                                color: '#1f2937',
                                '::placeholder': { color: '#9ca3af' },
                                iconColor: '#6b7280',
                            },
                            invalid: { color: '#ef4444', iconColor: '#ef4444' },
                        },
                        hidePostalCode: true,
                    });

                    this.card.mount(el);

                    this.card.on('change', (event) => {
                        const errDiv = document.getElementById('card-errors');
                        if (errDiv) errDiv.textContent = event.error ? event.error.message : '';
                    });
                },

                async payNow() {
                    const secret = this.$wire.paymentIntentClientSecret;
                    const btn = document.getElementById('pay-btn');
                    const btnText = document.getElementById('pay-btn-text');
                    const errDiv = document.getElementById('card-errors');

                    if (!this.stripe || !this.card) {
                        if (errDiv) errDiv.textContent = 'Payment form not ready — please wait a moment and try again.';
                        return;
                    }
                    if (!secret) {
                        if (errDiv) errDiv.textContent = 'Session expired. Please refresh and try again.';
                        return;
                    }

                    if (btn) btn.disabled = true;
                    if (btnText) btnText.textContent = 'Processing…';
                    if (errDiv) errDiv.textContent = '';

                    try {
                        const { paymentIntent, error } = await this.stripe.confirmCardPayment(secret, {
                            payment_method: { card: this.card },
                        });

                        if (error) {
                            if (errDiv) errDiv.textContent = error.message;
                            if (btn) btn.disabled = false;
                            if (btnText) btnText.textContent = 'Retry Payment';
                            return;
                        }

                        if (paymentIntent && paymentIntent.status === 'succeeded') {
                            if (btnText) btnText.textContent = 'Verifying…';
                            this.$wire.confirmPaymentSuccess(paymentIntent.id);
                        } else {
                            if (errDiv) errDiv.textContent = 'Payment incomplete. Please try again.';
                            if (btn) btn.disabled = false;
                            if (btnText) btnText.textContent = 'Retry Payment';
                        }
                    } catch (err) {
                        if (errDiv) errDiv.textContent = 'An error occurred during payment processing: ' + err.message;
                        if (btn) btn.disabled = false;
                        if (btnText) btnText.textContent = 'Retry Payment';
                    }
                }
            }));
        };

        if (window.Alpine) {
            registerComponent();
        } else {
            document.addEventListener('alpine:init', registerComponent);
        }
    })();
    </script>
    @endscript
</div>

