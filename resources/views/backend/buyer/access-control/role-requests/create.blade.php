@extends('backend.layouts.buyer')

@section('title', 'Request a Custom Role')
@section('breadcrumb', 'Organization / Role Requests / New')

@section('body')

    <x-backend.page-header title="Request a Custom Role" subtitle="An Admin will review your request before the role becomes available." />

    <form method="POST" action="{{ route('buyer.role-requests.store') }}" class="space-y-6">
        @csrf

        <x-backend.form-card title="Role Details">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-backend.input name="display_name" label="Display Name" required placeholder="e.g. Procurement Officer" />
                <x-backend.input name="role_name" label="Internal Name" required placeholder="e.g. procurement_officer" hint="Lowercase letters, numbers, dashes and underscores only." />
            </div>
            <div class="mt-4">
                <x-backend.textarea name="description" label="Why do you need this role?" required />
            </div>
        </x-backend.form-card>

        <x-backend.form-card title="Requested Permissions">
            <div class="space-y-4">
                @foreach($permissions as $group => $groupPermissions)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $group ?: 'General' }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($groupPermissions as $permission)
                                <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-100 rounded-lg px-3 py-2 cursor-pointer">
                                    <input type="checkbox" name="requested_permissions[]" value="{{ $permission->name }}" style="accent-color:var(--theme-primary)">
                                    {{ $permission->display_name ?? $permission->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-backend.form-card>

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('buyer.role-requests.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Submit Request</button>
        </div>
    </form>

@endsection
