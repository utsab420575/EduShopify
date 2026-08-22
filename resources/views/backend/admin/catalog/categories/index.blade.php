@extends('backend.layouts.admin')

@section('title', 'Categories')
@section('breadcrumb', 'Catalog & Taxonomy / Categories')

@section('body')

    <x-backend.page-header title="Categories" subtitle="Product and service taxonomy.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.categories.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Category</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search categories..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
        </x-slot:toolbar>

        @if($categories->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-layer-group" title="No categories found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Parent</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Specifications</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Subcategories</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">{{ $category->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $category->parent?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-xs text-gray-600 font-semibold uppercase">
                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ $category->type }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <a href="{{ route('admin.catalog.categories.attributes.index', $category) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $category->attributes_count > 0 ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200' }}" title="Manage Specifications">
                            <i class="fa-solid fa-sliders text-[10px]"></i>
                            <span>{{ $category->attributes_count }} {{ Str::plural('spec', $category->attributes_count) }}</span>
                        </a>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 text-center">{{ $category->children_count }}</td>
                    <td class="px-5 py-3.5">
                        <x-backend.status-badge :status="$category->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.catalog.categories.attributes.index', $category) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50" title="Specifications / Attributes">
                                <i class="fa-solid fa-sliders"></i>
                            </a>
                            <a href="{{ route('admin.catalog.categories.edit', $category) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Edit Category"><i class="fa-regular fa-pen-to-square"></i></a>
                            @if($category->children_count === 0)
                                <form method="POST" action="{{ route('admin.catalog.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50" title="Delete Category"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$categories" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
