@extends('backend.layouts.admin')

@section('title', 'Certification Requests')
@section('breadcrumb', 'Achievements / Certification Requests')

@section('body')

    <x-backend.page-header title="Certification Requests" subtitle="Certificates uploaded by accounts, awaiting review." />

    <x-backend.tabs>
        <x-backend.tab :href="route('admin.certification-requests.index', array_filter(['search' => $search]))" :active="$status === ''">
            All <span class="ml-1 text-xs text-gray-400">({{ $counts['all'] }})</span>
        </x-backend.tab>
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <x-backend.tab :href="route('admin.certification-requests.index', array_filter(['status' => $value, 'search' => $search]))" :active="$status === $value">
                {{ $label }} <span class="ml-1 text-xs text-gray-400">({{ $counts[$value] }})</span>
            </x-backend.tab>
        @endforeach
    </x-backend.tabs>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search account..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
        </x-slot:toolbar>

        @if($certifications->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-certificate" title="No certification requests found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Certification</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dates</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">File</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($certifications as $cert)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $cert->account?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <p class="font-semibold text-gray-900">{{ $cert->certification_name }}</p>
                        <p class="text-xs text-gray-500">{{ $cert->certification_title }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-600">
                        {{ $cert->certification_date?->format('d M Y') ?? '—' }}
                        @if($cert->expiry_date)
                            <br><span class="text-gray-400">Expires {{ $cert->expiry_date->format('d M Y') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm">
                        @if($cert->file)
                            <a href="{{ asset('storage/'.$cert->file) }}" target="_blank" class="text-indigo-600 hover:underline text-xs"><i class="fa-solid fa-paperclip mr-1"></i>View file</a>
                        @else
                            <span class="text-gray-400 text-xs">None</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$cert->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button"
                                    @click="$dispatch('open-modal-view-certification-{{ $cert->id }}')"
                                    title="View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            @if($cert->status === 'pending')
                                <form method="POST" action="{{ route('admin.certification-requests.approve', $cert) }}" onsubmit="return confirmSwal(this, 'Approve Certification?', 'Approve {{ addslashes($cert->certification_name) }} for {{ addslashes($cert->account?->display_name) }}?', 'question', 'Yes, Approve')">
                                    @csrf
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <button type="button"
                                        title="Reject"
                                        @click="$dispatch('open-modal-reject-certification-{{ $cert->id }}')"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @else
                                <form method="POST" action="{{ route('admin.certification-requests.undo', $cert) }}" onsubmit="return confirmSwal(this, 'Undo Decision?', 'Revert {{ addslashes($cert->certification_name) }} for {{ addslashes($cert->account?->display_name) }} back to Pending?', 'question', 'Yes, Undo')">
                                    @csrf
                                    <button type="submit" title="Undo Decision (Revert to Pending)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition-colors">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$certifications" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($certifications as $cert)
        <x-backend.modal :id="'view-certification-'.$cert->id" :title="'Certification — '.$cert->certification_name" width="max-w-lg">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-gray-500">Account</dt><dd class="font-medium text-gray-900">{{ $cert->account?->display_name }}</dd></div>
                <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$cert->status" /></dd></div>
                <div><dt class="text-xs text-gray-500">Title</dt><dd class="font-medium text-gray-900">{{ $cert->certification_title }}</dd></div>
                <div><dt class="text-xs text-gray-500">Certified On</dt><dd class="font-medium text-gray-900">{{ $cert->certification_date?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Expires</dt><dd class="font-medium text-gray-900">{{ $cert->expiry_date?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $cert->approvedBy?->name ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Description</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $cert->certification_description }}</dd></div>
                @if($cert->file)
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">File</dt><dd><a href="{{ asset('storage/'.$cert->file) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Open uploaded file</a></dd></div>
                @endif
                @if($cert->rejection_reason)
                    <div class="sm:col-span-2"><dt class="text-xs text-red-500 font-semibold">Rejection Reason</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $cert->rejection_reason }}</dd></div>
                @endif
            </dl>
        </x-backend.modal>

        @if($cert->status === 'pending')
            <x-backend.modal :id="'reject-certification-'.$cert->id" title="Reject Certification">
                <form method="POST" action="{{ route('admin.certification-requests.reject', $cert) }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="Reason for Rejection" placeholder="State reason for rejecting this certification..." required />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject Certification</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
