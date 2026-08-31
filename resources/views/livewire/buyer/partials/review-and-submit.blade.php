{{-- Shared by step 2 (when there's no documents step) and step 3 — review +
     final submit. Nothing is submitted until Final Submit is actually
     clicked, mirroring the Supplier wizard's step 7 pattern. --}}
<div class="mt-6 pt-5 border-t border-gray-100">
    @error('documents') <p class="mb-3 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">{{ $message }}</p> @enderror

    @if($requiredDocsSatisfied)
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" @click="previewOpen = true"
                class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition border-2 border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview Full Application
            </button>
            <button type="button" wire:click="confirmFinalSubmit" wire:loading.attr="disabled" wire:target="confirmFinalSubmit"
                class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition shadow-sm bg-gray-900 hover:bg-gray-800 text-white disabled:opacity-60">
                <span wire:loading.remove wire:target="confirmFinalSubmit">Final Submit</span>
                <span wire:loading wire:target="confirmFinalSubmit">Submitting…</span>
            </button>
        </div>
    @else
        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
            Upload all required documents above to review and submit your application.
        </div>
    @endif
</div>

{{-- Full Application Preview Modal --}}
@if($requiredDocsSatisfied)
<div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="previewOpen = false"></div>
    <div x-show="previewOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl border border-gray-100 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Review Your Application
            </h3>
            <button type="button" @click="previewOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-5 py-4 overflow-y-auto space-y-5">
            <div>
                <h4 class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-2">Company</h4>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                    <div><dt class="text-gray-400">Display Name</dt><dd class="font-semibold text-gray-800">{{ $display_name ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Organization</dt><dd class="font-semibold text-gray-800">{{ $organization_name ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Website</dt><dd class="font-semibold text-gray-800">{{ $website ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Locations</dt><dd class="font-semibold text-gray-800">{{ count($locations) }} location{{ count($locations) === 1 ? '' : 's' }}</dd></div>
                </dl>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <h4 class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-2">Contact</h4>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                    <div><dt class="text-gray-400">Contact Person</dt><dd class="font-semibold text-gray-800">{{ $contact_person ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Email</dt><dd class="font-semibold text-gray-800">{{ $email ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Phone</dt><dd class="font-semibold text-gray-800">{{ $phone ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <h4 class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-2">Branding</h4>
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center shrink-0">
                        @if($profile_photo)
                            <img src="{{ $profile_photo->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif($existingProfilePhoto)
                            <img src="{{ Storage::url($existingProfilePhoto) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-[9px] text-gray-400">No photo</span>
                        @endif
                    </div>
                    <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center shrink-0">
                        @if($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif($existingLogo)
                            <img src="{{ Storage::url($existingLogo) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-[9px] text-gray-400">No logo</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-600">{{ $existingGalleryImages->count() + count($gallery_files) }} gallery image{{ ($existingGalleryImages->count() + count($gallery_files)) === 1 ? '' : 's' }}</p>
                </div>
            </div>

            @if($totalSteps === 3)
                <div class="pt-4 border-t border-gray-100">
                    <h4 class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-2">Documents</h4>
                    <div class="space-y-1.5">
                        @foreach($requiredDocumentTypes as $type)
                            @php($doc = $currentDocs->get($type->id))
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-700">{{ $type->name }} <span class="text-red-500">*</span></span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $doc ? ($doc->status === 'verified' ? 'bg-green-100 text-green-800' : ($doc->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800')) : 'bg-gray-100 text-gray-500' }}">
                                    {{ $doc ? ucfirst($doc->status) : 'Not uploaded' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex gap-3 px-5 py-4 border-t border-gray-100 bg-gray-50/60 rounded-b-2xl shrink-0">
            <button type="button" @click="previewOpen = false" class="flex-1 py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg transition">
                Back to Continue
            </button>
            <button type="button" wire:click="confirmFinalSubmit" wire:loading.attr="disabled" wire:target="confirmFinalSubmit"
                class="flex-1 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition disabled:opacity-60">
                <span wire:loading.remove wire:target="confirmFinalSubmit">Final Submit</span>
                <span wire:loading wire:target="confirmFinalSubmit">Submitting…</span>
            </button>
        </div>
    </div>
</div>
@endif
