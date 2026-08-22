<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Complete Your Buyer Profile</h1>
        <p class="text-gray-500 text-sm mt-1">Fill in your details. You can save a draft at any time.</p>
        <div class="mt-3 h-1.5 bg-gray-100 rounded-full">
            <div class="h-1.5 bg-indigo-500 rounded-full" style="width: 66%"></div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="completeAndContinue" novalidate enctype="multipart/form-data">
        @csrf

        {{-- Logo upload --}}
        <div class="mb-5">
            <label class="block text-xs font-semibold text-gray-700 mb-1.5 flex items-center justify-between">
                <span>Organisation Logo <span class="text-gray-400 font-normal">(optional)</span></span>
                <span class="text-[10px] text-gray-400 font-normal">(Rec: 512×512 px · Max 5MB)</span>
            </label>
            <label class="relative w-28 h-28 overflow-hidden flex flex-col items-center justify-center
                          border-2 border-dashed border-gray-300 rounded-xl cursor-pointer
                          hover:border-indigo-400 hover:bg-indigo-50 transition group bg-white">
                @if($this->safeTemporaryUrl($logo))
                    <img src="{{ $this->safeTemporaryUrl($logo) }}" class="absolute inset-0 w-full h-full object-cover">
                @else
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span class="text-[10px] text-gray-400 mt-1">Upload logo</span>
                @endif
                <input type="file" wire:model="logo" class="hidden" accept="image/*">
            </label>
            @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Display name --}}
        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">
                @if($account->isOrganization())
                    Organisation Name <span class="text-red-500">*</span>
                @else
                    Display Name <span class="text-red-500">*</span>
                @endif
            </label>
            <input type="text" wire:model.blur="display_name"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('display_name') border-red-400 @enderror"
                placeholder="{{ $account->isOrganization() ? 'School / University name' : 'Your display name' }}">
            @error('display_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Buyer Types (Dropdown Selection List with Checkboxes) --}}
        <div class="mb-5" x-data="{ open: false }" @click.outside="open = false">
            <label class="block text-xs font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                <span>Buyer Type(s) <span class="text-red-500">*</span></span>
                <span class="text-[10px] text-gray-400 font-normal">Click to select multiple</span>
            </label>

            @php
                $cleanSelected = array_values(array_filter(array_map('intval', (array) $buyer_type_ids)));
                $selectedCount = count($cleanSelected);
                $totalCount    = count($buyerTypes);
                $allSelected   = $totalCount > 0 && $selectedCount >= $totalCount;
            @endphp

            <div class="relative">
                {{-- Dropdown Trigger Box --}}
                <button type="button" @click="open = !open"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white flex items-center justify-between focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-left transition cursor-pointer @error('buyer_type_ids') border-red-400 @enderror">
                    <span class="truncate">
                        @if($allSelected)
                            <span class="font-medium text-indigo-700 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600 inline flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                All types selected ({{ $selectedCount }})
                            </span>
                        @elseif($selectedCount > 0)
                            <span class="font-medium text-indigo-700">
                                {{ $selectedCount }} of {{ $totalCount }} type(s) selected
                            </span>
                        @else
                            <span class="text-gray-400">Select buyer type(s)…</span>
                        @endif
                    </span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown Checkbox Popup List --}}
                <div x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-64 overflow-y-auto p-2 space-y-1"
                    style="display: none;">

                    {{-- Top Select All / Deselect All Option --}}
                    <div class="pb-1.5 mb-1 border-b border-gray-100">
                        <button type="button"
                            wire:click="{{ $allSelected ? 'clearAllBuyerTypes' : 'selectAllBuyerTypes' }}"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-indigo-50 transition cursor-pointer text-xs font-semibold select-none text-left {{ $allSelected ? 'bg-indigo-50/70 text-indigo-900' : 'text-gray-800' }}">
                            <div class="w-4 h-4 rounded border flex items-center justify-center transition-colors {{ $allSelected ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-300 bg-white' }}">
                                @if($allSelected)
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </div>
                            <span class="flex-1 flex items-center justify-between">
                                <span class="font-bold text-indigo-950">Select All</span>
                                <span class="text-[11px] font-normal text-indigo-600">
                                    {{ $allSelected ? 'Deselect All' : 'Select All (' . $totalCount . ')' }}
                                </span>
                            </span>
                        </button>
                    </div>

                    {{-- Individual Items --}}
                    @foreach($buyerTypes as $type)
                        <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-indigo-50/70 transition cursor-pointer text-xs font-medium text-gray-700 select-none">
                            <input type="checkbox" wire:model.live="buyer_type_ids" value="{{ $type->id }}"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition cursor-pointer">
                            <span class="flex-1 text-gray-800">{{ $type->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Selected Badges --}}
            @if($selectedCount > 0)
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    @foreach($buyerTypes as $type)
                        @if(in_array((int) $type->id, $cleanSelected, true))
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-md border border-indigo-200">
                                {{ $type->name }}
                                <button type="button" wire:click="toggleBuyerType({{ $type->id }})" class="hover:text-indigo-900 text-indigo-400 font-bold ml-0.5" title="Remove">×</button>
                            </span>
                        @endif
                    @endforeach

                    @if($selectedCount > 1)
                        <button type="button" wire:click="clearAllBuyerTypes" class="text-[11px] text-gray-400 hover:text-red-500 underline ml-1.5 transition">
                            Clear all
                        </button>
                    @endif
                </div>
            @endif

            @error('buyer_type_ids') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Organisation name (org accounts only) --}}
        @if($account->isOrganization())
        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Official Organisation Name <span class="text-gray-400 font-normal">(if different)</span>
            </label>
            <input type="text" wire:model.blur="organization_name"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Official registered name">
        </div>
        @endif

        {{-- Contact person + position --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Contact Person <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.blur="contact_person"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_person') border-red-400 @enderror"
                    placeholder="Full name">
                @error('contact_person') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Position / Title</label>
                <input type="text" wire:model.blur="position"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Procurement Manager">
            </div>
        </div>

        {{-- Email + Phone --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Contact Email <span class="text-red-500">*</span>
                </label>
                <input type="email" wire:model.blur="email"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-400 @enderror"
                    placeholder="buyer@organization.com">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Phone</label>
                <input type="tel" wire:model.blur="phone"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="+971 50 000 0000">
            </div>
        </div>

        {{-- Country + State + City --}}
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Country <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="country_id"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white @error('country_id') border-red-400 @enderror">
                    <option value="">Select country…</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->id }}">{{ $c->flag }} {{ $c->name }}</option>
                    @endforeach
                </select>
                @error('country_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">State / Region</label>
                <select wire:model.live="state_id"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                    {{ empty($states) ? 'disabled' : '' }}>
                    <option value="">{{ empty($states) ? 'Select country first' : 'Select state…' }}</option>
                    @foreach($states as $s)
                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">City</label>
                <select wire:model="city_id"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
                    {{ empty($cities) ? 'disabled' : '' }}>
                    <option value="">{{ empty($cities) ? 'Select state first' : 'Select city…' }}</option>
                    @foreach($cities as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Address + Website --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Address <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.blur="address"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address') border-red-400 @enderror"
                    placeholder="Street, building…">
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Website <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" wire:model.blur="website"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('website') border-red-400 @enderror"
                    placeholder="https://school.edu">
                @error('website') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Tax ID + Bio --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Tax ID / VAT No. <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" wire:model.blur="tax_id"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="TRN or VAT number">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">About Your Organisation <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea wire:model.blur="bio" rows="1"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Brief description…"></textarea>
            </div>
        </div>

        {{-- Procurement info --}}
        <div class="mb-6">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">Procurement Info <span class="text-gray-400 font-normal">(optional)</span></label>
            <textarea wire:model.blur="procurement_info" rows="2"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Procurement department, typical order sizes, buying cycle…"></textarea>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft"
                class="flex-1 bg-gray-100 hover:bg-gray-200 disabled:opacity-60 text-gray-700 font-medium py-3 rounded-xl transition-all duration-200 text-sm">
                <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                <span wire:loading wire:target="saveDraft">Saving…</span>
            </button>
            <button type="submit" wire:loading.attr="disabled" wire:target="completeAndContinue"
                class="flex-2 flex-grow bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-lg">
                <span wire:loading.remove wire:target="completeAndContinue">Continue to Review →</span>
                <span wire:loading wire:target="completeAndContinue" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Saving…
                </span>
            </button>
        </div>
    </form>
</div>
