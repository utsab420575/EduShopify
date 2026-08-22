@extends('backend.layouts.admin')

@section('title', $roleRequest->display_name)
@section('breadcrumb', 'Access Control / Role Requests / ' . $roleRequest->display_name)

@section('body')

    <x-backend.page-header :title="$roleRequest->display_name" :subtitle="'Requested by ' . $roleRequest->account?->display_name">
        <x-slot:actions>
            <x-backend.status-badge :status="$roleRequest->status" />
        </x-slot:actions>
    </x-backend.page-header>

    @if($roleRequest->status === 'pending')
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <form method="POST" action="{{ route('admin.access-control.role-requests.approve', $roleRequest) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Approve</button>
            </form>
            <button @click="$dispatch('open-modal-reject')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Reject</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Role Details">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Internal Name</dt><dd class="font-medium text-gray-900">{{ $roleRequest->role_name }}</dd></div>
                    <div><dt class="text-gray-500">Scope</dt><dd class="font-medium text-gray-900">{{ ucfirst($roleRequest->capability_scope) }}</dd></div>
                </dl>
                @if($roleRequest->description)
                    <p class="text-sm text-gray-600 mt-4 border-t border-gray-100 pt-4">{{ $roleRequest->description }}</p>
                @endif
            </x-backend.form-card>

            <x-backend.form-card title="Requested Permissions">
                @if(empty($roleRequest->requested_permissions))
                    <p class="text-sm text-gray-400">No permissions requested.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($roleRequest->requested_permissions as $permission)
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">{{ $permission }}</span>
                        @endforeach
                    </div>
                @endif
            </x-backend.form-card>

            @if($roleRequest->review_comment)
                <x-backend.form-card title="Review Comment"><p class="text-sm text-gray-600">{{ $roleRequest->review_comment }}</p></x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Request Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Account</dt><dd class="font-medium text-gray-900"><a href="{{ route('admin.accounts.show', $roleRequest->account) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $roleRequest->account?->display_name }}</a></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Requested By</dt><dd class="font-medium text-gray-900">{{ $roleRequest->requestedBy?->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Requested</dt><dd class="font-medium text-gray-900">{{ $roleRequest->created_at->format('d M Y') }}</dd></div>
                    @if($roleRequest->reviewed_at)
                        <div class="flex justify-between"><dt class="text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900">{{ $roleRequest->reviewedBy?->name }}</dd></div>
                    @endif
                </dl>
            </x-backend.form-card>
        </div>
    </div>

    @if($roleRequest->status === 'pending')
        <x-backend.modal id="reject" title="Reject Role Request">
            <form method="POST" action="{{ route('admin.access-control.role-requests.reject', $roleRequest) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
