@extends('backend.layouts.admin')

@section('title', 'Attribute Groups')
@section('breadcrumb', 'Catalog & Taxonomy / Attribute Groups')

@section('body')

    <x-backend.page-header title="Attribute Groups" subtitle="Specification sections used to organize technical attributes on product sheets.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.attribute-groups.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                <i class="fa-solid fa-plus mr-1.5"></i>New Attribute Group
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search attribute groups..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>

                <select name="status" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>

                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                @if($search || $status)
                    <a href="{{ route('admin.catalog.attribute-groups.index') }}" class="text-sm font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>

        @if($groups->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-layer-group" title="No attribute groups found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Group Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Attributes Count</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Sort Order</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($groups as $group)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">
                        {{ $group->name }}
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500 font-mono">{{ $group->slug }}</td>
                    <td class="px-5 py-3.5 text-xs text-gray-600 max-w-xs truncate">{{ $group->description ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $group->attributes_count }} {{ Str::plural('attribute', $group->attributes_count) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-600 text-center font-medium">{{ $group->sort_order }}</td>
                    <td class="px-5 py-3.5">
                        <x-backend.status-badge :status="$group->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <form method="POST" action="{{ route('admin.catalog.attribute-groups.toggle-active', $group) }}">
                                @csrf
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="{{ $group->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fa-solid {{ $group->is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.catalog.attribute-groups.edit', $group) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Edit Group">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.catalog.attribute-groups.destroy', $group) }}" onsubmit="return confirm('Are you sure you want to delete this attribute group?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50" title="Delete Group">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$groups" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
