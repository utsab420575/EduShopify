@extends('backend.layouts.buyer')

@section('title', 'Roles & Permissions')
@section('breadcrumb', 'Organization / Roles & Permissions')

@section('body')

    <x-backend.page-header title="Roles & Permissions" subtitle="Roles available to assign within your organization.">
        <x-slot:actions>
            <a href="{{ route('buyer.permissions.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">View Permissions</a>
            <a href="{{ route('buyer.role-requests.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Request Custom Role
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($roles->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-shield-halved" title="No roles available" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Scope</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($roles as $role)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $role->display_name ?? $role->name }}</p>
                        @if($role->description)<p class="text-xs text-gray-400">{{ Str::limit($role->description, 60) }}</p>@endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($role->capability_scope) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $role->isGlobal() ? 'Global' : 'Custom' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $role->users_count }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('buyer.roles.show', $role) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-eye"></i></a>
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

@endsection
