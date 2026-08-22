@extends('backend.layouts.admin')

@section('title', 'Attributes')
@section('breadcrumb', 'Catalog & Taxonomy / Attributes')

@section('body')

    <x-backend.page-header title="Attributes" subtitle="Specification fields usable by categories, listings and variants.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.attributes.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Attribute</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search attributes..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
        </x-slot:toolbar>

        @if($attributes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-sliders" title="No attributes found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Input Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Values</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($attributes as $attribute)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $attribute->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ ucfirst($attribute->input_type) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $attribute->unit?->symbol ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $attribute->values_count }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$attribute->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.catalog.attributes.edit', $attribute) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            <form method="POST" action="{{ route('admin.catalog.attributes.destroy', $attribute) }}" onsubmit="return confirm('Delete this attribute?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
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
