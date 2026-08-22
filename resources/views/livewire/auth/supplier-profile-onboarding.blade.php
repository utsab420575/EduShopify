<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Complete Your Supplier Profile</h1>
        <p class="text-gray-500 text-sm mt-1">Provide your business details and legal entity type. You can save a draft at any time.</p>
        <div class="mt-3 h-1.5 bg-gray-100 rounded-full">
            <div class="h-1.5 bg-indigo-500 rounded-full" style="width: 33%"></div>
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

    <form wire:submit="completeAndContinue" novalidate enctype="multipart/form-data">
        @csrf

        {{-- Display Name & Legal Name --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Company Display Name <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.blur="display_name"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('display_name') border-red-400 @enderror"
                    placeholder="Brand or public trading name">
                @error('display_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Registered Legal Name <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.blur="legal_name"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('legal_name') border-red-400 @enderror"
                    placeholder="Official registered company name">
                @error('legal_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Legal Entity Type --}}
        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Legal Entity Structure <span class="text-gray-400 font-normal">(e.g. Sole Proprietorship, Limited Company)</span>
            </label>
            <select wire:model="legal_entity_type"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                <option value="">Select legal entity type…</option>
                @foreach($legalEntityTypes as $entityType)
                    <option value="{{ $entityType }}">{{ $entityType }}</option>
                @endforeach
            </select>
        </div>

        {{-- Supplier Business Types (Multi-select Dropdown with Checkboxes) --}}
        <div class="mb-5" x-data="{ open: false }" @click.outside="open = false">
            <label class="block text-xs font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                <span>Supplier Business Type(s) <span class="text-red-500">*</span></span>
                <span class="text-[10px] text-gray-400 font-normal">Select all that apply</span>
            </label>

            @php
                $cleanSelected = array_values(array_filter(array_map('intval', (array) $supplier_type_ids)));
                $selectedCount = count($cleanSelected);
                $totalCount    = count($supplierTypes);
                $allSelected   = $totalCount > 0 && $selectedCount >= $totalCount;
            @endphp

            <div class="relative">
                <button type="button" @click="open = !open"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white flex items-center justify-between focus:ring-2 focus:ring-indigo-500 text-left transition cursor-pointer @error('supplier_type_ids') border-red-400 @enderror">
                    <span class="truncate">
                        @if($allSelected)
                            <span class="font-medium text-indigo-700 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-indigo-600 inline flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                All types selected ({{ $selectedCount }})
                            </span>
                        @elseif($selectedCount > 0)
                            <span class="font-medium text-indigo-700">{{ $selectedCount }} of {{ $totalCount }} business type(s) selected</span>
                        @else
                            <span class="text-gray-400">Select supplier type(s)…</span>
                        @endif
                    </span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

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
                            wire:click="{{ $allSelected ? 'clearAllSupplierTypes' : 'selectAllSupplierTypes' }}"
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
                    @foreach($supplierTypes as $st)
                        <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-indigo-50/70 transition cursor-pointer text-xs font-medium text-gray-700 select-none">
                            <input type="checkbox" wire:model.live="supplier_type_ids" value="{{ $st->id }}"
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition cursor-pointer">
                            <span class="flex-1 text-gray-800">{{ $st->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Selected Badges --}}
            @if($selectedCount > 0)
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    @foreach($supplierTypes as $st)
                        @if(in_array((int) $st->id, $cleanSelected, true))
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-md border border-indigo-200">
                                {{ $st->name }}
                                <button type="button" wire:click="toggleSupplierType({{ $st->id }})" class="hover:text-indigo-900 text-indigo-400 font-bold ml-0.5" title="Remove">×</button>
                            </span>
                        @endif
                    @endforeach

                    @if($selectedCount > 1)
                        <button type="button" wire:click="clearAllSupplierTypes" class="text-[11px] text-gray-400 hover:text-red-500 underline ml-1.5 transition">
                            Clear all
                        </button>
                    @endif
                </div>
            @endif

            @error('supplier_type_ids') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Contact Person & Email --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Contact Person <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.blur="contact_person"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_person') border-red-400 @enderror"
                    placeholder="Full contact name">
                @error('contact_person') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                    Contact Email <span class="text-red-500">*</span>
                </label>
                <input type="email" wire:model.blur="contact_email"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('contact_email') border-red-400 @enderror"
                    placeholder="sales@company.com">
                @error('contact_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Phone & WhatsApp --}}
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Phone Number</label>
                <input type="tel" wire:model.blur="contact_phone"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="+971 4 000 0000">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">WhatsApp Number <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="tel" wire:model.blur="whatsapp"
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
                    <option value="">{{ empty($cities) ? 'Select country/state first' : 'Select city…' }}</option>
                    @foreach($cities as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Address & Website --}}
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
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Website</label>
                <input type="text" wire:model.blur="website"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="https://company.com">
            </div>
        </div>

        {{-- Description --}}
        <div class="mb-6">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">Business Description</label>
            <textarea wire:model.blur="description" rows="3"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Overview of products, services, specialization, and capacity…"></textarea>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft"
                class="flex-1 bg-gray-100 hover:bg-gray-200 disabled:opacity-60 text-gray-700 font-medium py-3 rounded-xl transition text-sm">
                Save Draft
            </button>
            <button type="submit" wire:loading.attr="disabled" wire:target="completeAndContinue"
                class="flex-2 flex-grow bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm shadow-lg">
                Continue to Documents →
            </button>
        </div>
    </form>
</div>
