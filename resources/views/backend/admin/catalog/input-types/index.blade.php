@extends('backend.layouts.admin')

@section('title', 'Input Types')
@section('breadcrumb', 'Catalog & Taxonomy / Input Types')

@section('body')

    <x-backend.page-header title="Input Types" subtitle="Manage attribute input formats, choice behaviors, and multi-selection rules.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.input-types.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> New Input Type
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search input types..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="has_options" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Option Types</option>
                    <option value="1" @selected($hasOptions === '1')>Has Predefined Options</option>
                    <option value="0" @selected($hasOptions === '0')>Freeform / Direct Input</option>
                </select>
                <select name="is_multiple" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Selections</option>
                    <option value="1" @selected($isMultiple === '1')>Multiple Choice</option>
                    <option value="0" @selected($isMultiple === '0')>Single Choice</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
            </form>
        </x-slot:toolbar>

        @if($inputTypes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-sliders" title="No input types found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Input Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Options Behavior</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Attributes</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($inputTypes as $type)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-mono font-bold">
                                {{ strtoupper(substr($type->code, 0, 2)) }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $type->name }}</p>
                                <span class="text-xs font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ $type->code }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-600 max-w-xs truncate">{{ $type->description ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-xs space-y-1">
                        @if($type->has_options)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-list-check text-[10px]"></i> Predefined Choices
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">
                                Freeform Input
                            </span>
                        @endif

                        @if($type->is_multiple)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                Multi-Select
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        @if($type->attributes_count > 0)
                            <button type="button"
                                    @click="$dispatch('open-modal-attributes-input-type-{{ $type->id }}')"
                                    class="inline-flex items-center gap-1.5 font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg text-xs transition border border-indigo-100 cursor-pointer"
                                    title="Click to view all {{ $type->attributes_count }} assigned attribute(s)">
                                <i class="fa-solid fa-layer-group text-[11px]"></i> {{ $type->attributes_count }} attribute{{ $type->attributes_count > 1 ? 's' : '' }}
                            </button>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs text-gray-400 font-medium px-2.5 py-1">
                                <i class="fa-solid fa-layer-group text-[11px]"></i> 0
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <x-backend.status-badge :status="$type->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-input-type-{{ $type->id }}')"
                                    title="Quick View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            {{-- Edit Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-input-type-{{ $type->id }}')"
                                    title="Edit Input Type"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Delete Button --}}
                            @if($type->attributes_count === 0)
                                <form method="POST" action="{{ route('admin.catalog.input-types.destroy', $type) }}" onsubmit="return confirmSwal(this, 'Delete Input Type?', 'Are you sure you want to delete input type {{ addslashes($type->name) }}?', 'warning', 'Yes, Delete')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete Input Type" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled title="In use by {{ $type->attributes_count }} attribute(s)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300 cursor-not-allowed">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$inputTypes" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Modals for Input Types --}}
    @foreach($inputTypes as $type)
        {{-- 1. Quick View Modal --}}
        <x-backend.modal :id="'view-input-type-'.$type->id" :title="'Input Type — '.$type->name" width="max-w-xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">{{ $type->name }}</h4>
                        <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $type->code }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="$dispatch('close-modal-view-input-type-{{ $type->id }}'); $dispatch('open-modal-edit-input-type-{{ $type->id }}')"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            <i class="fa-regular fa-pen-to-square"></i> Edit
                        </button>
                        <a href="{{ route('admin.catalog.input-types.edit', $type) }}"
                           class="text-xs font-semibold text-gray-600 hover:text-gray-900 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors"
                           title="Open full edit page">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                        </a>
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Code</dt><dd class="font-mono text-gray-900 font-medium">{{ $type->code }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Status</dt><dd><x-backend.status-badge :status="$type->is_active ? 'active' : 'inactive'" /></dd></div>
                    <div><dt class="text-xs text-gray-500">Has Predefined Options</dt><dd class="font-medium {{ $type->has_options ? 'text-emerald-700' : 'text-gray-600' }}">{{ $type->has_options ? 'Yes (uses attribute_values)' : 'No (freeform input)' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Allows Multiple Values</dt><dd class="font-medium {{ $type->is_multiple ? 'text-purple-700' : 'text-gray-600' }}">{{ $type->is_multiple ? 'Yes (Multi-select)' : 'No (Single value)' }}</dd></div>
                    <div>
                        <dt class="text-xs text-gray-500">Attributes Using This Type</dt>
                        <dd class="mt-0.5">
                            @if($type->attributes_count > 0)
                                <button type="button"
                                        @click="$dispatch('close-modal-view-input-type-{{ $type->id }}'); $dispatch('open-modal-attributes-input-type-{{ $type->id }}')"
                                        class="font-semibold text-indigo-600 hover:underline inline-flex items-center gap-1">
                                    {{ $type->attributes_count }} attribute(s) &rarr;
                                </button>
                            @else
                                <span class="text-gray-500">0 attributes</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt class="text-xs text-gray-500">Sort Order</dt><dd class="font-medium text-gray-900">{{ $type->sort_order }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Description</dt><dd class="text-xs text-gray-700 mt-0.5">{{ $type->description ?? 'No description provided.' }}</dd></div>
                </dl>
            </div>
        </x-backend.modal>

        {{-- 2. Edit Modal --}}
        <x-backend.modal :id="'edit-input-type-'.$type->id" :title="'Edit Input Type — '.$type->name" width="max-w-2xl">
            <form method="POST" action="{{ route('admin.catalog.input-types.update', $type) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <span class="text-xs text-gray-500 font-mono">Editing: {{ $type->code }}</span>
                    <a href="{{ route('admin.catalog.input-types.edit', $type) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                       title="Open dedicated edit page">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                    </a>
                </div>

                @include('backend.admin.catalog.input-types._form', ['inputType' => $type])

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="$dispatch('close-modal-edit-input-type-{{ $type->id }}')" class="text-xs font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg">Save Changes</button>
                </div>
            </form>
        </x-backend.modal>

        {{-- 3. Assigned Attributes List Modal --}}
        <x-backend.modal :id="'attributes-input-type-'.$type->id" :title="'Attributes using '.$type->name.' ('.$type->attributes_count.')'" width="max-w-2xl">
            <div class="space-y-4">
                <p class="text-xs text-gray-500 pb-2 border-b border-gray-100">
                    Below are all catalog attributes currently configured with the <strong>{{ $type->name }}</strong> format.
                </p>

                @if($type->attributes->isEmpty())
                    <div class="text-center py-8 text-xs text-gray-500">
                        <i class="fa-solid fa-folder-open text-2xl text-gray-300 mb-2 block"></i>
                        No attributes are currently using this input type.
                    </div>
                @else
                    <div class="max-h-[380px] overflow-y-auto divide-y divide-gray-100 border border-gray-200 rounded-xl">
                        @foreach($type->attributes as $attr)
                            <div class="flex items-center justify-between p-3 hover:bg-gray-50 transition-colors">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $attr->name }}</span>
                                        <span class="text-[11px] font-mono text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ $attr->slug }}</span>
                                        @if($attr->allow_custom_value)
                                            <span class="text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 px-1.5 py-0.2 rounded" title="Allows custom 'Other' value">
                                                Allows "Other"
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <span>Section: <strong>{{ $attr->attributeGroup?->name ?? 'General (Unassigned)' }}</strong></span>
                                        <span>&bull;</span>
                                        <span>Status: <strong class="{{ $attr->is_active ? 'text-emerald-600' : 'text-gray-400' }}">{{ $attr->is_active ? 'Active' : 'Inactive' }}</strong></span>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <x-backend.status-badge :status="$attr->is_active ? 'active' : 'inactive'" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end pt-2 border-t border-gray-100">
                    <button type="button" @click="$dispatch('close-modal-attributes-input-type-{{ $type->id }}')" class="text-xs font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Close</button>
                </div>
            </div>
        </x-backend.modal>
    @endforeach

@endsection
