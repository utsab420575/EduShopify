@extends('backend.layouts.admin')

@section('title', 'Document Types')
@section('breadcrumb', 'Catalog & Taxonomy / Document Types')

@section('body')

    <x-backend.page-header title="Document Types" subtitle="Catalogue of documents suppliers may be asked to upload.">
        <x-slot:actions>
            <a href="{{ route('admin.catalog.document-types.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>New Document Type</a>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        @if($documentTypes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-file-lines" title="No document types found" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accepted Formats</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Max Size</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($documentTypes as $documentType)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $documentType->name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ collect($documentType->accepted_formats)->implode(', ') ?: 'Any' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $documentType->max_size_kb ? $documentType->max_size_kb.' KB' : '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$documentType->is_active ? 'active' : 'inactive'" /></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.catalog.document-types.edit', $documentType) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-regular fa-pen-to-square"></i></a>
                            <form method="POST" action="{{ route('admin.catalog.document-types.destroy', $documentType) }}" onsubmit="return confirm('Delete this document type?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$documentTypes" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
