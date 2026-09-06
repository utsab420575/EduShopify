@extends('backend.layouts.admin')

@section('title', 'Achievement Requests')
@section('breadcrumb', 'Achievements / Achievement Requests')

@section('body')

    <x-backend.page-header title="Achievement Requests" subtitle="Accounts claiming a badge from the achievements catalogue." />

    <x-backend.tabs>
        <x-backend.tab :href="route('admin.achievement-requests.index', array_filter(['search' => $search]))" :active="$status === ''">
            All <span class="ml-1 text-xs text-gray-400">({{ $counts['all'] }})</span>
        </x-backend.tab>
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <x-backend.tab :href="route('admin.achievement-requests.index', array_filter(['status' => $value, 'search' => $search]))" :active="$status === $value">
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

        @if($requests->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-trophy" title="No achievement requests found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Achievement</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reviewed By</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($requests as $req)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $req->account?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fa-solid {{ $req->achievement?->badge_icon ?: 'fa-trophy' }} text-amber-500 text-xs"></i>
                            {{ $req->achievement?->name }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $req->requested_at?->format('d M Y') ?? $req->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $req->reviewedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$req->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button"
                                    @click="$dispatch('open-modal-view-achievement-request-{{ $req->id }}')"
                                    title="View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            @if($req->status === 'pending')
                                <form method="POST" action="{{ route('admin.achievement-requests.approve', $req) }}" onsubmit="return confirmSwal(this, 'Approve Claim?', 'Approve {{ addslashes($req->account?->display_name) }}\'s claim on {{ addslashes($req->achievement?->name) }}?', 'question', 'Yes, Approve')">
                                    @csrf
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.achievement-requests.reject', $req) }}" onsubmit="return confirmSwal(this, 'Reject Claim?', 'Reject {{ addslashes($req->account?->display_name) }}\'s claim on {{ addslashes($req->achievement?->name) }}?', 'warning', 'Yes, Reject')">
                                    @csrf
                                    <button type="submit" title="Reject" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.achievement-requests.undo', $req) }}" onsubmit="return confirmSwal(this, 'Undo Decision?', 'Revert {{ addslashes($req->account?->display_name) }}\'s claim on {{ addslashes($req->achievement?->name) }} back to Pending?', 'question', 'Yes, Undo')">
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
            <x-backend.pagination :paginator="$requests" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($requests as $req)
        <x-backend.modal :id="'view-achievement-request-'.$req->id" :title="'Achievement Claim — '.$req->account?->display_name" width="max-w-lg">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-gray-500">Account</dt><dd class="font-medium text-gray-900">{{ $req->account?->display_name }}</dd></div>
                <div><dt class="text-xs text-gray-500">Achievement</dt><dd class="font-medium text-gray-900">{{ $req->achievement?->name }}</dd></div>
                <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$req->status" /></dd></div>
                <div><dt class="text-xs text-gray-500">Requested At</dt><dd class="font-medium text-gray-900">{{ $req->requested_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $req->reviewedBy?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Earned At</dt><dd class="font-medium text-gray-900">{{ $req->earned_at?->format('d M Y') ?? '—' }}</dd></div>
                @if($req->achievement?->description)
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Achievement Description</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $req->achievement->description }}</dd></div>
                @endif
                @if($req->note)
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Account Note</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $req->note }}</dd></div>
                @endif
                @if($req->proof)
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Proof</dt><dd><a href="{{ asset('storage/'.$req->proof) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">View proof file</a></dd></div>
                @endif
            </dl>
        </x-backend.modal>
    @endforeach

@endsection
