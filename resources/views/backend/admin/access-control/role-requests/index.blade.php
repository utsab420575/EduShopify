@extends('backend.layouts.admin')

@section('title', 'Role Requests')
@section('breadcrumb', 'Access Control / Role Requests')

@section('body')

    <x-backend.page-header title="Role Requests" subtitle="Custom account-specific roles awaiting approval." />

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search account..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($roleRequests->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-user-shield" title="No role requests found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested By</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($roleRequests as $roleRequest)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $roleRequest->account?->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $roleRequest->display_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $roleRequest->requestedBy?->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $roleRequest->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$roleRequest->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($roleRequest->status === 'pending')
                                <form method="POST" action="{{ route('admin.access-control.role-requests.approve', $roleRequest) }}">
                                    @csrf
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-green-600 hover:bg-green-50"><i class="fa-solid fa-check"></i></button>
                                </form>
                                <button type="button" title="Reject" @click="$dispatch('open-modal-reject-role-request-{{ $roleRequest->id }}')" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
                            @endif
                            <a href="{{ route('admin.access-control.role-requests.show', $roleRequest) }}" title="View" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$roleRequests" />
        </x-slot:pagination>
    </x-backend.table>

    @foreach($roleRequests as $roleRequest)
        @if($roleRequest->status === 'pending')
            <x-backend.modal :id="'reject-role-request-'.$roleRequest->id" title="Reject Role Request">
                <form method="POST" action="{{ route('admin.access-control.role-requests.reject', $roleRequest) }}">
                    @csrf
                    <x-backend.textarea name="reason" label="Rejection reason" required />
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                    </div>
                </form>
            </x-backend.modal>
        @endif
    @endforeach

@endsection
