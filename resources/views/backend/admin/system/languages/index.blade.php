@extends('backend.layouts.admin')

@section('title', 'Languages')
@section('breadcrumb', 'System & Settings / Languages')

@section('body')

    <x-backend.page-header title="Languages" subtitle="Languages available across the platform.">
        <x-slot:actions>
            <a href="{{ route('admin.system.languages.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Language</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($languages->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-language" title="No languages found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Language</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Direction</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Default</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($languages as $language)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $language->name }} <span class="text-gray-400">({{ $language->native_name }})</span></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $language->code }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ strtoupper($language->direction) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">
                        @if($language->is_default)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-50" style="color:var(--theme-primary)">Default</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$language->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.system.languages.edit', $language) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            @if(!$language->is_default)
                                <form method="POST" action="{{ route('admin.system.languages.destroy', $language) }}" onsubmit="return confirm('Delete this language?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

@endsection
