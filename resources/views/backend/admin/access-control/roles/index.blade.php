@extends('backend.layouts.admin')

@section('title', 'Roles')
@section('breadcrumb', 'Access Control / Roles')

@section('body')

    <x-backend.page-header title="Roles" subtitle="Global roles available across the platform.">
        <x-slot:actions>
            <a href="{{ route('admin.access-control.roles.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Role</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search roles..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="scope" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Scopes</option>
                    @foreach(['platform' => 'Platform', 'buyer' => 'Buyer', 'supplier' => 'Supplier', 'common' => 'Common'] as $value => $label)
                        <option value="{{ $value }}" @selected($scope === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($roles->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-user-shield" title="No roles found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Scope</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Users</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($roles as $role)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $role->display_name ?? $role->name }}</p>
                        <p class="text-xs text-gray-400">{{ $role->name }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($role->capability_scope) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $role->users_count }}</td>
                    <td class="px-5 py-3.5">
                        @if($role->is_system)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">System</span>
                        @else
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-50" style="color:var(--theme-primary)">Custom</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.access-control.roles.edit', $role) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center {{ $role->is_system ? 'text-gray-300 cursor-not-allowed' : 'text-gray-500 hover:bg-gray-100' }}" @if($role->is_system) onclick="return false;" @endif><i class="fa-regular fa-pen-to-square"></i></a>
                            @if(!$role->is_system && $role->users_count === 0)
                                <form method="POST" action="{{ route('admin.access-control.roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$roles" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
