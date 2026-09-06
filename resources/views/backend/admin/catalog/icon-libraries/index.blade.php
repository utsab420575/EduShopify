@extends('backend.layouts.admin')

@section('title', 'Icon Libraries')
@section('breadcrumb', 'Catalog & Taxonomy / Icon Libraries')

@section('body')

    <x-backend.page-header title="Icon Libraries" subtitle="Manage icon libraries (FontAwesome, Bootstrap Icons, Custom SVG) used for services, categories, and catalogs.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.icons.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 mr-2">
                <i class="fa-solid fa-icons mr-1.5 text-gray-500"></i>View Icons
            </a>
            <a href="{{ route('admin.catalog.icon-libraries.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i>New Library
            </a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" action="{{ route('admin.catalog.icon-libraries.index') }}" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search library name or slug..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="type" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Types</option>
                    @foreach(['CDN' => 'CDN', 'Local' => 'Local', 'Custom' => 'Custom'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($type === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                @if($search || $type)
                    <a href="{{ route('admin.catalog.icon-libraries.index') }}" class="text-xs text-gray-500 hover:text-gray-800 px-2 py-2">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>

        @if($libraries->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-icons" title="No icon libraries found" subtitle="Get started by creating your first icon library." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Icons</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CDN URL</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($libraries as $library)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-semibold text-gray-900">
                        {{ $library->name }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-500 font-mono text-xs">
                        {{ $library->slug }}
                    </td>
                    <td class="px-5 py-3.5 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $library->type === 'Local' ? 'bg-blue-50 text-blue-700' : ($library->type === 'CDN' ? 'bg-purple-50 text-purple-700' : 'bg-emerald-50 text-emerald-700') }}">
                            {{ $library->type }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-700">
                        <a href="{{ route('admin.catalog.icons.index', ['library_id' => $library->id]) }}" class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium">
                            <span>{{ $library->icons_count }}</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500 max-w-xs truncate" title="{{ $library->cdn_url }}">
                        {{ $library->cdn_url ?: '—' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <form method="POST" action="{{ route('admin.catalog.icon-libraries.toggle-active', $library) }}" class="inline">
                            @csrf
                            <button type="submit" title="Click to toggle status" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition {{ $library->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $library->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                {{ $library->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.catalog.icon-libraries.edit', $library) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100" title="Edit">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.catalog.icon-libraries.destroy', $library) }}" onsubmit="return confirm('Delete icon library {{ addslashes($library->name) }} and all associated icons?');">
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
            <x-backend.pagination :paginator="$libraries" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
