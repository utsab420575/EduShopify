{{-- ── 10. Achievements & Certifications ── --}}
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden" x-data="{ open: {{ $spAccOpen('achievements', false) }} }" x-init="$watch('open', v => localStorage.setItem('sp-acc-achievements', v ? '1' : '0'))">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none cursor-pointer">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                <i class="fa-solid fa-trophy text-sm"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold text-gray-900">Achievements &amp; Certifications</p>
                <p class="text-xs text-gray-400">{{ $myAchievementClaims->count() }} achievement{{ $myAchievementClaims->count() === 1 ? '' : 's' }}, {{ $myCertifications->count() }} certification{{ $myCertifications->count() === 1 ? '' : 's' }}</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" x-cloak x-transition class="border-t border-gray-100 px-6 py-6">

        {{-- Achievements --}}
        <label class="block text-sm font-medium text-gray-700 mb-3">Achievements</label>

        @if($myAchievementClaims->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                @foreach($myAchievementClaims as $claim)
                    <div class="p-3 border border-gray-200 rounded-xl flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid {{ $claim->achievement?->badge_icon ?: 'fa-trophy' }}"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $claim->achievement?->name }}</p>
                            <p class="text-xs text-gray-400">Requested {{ $claim->requested_at?->format('d M Y') ?? $claim->created_at->format('d M Y') }}</p>
                        </div>
                        <x-backend.status-badge :status="$claim->status" />
                        @if(in_array($claim->status, ['pending', 'rejected'], true))
                            <form method="POST" action="{{ route('supplier.company.profile.achievements.undo', $claim) }}" onsubmit="return confirmSwal(this, 'Undo this request?', 'Withdraw your claim on {{ addslashes($claim->achievement?->name) }}?', 'warning', 'Yes, Undo')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Undo Request" class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer shrink-0">
                                    <i class="fa-solid fa-rotate-left text-xs"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if($availableAchievements->isNotEmpty())
            <div class="border border-gray-100 rounded-xl divide-y divide-gray-100 mb-2">
                @foreach($availableAchievements as $achievement)
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid {{ $achievement->badge_icon ?: 'fa-trophy' }} text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $achievement->name }}</p>
                                @if($achievement->description)
                                    <p class="text-xs text-gray-400 truncate">{{ $achievement->description }}</p>
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('supplier.company.profile.achievements.request', $achievement) }}" onsubmit="return confirmSwal(this, 'Request this achievement?', 'Submit a claim on {{ addslashes($achievement->name) }} for admin review?', 'question', 'Yes, Request')">
                            @csrf
                            <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 transition cursor-pointer shrink-0">
                                Request
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @elseif($myAchievementClaims->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4 border border-gray-100 rounded-xl">No achievements available to request right now.</p>
        @endif

        {{-- Certifications --}}
        <div class="mt-6 pt-6 border-t border-gray-100">
            <label class="block text-sm font-medium text-gray-700 mb-3">Certifications</label>

            @if($myCertifications->isNotEmpty())
                <div class="space-y-3 mb-4">
                    @foreach($myCertifications as $cert)
                        <div class="border border-gray-200 rounded-xl overflow-hidden" x-data="{ editing: false }">
                            <div class="p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $cert->certification_name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $cert->certification_title }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <x-backend.status-badge :status="$cert->status" />
                                        <button type="button" title="View Details"
                                                @click="$dispatch('open-modal-view-certification-{{ $cert->id }}')"
                                                class="w-7 h-7 rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                            <i class="fa-regular fa-eye text-xs"></i>
                                        </button>
                                        @if($cert->status === 'pending')
                                            <button type="button" title="Edit" @click="editing = !editing"
                                                    class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition cursor-pointer">
                                                <i class="fa-solid fa-pen text-xs"></i>
                                            </button>
                                        @endif
                                        @if(in_array($cert->status, ['pending', 'rejected'], true))
                                            <form method="POST" action="{{ route('supplier.company.profile.certifications.destroy', $cert) }}" onsubmit="return confirmSwal(this, 'Cancel this request?', '{{ addslashes($cert->certification_name) }} will be permanently removed.', 'warning', 'Yes, Cancel')">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Cancel Request" class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition cursor-pointer">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                    @if($cert->certification_date)
                                        <span>Certified {{ $cert->certification_date->format('d M Y') }}</span>
                                    @endif
                                    @if($cert->expiry_date)
                                        <span>Expires {{ $cert->expiry_date->format('d M Y') }}</span>
                                    @endif
                                    @if($cert->file)
                                        <a href="{{ asset('storage/'.$cert->file) }}" target="_blank" class="text-indigo-600 hover:underline"><i class="fa-solid fa-paperclip mr-1"></i>File</a>
                                    @endif
                                </div>
                                @if($cert->status === 'rejected' && $cert->rejection_reason)
                                    <p class="text-xs text-red-600 mt-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $cert->rejection_reason }}</p>
                                @endif
                            </div>

                            @if($cert->status === 'pending')
                                <div x-show="editing" x-transition x-cloak class="p-4 border-t border-gray-100 bg-gray-50/50">
                                    <form method="POST" action="{{ route('supplier.company.profile.certifications.update', $cert) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Certification Name <span class="text-red-500">*</span></label>
                                                <input name="certification_name" value="{{ $cert->certification_name }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-amber-300 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Certification Title <span class="text-red-500">*</span></label>
                                                <input name="certification_title" value="{{ $cert->certification_title }}" type="text" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-amber-300 outline-none">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                                            <textarea name="certification_description" rows="3" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-amber-300 outline-none">{{ $cert->certification_description }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Certification Date</label>
                                                <input name="certification_date" value="{{ $cert->certification_date?->format('Y-m-d') }}" type="date" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-amber-300 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                                                <input name="expiry_date" value="{{ $cert->expiry_date?->format('Y-m-d') }}" type="date" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-amber-300 outline-none">
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-2 mt-4">
                                            <button type="button" @click="editing = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                                            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                                <i class="fa-solid fa-floppy-disk"></i> Save
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <x-backend.modal :id="'view-certification-'.$cert->id" :title="$cert->certification_name" width="max-w-lg">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$cert->status" /></dd></div>
                                <div><dt class="text-xs text-gray-500">Title</dt><dd class="font-medium text-gray-900">{{ $cert->certification_title }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Certified On</dt><dd class="font-medium text-gray-900">{{ $cert->certification_date?->format('d M Y') ?? '—' }}</dd></div>
                                <div><dt class="text-xs text-gray-500">Expires</dt><dd class="font-medium text-gray-900">{{ $cert->expiry_date?->format('d M Y') ?? '—' }}</dd></div>
                                <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Description</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $cert->certification_description }}</dd></div>
                                @if($cert->file)
                                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">File</dt><dd><a href="{{ asset('storage/'.$cert->file) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Open uploaded file</a></dd></div>
                                @endif
                                @if($cert->status === 'rejected' && $cert->rejection_reason)
                                    <div class="sm:col-span-2"><dt class="text-xs text-red-500 font-semibold">Rejection Reason</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $cert->rejection_reason }}</dd></div>
                                @endif
                            </dl>
                        </x-backend.modal>
                    @endforeach
                </div>
            @endif

            <div class="mt-2" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-amber-600 hover:text-amber-800 transition mb-4 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Request Certification
                </button>

                <div x-show="open" x-transition x-cloak class="border border-amber-200 bg-amber-50/40 rounded-xl p-5">
                    <form method="POST" action="{{ route('supplier.company.profile.certifications.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Certification Name <span class="text-red-500">*</span></label>
                                <input name="certification_name" value="{{ old('certification_name') }}" type="text" placeholder="e.g. ISO 9001" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-amber-300 outline-none">
                                @error('certification_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Certification Title <span class="text-red-500">*</span></label>
                                <input name="certification_title" value="{{ old('certification_title') }}" type="text" placeholder="e.g. Quality Management System" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-amber-300 outline-none">
                                @error('certification_title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                            <textarea name="certification_description" rows="3" placeholder="Describe this certification" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-amber-300 outline-none">{{ old('certification_description') }}</textarea>
                            @error('certification_description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Certification Date</label>
                                <input name="certification_date" value="{{ old('certification_date') }}" type="date" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-amber-300 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                                <input name="expiry_date" value="{{ old('expiry_date') }}" type="date" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-amber-300 outline-none">
                                @error('expiry_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Proof File</label>
                            <input name="file" type="file" class="w-full text-sm text-gray-500 border border-gray-200 rounded-lg p-2 bg-white">
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer">Cancel</button>
                            <button type="button" @click="confirmSwal(() => $el.closest('form').submit(), 'Submit certification for review?', 'Your certification request will be sent to admin for approval.', 'question', 'Yes, Submit')"
                                    class="inline-flex items-center gap-2 text-sm font-medium px-5 py-2 rounded-lg text-white transition cursor-pointer" style="background:var(--theme-primary)">
                                <i class="fa-solid fa-plus"></i> Submit for Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
