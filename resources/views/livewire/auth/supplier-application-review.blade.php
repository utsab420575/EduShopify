<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Review & Submit Application</h1>
        <p class="text-gray-500 text-sm mt-1">Step 3 of 3 — Confirm your information before submitting for Admin review.</p>
        <div class="mt-3 h-1.5 bg-gray-100 rounded-full">
            <div class="h-1.5 bg-indigo-500 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    @error('submission')
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            {{ $message }}
        </div>
    @enderror

    <div class="space-y-6 mb-6">
        {{-- Profile Summary --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3 mb-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Supplier Profile</h3>
                <a href="{{ route('supplier.onboarding.profile') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Edit Profile</a>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-500">Display Name:</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $profile->display_name }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Legal Name:</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $profile->legal_name }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Legal Structure:</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $profile->legal_entity_type ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Contact Person:</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $profile->contact_person }} ({{ $profile->contact_email }})</p>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-500">Business Type(s):</span>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @foreach($account->supplierTypes as $st)
                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-md font-medium border border-indigo-200">
                                {{ $st->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-500">Address:</span>
                    <p class="font-medium text-gray-900 mt-0.5">{{ $profile->address }}, {{ $profile->country?->name }}</p>
                </div>
            </div>
        </div>

        {{-- Documents Summary --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between border-b pb-3 mb-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Uploaded Compliance Documents</h3>
                <a href="{{ route('supplier.onboarding.documents') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Manage Documents</a>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($docs as $doc)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-semibold text-gray-900">{{ $doc->documentType?->name }}</span>
                            <p class="text-gray-500">{{ $doc->original_name }} ({{ $doc->file_size_kb }} KB)</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full font-medium {{ $doc->status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ ucfirst($doc->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Submission Action --}}
    <div class="flex gap-3">
        <a href="{{ route('supplier.onboarding.documents') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 rounded-xl transition text-sm">
            ← Back to Documents
        </a>
        <button type="button" wire:click="submitApplication" wire:loading.attr="disabled"
            class="flex-2 flex-grow bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm shadow-lg">
            Submit Application for Review ✓
        </button>
    </div>
</div>
