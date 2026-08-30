@extends('backend.layouts.admin')

@section('title', 'All Roles in Permission')
@section('breadcrumb', 'Access Control / All Roles in Permission')

@section('body')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">All Roles in Permission</h1>
            <p class="text-sm text-gray-500 mt-1">Overview of default permissions mapped to each system and custom role.</p>
        </div>
        <a href="{{ route('admin.access-control.roles-in-permission.create') }}" class="btn-primary text-sm font-semibold px-4 py-2.5 rounded-xl flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Role in Permission
        </a>
    </div>

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
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($roles->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-user-shield" title="No roles found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Role</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Scope</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Permissions</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-gray-500 text-right">Action</th>
                </tr>
            </x-slot:head>
            @foreach($roles as $role)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="px-6 py-3.5">
                        <div class="font-bold text-gray-900 text-sm">{{ $role->display_name ?? $role->name }}</div>
                        <div class="text-[11px] text-gray-400 font-mono">{{ $role->name }}</div>
                    </td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $role->capability_scope === 'supplier' ? 'bg-emerald-50 text-emerald-700' : ($role->capability_scope === 'buyer' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                            {{ $role->capability_scope }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                            {{ $role->permissions->count() }} permissions
                        </span>
                    </td>
                    <td class="px-6 py-3.5 text-right">
                        <a href="{{ route('admin.access-control.roles-in-permission.edit', $role) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                            <i class="fa-regular fa-pen-to-square"></i> Edit
                        </a>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$roles" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
