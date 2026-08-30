@extends('backend.layouts.admin')

@section('title', 'Roles')
@section('breadcrumb', 'Access Control / Roles')

@section('body')

    <x-backend.page-header title="Roles" subtitle="Global roles available across the platform. Configure default permissions and assign roles.">
        <x-slot:actions>
            <a href="{{ route('admin.access-control.roles.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Role</a>
        </x-slot:actions>
    </x-backend.page-header>

    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search roles..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="scope" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Scopes</option>
                    @foreach(['platform' => 'Platform Staff', 'buyer' => 'Buyer', 'supplier' => 'Supplier', 'common' => 'Common', 'both' => 'Both (Supplier & Buyer)'] as $value => $label)
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
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Permissions</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Users</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($roles as $role)
                @php($isCoreRoot = in_array($role->name, ['super_admin', 'admin']))
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-semibold text-gray-900">{{ $role->display_name ?? $role->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $role->name }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 capitalize">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                            {{ $role->capability_scope === 'supplier' ? 'bg-emerald-50 text-emerald-700' : ($role->capability_scope === 'buyer' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                            {{ $role->capability_scope }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                            {{ $role->permissions->count() }} permissions
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $role->users_count }}</td>
                    <td class="px-5 py-3.5">
                        @if($role->is_system)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-semibold">Global System</span>
                        @else
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold">Custom</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <!-- Duplicate Role Action -->
                            <form method="POST" action="{{ route('admin.access-control.roles.duplicate', $role) }}" onsubmit="const name = prompt('Name for duplicated role:', '{{ addslashes($role->display_name ?? $role->name) }} (Copy)'); if (name) { this.querySelector('input[name=new_display_name]').value = name; return true; } return false;">
                                @csrf
                                <input type="hidden" name="new_display_name" value="">
                                <button type="submit" title="Duplicate Role" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </form>

                            <!-- Edit Role & Permissions -->
                            <a href="{{ route('admin.access-control.roles.edit', $role) }}" title="Edit Role & Permissions" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>

                            <!-- Delete Role -->
                            @if(!$isCoreRoot && $role->users_count === 0)
                                <form method="POST" action="{{ route('admin.access-control.roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete role {{ addslashes($role->display_name ?? $role->name) }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Delete Role" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
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
