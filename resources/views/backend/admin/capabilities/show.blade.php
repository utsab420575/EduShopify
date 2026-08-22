@extends('backend.layouts.admin')

@section('title', $capability->account?->display_name)
@section('breadcrumb', 'Users & Accounts / Capabilities / ' . $capability->account?->display_name)

@section('body')

    <x-backend.page-header :title="$capability->account?->display_name" :subtitle="$capability->capabilityType?->name . ' capability application'">
        <x-slot:actions>
            <x-backend.status-badge :status="$capability->status" />
        </x-slot:actions>
    </x-backend.page-header>

    @php
        $pendingDocs = $documents->where('status', '!=', 'verified');
        $blockedByDocs = $capability->status === 'pending'
            && $capability->capabilityType?->code === 'supplier'
            && $pendingDocs->isNotEmpty();
    @endphp

    @if($capability->status === 'pending')
        {{-- Pre-flight document warning --}}
        @if($blockedByDocs)
            <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 mb-6 flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 shrink-0"></i>
                <div>
                    <p class="text-sm font-bold text-amber-900">Approval blocked — unverified documents</p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        The following required documents are not yet verified. Use the <strong>Verify</strong> buttons below to verify them, then approve the capability.
                    </p>
                    <ul class="mt-2 space-y-0.5">
                        @foreach($pendingDocs as $doc)
                            <li class="text-xs text-amber-800">
                                <i class="fa-solid fa-circle-dot mr-1"></i>
                                {{ $doc->documentType?->name ?? $doc->custom_name }}
                                <span class="text-amber-600 font-medium">({{ $doc->status }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2 mb-6">
            <form method="POST" action="{{ route('admin.capabilities.approve', $capability) }}">
                @csrf
                <button type="submit"
                        class="btn-primary text-sm font-medium px-4 py-2 rounded-lg {{ $blockedByDocs ? 'opacity-60 cursor-not-allowed' : '' }}"
                        {{ $blockedByDocs ? 'title=Verify all required documents first' : '' }}>
                    Approve
                </button>
            </form>
            <button @click="$dispatch('open-modal-revision')" class="text-sm font-medium px-4 py-2 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50">Request Revision</button>
            <button @click="$dispatch('open-modal-reject')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Reject</button>
        </div>
    @elseif($capability->status === 'active')
        <div class="mb-6">
            <button @click="$dispatch('open-modal-suspend')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Suspend Capability</button>
        </div>
    @elseif($capability->status === 'suspended')
        <div class="mb-6">
            <form method="POST" action="{{ route('admin.capabilities.reactivate', $capability) }}">
                @csrf
                <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reactivate</button>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            @if($capability->capabilityType?->code === 'buyer' && $capability->account?->buyerProfile)
                @php($profile = $capability->account->buyerProfile)
                <x-backend.form-card title="Buyer Profile">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500">Display Name</dt><dd class="font-medium text-gray-900">{{ $profile->display_name }}</dd></div>
                        <div><dt class="text-gray-500">Contact Person</dt><dd class="font-medium text-gray-900">{{ $profile->contact_person ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $profile->email ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Country</dt><dd class="font-medium text-gray-900">{{ $profile->country?->name ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $profile->address ?? '—' }}</dd></div>
                    </dl>
                </x-backend.form-card>
            @endif

            @if($capability->capabilityType?->code === 'supplier' && $capability->account?->supplierProfile)
                @php($profile = $capability->account->supplierProfile)
                <x-backend.form-card title="Supplier Profile">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500">Display Name</dt><dd class="font-medium text-gray-900">{{ $profile->display_name }}</dd></div>
                        <div><dt class="text-gray-500">Legal Name</dt><dd class="font-medium text-gray-900">{{ $profile->legal_name ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Contact Email</dt><dd class="font-medium text-gray-900">{{ $profile->contact_email ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500">Country</dt><dd class="font-medium text-gray-900">{{ $profile->country?->name ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $profile->address ?? '—' }}</dd></div>
                    </dl>
                </x-backend.form-card>

                <x-backend.form-card title="Required Documents">
                    @if($documents->isEmpty())
                        <p class="text-sm text-gray-400">No documents uploaded.</p>
                    @else
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            @foreach($documents as $document)
                                <li class="flex items-center justify-between px-5 py-3.5 gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $document->documentType?->name ?? $document->custom_name }}
                                        </p>
                                        <div class="flex items-center gap-3 mt-1">
                                            <a href="{{ asset('storage/'.$document->file_path) }}"
                                               target="_blank"
                                               class="text-xs font-medium hover:underline"
                                               style="color:var(--theme-primary)">
                                                <i class="fa-regular fa-file mr-1"></i>View file
                                            </a>
                                            @if($document->expires_at)
                                                <span class="text-xs text-gray-400">
                                                    Expires {{ $document->expires_at->format('d M Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <x-backend.status-badge :status="$document->status" />
                                        @if($document->status === 'pending')
                                            <form method="POST"
                                                  action="{{ route('admin.suppliers.documents.verify', [$capability->account, $document]) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700">
                                                    <i class="fa-solid fa-check mr-1"></i> Verify
                                                </button>
                                            </form>
                                            <div x-data="{ open: false }">
                                                <button @click="open = !open"
                                                        type="button"
                                                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">
                                                    Reject
                                                </button>
                                                <div x-show="open" x-cloak
                                                     class="absolute z-20 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-3 w-64">
                                                    <form method="POST"
                                                          action="{{ route('admin.suppliers.documents.reject', [$capability->account, $document]) }}"
                                                          class="space-y-2">
                                                        @csrf
                                                        <label class="text-xs font-semibold text-gray-700">Rejection reason</label>
                                                        <textarea name="reason" required rows="2"
                                                                  class="w-full text-xs border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-red-400"
                                                                  placeholder="e.g. Document is expired or illegible"></textarea>
                                                        <div class="flex gap-2 justify-end">
                                                            <button type="button" @click="open = false"
                                                                    class="text-xs px-2 py-1 rounded-lg border border-gray-200 text-gray-500">
                                                                Cancel
                                                            </button>
                                                            <button type="submit"
                                                                    class="text-xs font-semibold px-3 py-1 rounded-lg bg-red-600 text-white hover:bg-red-700">
                                                                Confirm Reject
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @elseif($document->status === 'verified')
                                            <span class="text-xs font-semibold text-green-600 flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check"></i> Verified
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <a href="{{ route('admin.suppliers.show', $capability->account) }}"
                       class="inline-block text-xs font-medium mt-4"
                       style="color:var(--theme-primary)">
                        View full supplier profile &rarr;
                    </a>
                </x-backend.form-card>
            @endif

            @if($capability->rejection_reason)
                <x-backend.form-card title="Rejection Reason"><p class="text-sm text-gray-600">{{ $capability->rejection_reason }}</p></x-backend.form-card>
            @endif
            @if($capability->revision_reason)
                <x-backend.form-card title="Revision Reason"><p class="text-sm text-gray-600">{{ $capability->revision_reason }}</p></x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Application Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Attempts</dt><dd class="font-medium text-gray-900">{{ $capability->application_attempts }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Applied</dt><dd class="font-medium text-gray-900">{{ $capability->applied_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Applied By</dt><dd class="font-medium text-gray-900">{{ $capability->appliedBy?->name ?? '—' }}</dd></div>
                    @if($capability->reviewed_at)
                        <div class="flex justify-between"><dt class="text-gray-500">Reviewed</dt><dd class="font-medium text-gray-900">{{ $capability->reviewed_at->format('d M Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $capability->reviewedBy?->name }}</dd></div>
                    @endif
                </dl>
            </x-backend.form-card>

            <x-backend.form-card title="History">
                @if($capability->applicationHistory->isEmpty())
                    <p class="text-sm text-gray-400">No prior attempts.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($capability->applicationHistory as $entry)
                            <li class="text-sm">
                                <div class="flex justify-between"><span class="text-gray-700">Attempt #{{ $entry->attempt_no }}</span><x-backend.status-badge :status="$entry->status" /></div>
                                @if($entry->review_comment)<p class="text-xs text-gray-500 mt-0.5">{{ $entry->review_comment }}</p>@endif
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $entry->created_at->format('d M Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-backend.form-card>
        </div>
    </div>

    @if($capability->status === 'pending')
        <x-backend.modal id="revision" title="Request Revision">
            <form method="POST" action="{{ route('admin.capabilities.revision', $capability) }}">
                @csrf
                <x-backend.textarea name="reason" label="What needs to change?" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Send</button>
                </div>
            </form>
        </x-backend.modal>
        <x-backend.modal id="reject" title="Reject Application">
            <form method="POST" action="{{ route('admin.capabilities.reject', $capability) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason for rejection" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                </div>
            </form>
        </x-backend.modal>
    @elseif($capability->status === 'active')
        <x-backend.modal id="suspend" title="Suspend Capability">
            <form method="POST" action="{{ route('admin.capabilities.suspend', $capability) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Suspend</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
