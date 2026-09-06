@extends('backend.layouts.admin')

@section('title', 'Icons')
@section('breadcrumb', 'Catalog & Taxonomy / Icons')

@section('body')

    <x-backend.page-header title="Icons" subtitle="Browse, search, and manage icons used for supplier services, badges, and catalog items.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.icon-libraries.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 mr-2">
                <i class="fa-solid fa-folder-tree mr-1.5 text-gray-500"></i>Libraries
            </a>
            <a href="{{ route('admin.catalog.icons.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>New Icon
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" action="{{ route('admin.catalog.icons.index') }}" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search icon name or value..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="library_id" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Libraries</option>
                    @foreach($libraries as $lib)
                        <option value="{{ $lib->id }}" @selected($libraryId == $lib->id)>{{ $lib->name }}</option>
                    @endforeach
                </select>
                <select name="icon_type" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Types</option>
                    <option value="fontawesome" @selected($iconType === 'fontawesome')>FontAwesome</option>
                    <option value="svg" @selected($iconType === 'svg')>SVG</option>
                    <option value="image_url" @selected($iconType === 'image_url')>Image URL</option>
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                @if($search || $libraryId || $iconType)
                    <a href="{{ route('admin.catalog.icons.index') }}" class="text-xs text-gray-500 hover:text-gray-800 px-2 py-2">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>

        @if($icons->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-icons" title="No icons found" subtitle="Try adjusting your search filters or add a new icon." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Preview</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Library</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value / Class</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($icons as $icon)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-indigo-600 shrink-0">
                            {!! $icon->render('w-5 h-5 text-indigo-600') !!}
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">
                        {{ $icon->name }}
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-600">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $icon->library?->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $icon->icon_type === 'fontawesome' ? 'bg-blue-50 text-blue-700' : ($icon->icon_type === 'svg' ? 'bg-emerald-50 text-emerald-700' : 'bg-purple-50 text-purple-700') }}">
                            {{ ucfirst($icon->icon_type) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500 max-w-xs truncate font-mono">
                        <span title="{{ $icon->icon_value }}">{{ Str::limit($icon->icon_value, 40) }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <form method="POST" action="{{ route('admin.catalog.icons.toggle-active', $icon) }}" class="inline">
                            @csrf
                            <button type="submit" title="Click to toggle status" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition {{ $icon->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $icon->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                {{ $icon->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.catalog.icons.edit', $icon) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Edit">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.catalog.icons.destroy', $icon) }}" onsubmit="return confirm('Delete icon {{ addslashes($icon->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50" title="Delete">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$icons" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
