@extends('backend.layouts.admin')

@section('title', 'Account Conversions')
@section('breadcrumb', 'Users & Accounts / Conversions')

@section('body')

    <x-backend.page-header title="Account Conversions" subtitle="Individual buyer accounts requesting to convert to an organization." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search account..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'revision_required' => 'Revision Required', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($conversions->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-arrows-rotate" title="No conversion requests found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Conversion</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proposed Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($conversions as $conversion)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $conversion->account?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ ucfirst($conversion->from_type) }} &rarr; {{ ucfirst($conversion->to_type) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 font-medium">{{ $conversion->proposed_display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $conversion->submitted_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$conversion->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-conversion-{{ $conversion->id }}')"
                                    title="Quick Inspect"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            @if($conversion->status === 'pending')
                                <form method="POST" action="{{ route('admin.conversions.approve', $conversion) }}" onsubmit="return confirmSwal(this, 'Approve Conversion?', 'Approve conversion of {{ addslashes($conversion->account?->display_name) }} to organization?', 'question', 'Yes, Approve')">
                                    @csrf
                                    <button type="submit" title="Approve Conversion" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <button type="button"
                                        title="Request Revision"
                                        @click="$dispatch('open-modal-revision-conversion-{{ $conversion->id }}')"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition-colors">
                                    <i class="fa-solid fa-pen-clip"></i>
                                </button>
                                <button type="button"
                                        title="Reject Conversion"
                                        @click="$dispatch('open-modal-reject-conversion-{{ $conversion->id }}')"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @endif

                            {{-- Direct Show Link --}}
                            <a href="{{ route('admin.conversions.show', $conversion) }}"
                               title="Full Review Page"
                               class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$conversions" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Modals for Conversions --}}
    @foreach($conversions as $conversion)
        {{-- Quick View Modal --}}
        <x-backend.modal :id="'view-conversion-'.$conversion->id" :title="'Conversion Request — '.$conversion->account?->display_name" width="max-w-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">{{ $conversion->account?->display_name }}</h4>
                        <p class="text-xs text-indigo-600 font-semibold">{{ ucfirst($conversion->from_type) }} &rarr; {{ ucfirst($conversion->to_type) }}</p>
                    </div>
                    <a href="{{ route('admin.conversions.show', $conversion) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                       title="Open full page view in current tab">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                    </a>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Proposed Display Name</dt><dd class="font-medium text-gray-900">{{ $conversion->proposed_display_name }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$conversion->status" /></dd></div>
                    <div><dt class="text-xs text-gray-500">Submitted At</dt><dd class="font-medium text-gray-900">{{ $conversion->submitted_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $conversion->reviewedBy?->name ?? '—' }}</dd></div>
                    @if($conversion->rejection_reason)
                        <div class="sm:col-span-2"><dt class="text-xs text-red-500 font-semibold">Rejection Reason</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $conversion->rejection_reason }}</dd></div>
                    @endif
                    @if($conversion->revision_reason)
                        <div class="sm:col-span-2"><dt class="text-xs text-amber-600 font-semibold">Revision Reason</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $conversion->revision_reason }}</dd></div>
                    @endif
                </dl>
            </div>
        </x-backend.modal>

        {{-- Request Revision Modal --}}
        @if($conversion->status === 'pending')
            <x-backend.modal :id="'revision-conversion-'.$conversion->id" title="Request Revision">
                <form method="POST" action="{{ route('admin.conversions.revision', $conversion) }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="What changes are required?" placeholder="Specify required revisions..." required />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Submit Request</button>
                    </div>
                </form>
            </x-backend.modal>

            {{-- Reject Modal --}}
            <x-backend.modal :id="'reject-conversion-'.$conversion->id" title="Reject Conversion">
                <form method="POST" action="{{ route('admin.conversions.reject', $conversion) }}" class="space-y-4">
                    @csrf
                    <x-backend.textarea name="reason" label="Reason for Rejection" placeholder="State reason for rejecting this conversion..." required />
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject Conversion</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
