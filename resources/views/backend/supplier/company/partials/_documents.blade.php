{{-- ── 8. Documents & Verification ── --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('documents', false) }}, docTypeId: '{{ old('document_type_id', '') }}' }" x-init="$watch('open', v => localStorage.setItem('sp-acc-documents', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                <i class="fa-solid fa-file-shield text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Documents & Verification</p>
                <p class="text-xs text-gray-400">{{ $documents->where('is_current', true)->count() }} document{{ $documents->where('is_current', true)->count() === 1 ? '' : 's' }} on file</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        @error('file') <p class="text-xs text-red-600 mb-3">{{ $message }}</p> @enderror

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Required Verification Checklist</p>
        <ul class="space-y-2 text-sm mb-6">
            @forelse($requiredDocumentTypes as $reqType)
                @php($uploaded = $documents->first(fn ($d) => $d->document_type_id === $reqType->id && $d->is_current))
                <li class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                    <div class="flex items-center gap-2">
                        @if($uploaded && $uploaded->isVerified())
                            <i class="fa-solid fa-circle-check text-green-500"></i>
                        @elseif($uploaded && $uploaded->isPending())
                            <i class="fa-solid fa-clock text-amber-500"></i>
                        @elseif($uploaded && $uploaded->isRejected())
                            <i class="fa-solid fa-circle-xmark text-red-500"></i>
                        @else
                            <i class="fa-regular fa-circle text-gray-300"></i>
                        @endif
                        <span class="text-gray-700 font-medium">{{ $reqType->name }}</span>
                    </div>
                    @if($uploaded)
                        <x-backend.status-badge :status="$uploaded->status" />
                    @else
                        <span class="text-xs text-red-500 font-medium">Missing</span>
                    @endif
                </li>
            @empty
                <li class="text-xs text-gray-400">No specific required document types configured.</li>
            @endforelse
        </ul>

        <form method="POST" action="{{ route('supplier.company.profile.documents.store') }}" enctype="multipart/form-data">
            @csrf

            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Upload a Document</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Document Type</label>
                    <select name="document_type_id" x-model="docTypeId" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-2 focus:ring-rose-300 outline-none">
                        <option value="">Other / Additional Document</option>
                        @foreach($requiredDocumentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}@if($type->is_required) (Required)@endif</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date (optional)</label>
                    <input name="expires_at" value="{{ old('expires_at') }}" type="date" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-rose-300 outline-none">
                </div>
            </div>

            <div class="mb-3" x-show="docTypeId === ''">
                <label class="block text-xs font-medium text-gray-600 mb-1">Document Title <span class="text-red-500">*</span></label>
                <input name="custom_name" value="{{ old('custom_name') }}" type="text" placeholder="e.g. ISO 9001 Certificate" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-rose-300 outline-none">
                @error('custom_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Select File <span class="text-red-500">*</span></label>
                <input name="file" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full text-sm text-gray-500 border border-gray-200 rounded-lg p-2 bg-gray-50">
            </div>

            <div class="flex justify-end mb-6 pb-6 border-b border-gray-100">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                    <i class="fa-solid fa-upload"></i> Upload Document
                </button>
            </div>
        </form>

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Uploaded Documents</p>
        @if($documents->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">No documents uploaded yet.</p>
        @else
            <div class="overflow-x-auto -mx-2">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-y border-gray-100">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Document</th>
                            <th class="px-3 py-2 font-semibold">Status</th>
                            <th class="px-3 py-2 font-semibold">Expiry</th>
                            <th class="px-3 py-2 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($documents as $doc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2.5">
                                    <p class="font-medium text-gray-900">{{ $doc->document_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $doc->original_name }} &middot; {{ $doc->file_size_kb }} KB</p>
                                    @if($doc->rejection_reason)
                                        <p class="text-xs text-red-600 mt-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $doc->rejection_reason }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5">
                                    <x-backend.status-badge :status="$doc->status" />
                                    @if(! $doc->is_current)
                                        <span class="block text-[10px] text-gray-400 mt-0.5">Archived version</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-xs text-gray-600">
                                    @if($doc->expires_at)
                                        <span class="{{ $doc->isExpired() ? 'text-red-600 font-semibold' : '' }}">{{ $doc->expires_at->format('d M Y') }}</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="p-1.5 text-gray-500 hover:text-indigo-600 rounded" title="View / Download">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('supplier.company.profile.documents.destroy', $doc) }}" onsubmit="return confirmSwal(this, 'Delete this document?', '', 'warning', 'Yes, delete')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded cursor-pointer" title="Delete">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
