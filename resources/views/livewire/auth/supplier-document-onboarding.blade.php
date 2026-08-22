<div x-data="{ addOpen: false }" @open-add-document.window="addOpen = true" @close-add-document.window="addOpen = false">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Upload Supplier Compliance Documents</h1>
        <p class="text-gray-500 text-sm mt-1">Step 2 of 3 — Upload your required business documents for verification.</p>
        <div class="mt-3 h-1.5 bg-gray-100 rounded-full">
            <div class="h-1.5 bg-indigo-500 rounded-full" style="width: 66%"></div>
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

    {{-- Uploaded Documents --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-6">
        <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-100 bg-gray-50/60">
            <div>
                <h2 class="text-sm font-bold text-gray-900">Your Documents</h2>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    @php $requiredNames = $documentTypes->filter(fn($t) => $t->capabilityEnables->first()?->is_required)->pluck('name'); @endphp
                    @if($requiredNames->count() > 0)
                        Required: {{ $requiredNames->implode(', ') }}
                    @else
                        Add the documents needed for your supplier verification.
                    @endif
                </p>
            </div>
            <button type="button" wire:click="openAddDocument" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition shadow-sm shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Document
            </button>
        </div>

        @if($currentDocs->count() === 0)
            <div class="p-8 text-center">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-xs text-gray-500">No documents uploaded yet.</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Click "Add Document" above to get started.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($currentDocs as $doc)
                    @php
                        $type = $doc->documentType;
                        $statusMeta = match($doc->status) {
                            'verified' => ['label' => 'Verified', 'pill' => 'bg-green-100 text-green-800', 'icon' => 'check'],
                            'rejected' => ['label' => 'Rejected', 'pill' => 'bg-red-100 text-red-800', 'icon' => 'x'],
                            default    => ['label' => 'Under Review', 'pill' => 'bg-amber-100 text-amber-800', 'icon' => 'clock'],
                        };
                    @endphp
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            {{-- Status icon --}}
                            <div class="mt-0.5 shrink-0 w-7 h-7 rounded-full flex items-center justify-center
                                {{ $doc->status === 'verified' ? 'bg-green-100' : ($doc->status === 'rejected' ? 'bg-red-100' : 'bg-amber-100') }}">
                                @if($statusMeta['icon'] === 'check')
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @elseif($statusMeta['icon'] === 'x')
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $type?->name ?? 'Document' }}@if($type?->capabilityEnables->first()?->is_required)<span class="text-red-500">&nbsp;*</span>@endif
                                    </h3>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $statusMeta['pill'] }}">{{ $statusMeta['label'] }}</span>
                                </div>

                                <p class="text-xs text-gray-600 mt-1 truncate">
                                    <span class="font-medium text-gray-800">{{ $doc->original_name }}</span>
                                    <span class="text-gray-400">({{ $doc->file_size_kb }} KB)</span>
                                    @if($doc->expires_at)
                                        <span class="text-gray-500">· Expires {{ $doc->expires_at->format('d M Y') }}</span>
                                    @endif
                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline font-medium ml-1">View</a>
                                </p>
                                @if($doc->status === 'rejected' && $doc->rejection_reason)
                                    <p class="mt-1.5 p-2 bg-red-50 border border-red-100 rounded-lg text-[11px] text-red-700">
                                        <span class="font-bold">Rejection reason:</span> {{ $doc->rejection_reason }}
                                    </p>
                                @endif
                            </div>

                            <button type="button" wire:click="openAddDocument({{ $doc->document_type_id }})"
                                class="shrink-0 self-center px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg transition">
                                Re-upload
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Additional / Custom Documents --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-bold text-gray-900">Additional Supporting Documents</h2>
        </div>
        <p class="text-xs text-gray-500 mb-3">Any other certificates, licenses, or reference letters you've uploaded via "Other" appear here.</p>

        @if($customDocs->count() > 0)
            <div class="space-y-2">
                @foreach($customDocs as $doc)
                    <div class="bg-white border border-gray-200 rounded-xl p-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-xs font-semibold text-gray-900 truncate">{{ $doc->custom_name }}</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5 truncate">
                                {{ $doc->original_name }} ({{ $doc->file_size_kb }} KB)
                                @if($doc->expires_at)
                                    · Expires {{ $doc->expires_at->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase
                                {{ $doc->status === 'verified' ? 'bg-green-100 text-green-800' : ($doc->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $doc->status }}
                            </span>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-medium">View</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="border border-dashed border-gray-200 rounded-xl p-4 text-center text-xs text-gray-400">
                No additional documents uploaded yet.
            </div>
        @endif
    </div>

    {{-- Navigation --}}
    <div class="flex gap-3">
        <a href="{{ route('supplier.onboarding.profile') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 rounded-xl transition text-sm">
            ← Back to Profile
        </a>
        <button type="button" wire:click="continueToReview" wire:loading.attr="disabled"
            class="flex-2 flex-grow bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm shadow-lg">
            Continue to Application Review →
        </button>
    </div>

    {{-- Add Document Modal --}}
    <div x-show="addOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
         style="display: none;">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="addOpen = false"></div>

        <div x-show="addOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-900">Add Document</h3>
                <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Document Type Custom Dropdown --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5 flex items-center justify-between">
                        <span>Document Type <span class="text-red-500">*</span></span>
                        <span class="text-[10px] text-gray-400 font-normal">Select from compliance list</span>
                    </label>

                    @php
                        $uploadedMap = $currentDocs->keyBy('document_type_id');
                        $selectedType = ($new_document_type_id !== '' && $new_document_type_id !== 'other')
                            ? $documentTypes->firstWhere('id', (int) $new_document_type_id)
                            : null;
                        $isSelectedUploaded = $selectedType && $uploadedMap->has($selectedType->id);
                    @endphp

                    {{-- Trigger Button --}}
                    <button type="button" @click="open = !open"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white flex items-center justify-between focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-left transition cursor-pointer @error('new_document_type_id') border-red-400 @enderror">
                        <span class="flex items-center gap-2 truncate min-w-0">
                            @if($new_document_type_id === 'other')
                                <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0 inline-block"></span>
                                <span class="font-medium text-gray-900 truncate">Other (Custom Document)</span>
                                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-amber-50 text-amber-700 border border-amber-200 shrink-0">Optional</span>
                            @elseif($selectedType)
                                @if($isSelectedUploaded)
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <span class="w-2 h-2 rounded-full {{ $selectedType->capabilityEnables->first()?->is_required ? 'bg-red-500' : 'bg-amber-400' }} shrink-0 inline-block"></span>
                                @endif
                                <span class="font-medium text-gray-900 truncate">{{ $selectedType->name }}</span>
                                @if($selectedType->capabilityEnables->first()?->is_required)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-red-50 text-red-700 border border-red-200 shrink-0">Required</span>
                                @else
                                    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-amber-50 text-amber-700 border border-amber-200 shrink-0">Optional</span>
                                @endif
                                @if($isSelectedUploaded)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Uploaded</span>
                                @endif
                            @else
                                <span class="text-gray-400">Select a document type…</span>
                            @endif
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0 ml-2" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown Popup List --}}
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto p-1.5 space-y-1"
                        style="display: none;">

                        @foreach($documentTypes as $type)
                            @php
                                $isRequired = (bool) ($type->capabilityEnables->first()?->is_required);
                                $isUploaded = $uploadedMap->has($type->id);
                                $isSelected = ((string) $type->id === $new_document_type_id);
                            @endphp
                            <button type="button"
                                wire:click="$set('new_document_type_id', '{{ $type->id }}')"
                                @click="open = false"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left transition cursor-pointer text-xs font-medium {{ $isSelected ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : 'hover:bg-gray-50 text-gray-800' }}">
                                <span class="flex items-center gap-2 min-w-0 truncate">
                                    @if($isUploaded)
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0" title="Already Uploaded">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="w-5 h-5 rounded-full {{ $isRequired ? 'bg-red-50 text-red-500' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </span>
                                    @endif
                                    <span class="truncate {{ $isSelected ? 'font-bold text-indigo-950' : 'text-gray-900' }}">{{ $type->name }}</span>
                                </span>

                                <span class="flex items-center gap-1.5 shrink-0">
                                    {{-- Uploaded badge & check icon --}}
                                    @if($isUploaded)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Uploaded
                                        </span>
                                    @endif

                                    {{-- Required (Red) vs Optional (Yellow/Amber) --}}
                                    @if($isRequired)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                            Required
                                        </span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Optional
                                        </span>
                                    @endif
                                </span>
                            </button>
                        @endforeach

                        {{-- Divider --}}
                        <div class="border-t border-gray-100 my-1"></div>

                        {{-- Other (Custom Document) Option --}}
                        <button type="button"
                            wire:click="$set('new_document_type_id', 'other')"
                            @click="open = false"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-left transition cursor-pointer text-xs font-medium {{ $new_document_type_id === 'other' ? 'bg-indigo-50 text-indigo-900 border border-indigo-200' : 'hover:bg-gray-50 text-gray-800' }}">
                            <span class="flex items-center gap-2 min-w-0 truncate">
                                <span class="w-5 h-5 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </span>
                                <span class="truncate {{ $new_document_type_id === 'other' ? 'font-bold text-indigo-950' : 'text-gray-900' }}">Other (Custom Document)</span>
                            </span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                                Optional
                            </span>
                        </button>
                    </div>

                    @error('new_document_type_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                    @if($new_document_type_id !== '' && $new_document_type_id !== 'other' && $selectedType)
                        <p class="mt-1.5 text-[11px] text-gray-500 flex flex-wrap items-center gap-1.5">
                            <span>Max {{ $selectedType->max_size_kb ? round($selectedType->max_size_kb / 1024) . 'MB' : '10MB' }}</span>
                            <span>·</span>
                            <span>Formats: {{ !empty($selectedType->accepted_formats) ? implode(', ', $selectedType->accepted_formats) : 'PDF, JPG, PNG' }}</span>
                            @if($isSelectedUploaded)
                                <span class="text-emerald-700 font-medium bg-emerald-50 px-1.5 py-0.5 rounded text-[10px]">· Re-uploading will replace existing file</span>
                            @endif
                        </p>
                    @endif
                </div>

                @if($new_document_type_id === 'other')
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Document Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="new_custom_name" placeholder="e.g. ISO Certificate, Brand Authorization Letter"
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
                    <input type="date" wire:model="new_expiry"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('new_expiry') border-red-400 @enderror">
                    @error('new_expiry') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 px-5 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl">
                <button type="button" @click="addOpen = false"
                    class="flex-1 py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg transition">
                    Cancel
                </button>
                <button type="button" wire:click="addDocument" wire:loading.attr="disabled" wire:target="addDocument,new_file"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="addDocument">Upload Document</span>
                    <span wire:loading wire:target="addDocument">Uploading…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- SweetAlert2 (loaded/bound once, survives Livewire re-renders) --}}
    @assets
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endassets

    @script
    <script>
        Livewire.on('missing-required-documents', (event) => {
            const missing = Array.isArray(event) ? (event[0]?.missing ?? []) : (event.missing ?? []);
            
            const itemsHtml = missing.map(name => `
                <div style="background:#ffffff; border:1px solid #fee2e2; border-radius:10px; padding:10px 14px; margin-bottom:8px; display:flex; align-items:center; justify-content:space-between; gap:12px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                    <div style="display:flex; align-items:center; gap:10px; text-align:left; min-width:0;">
                        <div style="width:30px; height:30px; border-radius:8px; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <span style="font-size:13px; font-weight:600; color:#111827; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${name}</span>
                    </div>
                    <span style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; padding:2px 8px; border-radius:6px; font-size:10px; font-weight:700; flex-shrink:0; text-transform:uppercase; letter-spacing:0.025em;">
                        Required
                    </span>
                </div>
            `).join('');

            Swal.fire({
                icon: 'warning',
                title: '<span style="font-size:18px; font-weight:700; color:#111827;">Required Documents Missing</span>',
                html: `
                    <p style="font-size:13px; color:#6b7280; margin-bottom:16px; text-align:center;">
                        Please upload the following required compliance document${missing.length > 1 ? 's' : ''} before continuing:
                    </p>
                    <div style="max-height:220px; overflow-y:auto; padding:2px; margin-bottom:4px;">
                        ${itemsHtml}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Add Document Now',
                cancelButtonText: 'I’ll Do It Later',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#9ca3af',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl p-6 border border-gray-100',
                    confirmButton: 'px-4 py-2.5 rounded-xl font-semibold text-sm shadow-md',
                    cancelButton: 'px-4 py-2.5 rounded-xl font-semibold text-sm',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.dispatchEvent(new CustomEvent('open-add-document'));
                }
            });
        });

        Livewire.on('document-uploaded', () => {
            window.dispatchEvent(new CustomEvent('close-add-document'));
            Swal.fire({
                icon: 'success',
                title: 'Document uploaded',
                text: 'Your document was submitted and is pending review.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#4f46e5',
                timer: 2500,
                timerProgressBar: true,
            });
        });
    </script>
    @endscript
</div>
