@extends('backend.layouts.admin')

@section('title', 'Attributes')
@section('breadcrumb', 'Catalog & Taxonomy / Attributes')

@section('body')

    <x-backend.page-header title="Attributes" subtitle="Specification fields usable by categories, listings, and variants.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.attributes.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                <i class="fa-solid fa-plus mr-1.5"></i>New Attribute
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[180px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search attributes..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>

                {{-- Group Filter --}}
                <select name="group_id" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white max-w-[180px]">
                    <option value="">All Groups</option>
                    @foreach($attributeGroups as $group)
                        <option value="{{ $group->id }}" @selected($groupId == $group->id)>{{ $group->name }}</option>
                    @endforeach
                </select>

                {{-- Input Type Filter --}}
                <select name="input_type" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Types</option>
                    <option value="text" @selected($inputType === 'text')>Text</option>
                    <option value="textarea" @selected($inputType === 'textarea')>Textarea</option>
                    <option value="number" @selected($inputType === 'number')>Number</option>
                    <option value="select" @selected($inputType === 'select')>Select</option>
                    <option value="multi_select" @selected($inputType === 'multi_select')>Multi-Select</option>
                    <option value="boolean" @selected($inputType === 'boolean')>Yes / No</option>
                    <option value="date" @selected($inputType === 'date')>Date</option>
                    <option value="color" @selected($inputType === 'color')>Color</option>
                </select>

                {{-- Unit Filter --}}
                <select name="unit_id" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white max-w-[140px]">
                    <option value="">All Units</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected($unitId == $unit->id)>{{ $unit->name }} ({{ $unit->symbol }})</option>
                    @endforeach
                </select>

                {{-- Status Filter --}}
                <select name="status" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>

                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                @if($search || $groupId || $inputType || $unitId || $status)
                    <a href="{{ route('admin.catalog.attributes.index') }}" class="text-sm font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>

        @if($attributes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-sliders" title="No attributes found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Attribute Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Group Section</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Input Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Predefined Values</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Default Behavior</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($attributes as $attribute)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <div class="font-semibold text-sm text-gray-900">{{ $attribute->name }}</div>
                        <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $attribute->slug }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-xs">
                        @if($attribute->attributeGroup)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                <i class="fa-solid fa-layer-group text-[10px] text-gray-400"></i>
                                {{ $attribute->attributeGroup->name }}
                            </span>
                        @else
                            <span class="text-gray-400 italic">— None —</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-xs font-medium text-gray-700">
                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100 uppercase text-[10px] font-bold">
                            {{ str_replace('_', ' ', $attribute->input_type) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        {{ $attribute->unit ? ($attribute->unit->name . ' (' . $attribute->unit->symbol . ')') : '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if(in_array($attribute->input_type, ['select', 'multi_select', 'color']))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $attribute->values_count }} {{ Str::plural('option', $attribute->values_count) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">N/A (Free input)</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-xs">
                        <div class="flex flex-wrap gap-1">
                            @if($attribute->is_required)
                                <span class="px-1.5 py-0.5 rounded bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold">Required</span>
                            @endif
                            @if($attribute->is_filterable)
                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-semibold">Filterable</span>
                            @endif
                            @if($attribute->is_variant)
                                <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-semibold">Variant</span>
                            @endif
                            @if(!$attribute->is_required && !$attribute->is_filterable && !$attribute->is_variant)
                                <span class="text-gray-400 italic">Standard</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <x-backend.status-badge :status="$attribute->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.catalog.attributes.edit', $attribute) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Edit Attribute">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.catalog.attributes.destroy', $attribute) }}" onsubmit="return confirm('Delete this attribute?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50" title="Delete Attribute">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$attributes" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
