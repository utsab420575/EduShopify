@extends('backend.layouts.admin')

@section('title', 'Document Types')
@section('breadcrumb', 'Catalog & Taxonomy / Document Types')

@section('body')

    <x-backend.page-header title="Document Types" subtitle="Catalogue of compliance and verification document types for suppliers.">
        <x-slot:actions>
            <button type="button"
                    @click="$dispatch('open-modal-create-document-type')"
                    class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i> New Document Type
            </button>
        </x-slot:actions>
    </x-backend.page-header>

    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Search document types..."
                           class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <select name="status" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(($status ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($status ?? '') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                @if(!empty($search) || !empty($status))
                    <a href="{{ route('admin.catalog.document-types.index') }}" class="text-sm font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>

        @if($documentTypes->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-file-lines" title="No document types found" description="Create a new document type to get started." /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accepted Formats</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Max Size</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sort Order</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($documentTypes as $documentType)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                                <i class="fa-solid fa-file-lines text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $documentType->name }}</p>
                                @if($documentType->description)
                                    <p class="text-xs text-gray-500 line-clamp-1 max-w-sm">{{ $documentType->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm">
                        @if(!empty($documentType->accepted_formats) && count($documentType->accepted_formats) > 0)
                            <div class="flex items-center gap-1 flex-wrap">
                                @foreach($documentType->accepted_formats as $fmt)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $fmt }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400 text-xs">Any format</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 font-medium">
                        {{ $documentType->max_size_kb ? number_format($documentType->max_size_kb).' KB' : 'Default (10MB)' }}
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-500 font-mono">
                        {{ $documentType->sort_order ?? 0 }}
                    </td>
                    <td class="px-5 py-3.5">
                        <x-backend.status-badge :status="$documentType->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Edit Button (Opens Modal) --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-document-type-{{ $documentType->id }}')"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition-colors"
                                    title="Edit Document Type">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Delete Button --}}
                            <form method="POST"
                                  action="{{ route('admin.catalog.document-types.destroy', $documentType) }}"
                                  onsubmit="return confirmSwal(this, 'Delete Document Type?', 'Are you sure you want to delete {{ addslashes($documentType->name) }}?', 'warning', 'Yes, Delete')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors"
                                        title="Delete Document Type">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
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

    {{-- Create Document Type Modal --}}
    <x-backend.modal id="create-document-type" title="New Document Type" width="max-w-xl">
        <form method="POST" action="{{ route('admin.catalog.document-types.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-backend.input name="name" label="Document Type Name" placeholder="e.g. Trade License / Certificate of Incorporation" required />
            </div>

            <div>
                <x-backend.textarea name="description" label="Description" placeholder="Specify instructions or requirements for this document..." rows="2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-backend.input name="accepted_formats" label="Accepted Formats" placeholder="pdf, jpg, png, doc, docx" hint="Comma-separated extensions" value="pdf, jpg, jpeg, png" />
                <x-backend.input name="max_size_kb" label="Max Size (KB)" type="number" placeholder="10240" hint="10240 KB = 10 MB" value="10240" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                <x-backend.input name="sort_order" label="Sort Order" type="number" value="0" />
                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                    <label for="create_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">Active / Enabled</label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                    Cancel
                </button>
                <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-plus"></i> Create Document Type
                </button>
            </div>
        </form>
    </x-backend.modal>

    {{-- Edit Document Type Modals --}}
    @foreach($documentTypes as $documentType)
        <x-backend.modal :id="'edit-document-type-'.$documentType->id" :title="'Edit Document Type: '.$documentType->name" width="max-w-xl">
            <form method="POST" action="{{ route('admin.catalog.document-types.update', $documentType) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <x-backend.input name="name" label="Document Type Name" :value="$documentType->name" required />
                </div>

                <div>
                    <x-backend.textarea name="description" label="Description" :value="$documentType->description" rows="2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-backend.input name="accepted_formats" label="Accepted Formats" hint="Comma-separated extensions" :value="collect($documentType->accepted_formats)->implode(', ')" />
                    <x-backend.input name="max_size_kb" label="Max Size (KB)" type="number" :value="$documentType->max_size_kb" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-1">
                    <x-backend.input name="sort_order" label="Sort Order" type="number" :value="$documentType->sort_order ?? 0" />
                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" name="is_active" id="edit_is_active_{{ $documentType->id }}" value="1" @checked($documentType->is_active) class="w-4 h-4 rounded border-gray-300" style="accent-color:var(--theme-primary)">
                        <label for="edit_is_active_{{ $documentType->id }}" class="text-sm font-medium text-gray-700 cursor-pointer">Active / Enabled</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-check"></i> Update Document Type
                    </button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

@endsection

