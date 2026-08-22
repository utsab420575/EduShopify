@extends('backend.layouts.buyer')

@section('title', 'Role Requests')
@section('breadcrumb', 'Organization / Role Requests')

@section('body')

    <x-backend.page-header title="Role Requests" subtitle="Requests for new custom roles, reviewed by an Admin.">
        <x-slot:actions>
            <a href="{{ route('buyer.role-requests.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Request
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($roleRequests->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-shield-halved" title="No role requests yet" description="Need a custom role? Request one and an Admin will review it." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin Comment</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($roleRequests as $roleRequest)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $roleRequest->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ count($roleRequest->requested_permissions) }} permissions</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $roleRequest->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$roleRequest->status" /></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $roleRequest->review_comment ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        @if($roleRequest->status === 'pending')
                            <form method="POST" action="{{ route('buyer.role-requests.cancel', $roleRequest) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600">Cancel</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

@endsection
