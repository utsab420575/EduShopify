@extends('backend.layouts.admin')

@section('title', 'Document Enables')
@section('breadcrumb', 'Catalog & Taxonomy / Document Enables')

@section('body')

    {{-- Page Header --}}
    <x-backend.page-header title="Document Enables" subtitle="Configure which compliance documents are enabled, mandatory (Required), or optional for each capability.">
        <x-slot:actions>
            <button type="button"
                    @click="$dispatch('open-modal-create-document-enable')"
                    class="btn-primary text-sm font-medium px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-plus"></i> Enable Document for Capability
            </button>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Quick Metric Pills --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                <i class="fa-solid fa-layer-group text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Total Configured</p>
                <p class="text-xl font-bold text-gray-900">{{ $totalCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                <i class="fa-solid fa-asterisk text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Required (Mandatory)</p>
                <p class="text-xl font-bold text-rose-600">{{ $requiredCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Optional Documents</p>
                <p class="text-xl font-bold text-emerald-600">{{ $optionalCount }}</p>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <x-backend.table>
        <x-slot:toolbar>
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search document name or description..."
                           class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>

                {{-- Capability Filter --}}
                <select name="capability_id" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Capabilities</option>
                    @foreach($capabilityTypes as $cap)
                        <option value="{{ $cap->id }}" @selected((string)$capabilityId === (string)$cap->id)>{{ $cap->name }} Capability</option>
                    @endforeach
                </select>

                {{-- Requirement Filter --}}
                <select name="requirement" onchange="this.form.submit()" class="focus-accent text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">All Requirements</option>
                    <option value="required" @selected($requirement === 'required')>Required (Mandatory)</option>
                    <option value="optional" @selected($requirement === 'optional')>Optional</option>
                </select>

                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                @if(!empty($search) || !empty($capabilityId) || !empty($requirement))
                    <a href="{{ route('admin.catalog.document-type-enables.index') }}" class="text-sm font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">Clear</a>
                @endif
            </form>
        </x-slot:toolbar>

        @if($enables->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-file-shield"
                                       title="No document enables found"
                                       description="Enable a document type for a capability to mandate or recommend compliance uploads." />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Capability / Role</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requirement Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Accepted Formats</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Configured Date</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($enables as $item)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    {{-- Document Type --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 flex-shrink-0">
                                <i class="fa-solid fa-file-shield text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $item->documentType?->name ?? '—' }}</p>
                                @if($item->documentType?->description)
                                    <p class="text-xs text-gray-500 line-clamp-1 max-w-sm">{{ $item->documentType->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Capability Type --}}
                    <td class="px-5 py-3.5">
                        @if($item->capabilityType)
                            @if(strtolower($item->capabilityType->code) === 'supplier')
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-store text-[10px]"></i> Supplier
                                </span>
                            @elseif(strtolower($item->capabilityType->code) === 'buyer')
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-cart-shopping text-[10px]"></i> Buyer
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ $item->capabilityType->name }}
                                </span>
                            @endif
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Requirement Status with Quick Toggle --}}
                    <td class="px-5 py-3.5">
                        <form method="POST" action="{{ route('admin.catalog.document-type-enables.toggle', $item) }}" class="inline-block">
                            @csrf
                            @if($item->is_required)
                                <button type="submit"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors inline-flex items-center gap-1 shadow-2xs"
                                        title="Click to toggle to Optional">
                                    <i class="fa-solid fa-asterisk text-[10px] text-rose-500"></i> Required (Mandatory)
                                    <i class="fa-solid fa-arrows-rotate text-[10px] ml-1 text-rose-400"></i>
                                </button>
                            @else
                                <button type="submit"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors inline-flex items-center gap-1 shadow-2xs"
                                        title="Click to toggle to Required">
                                    <i class="fa-solid fa-circle-check text-[10px] text-emerald-500"></i> Optional
                                    <i class="fa-solid fa-arrows-rotate text-[10px] ml-1 text-emerald-400"></i>
                                </button>
                            @endif
                        </form>
                    </td>

                    {{-- Accepted Formats --}}
                    <td class="px-5 py-3.5 text-sm">
                        @if(!empty($item->documentType?->accepted_formats) && count($item->documentType->accepted_formats) > 0)
                            <div class="flex items-center gap-1 flex-wrap">
                                @foreach($item->documentType->accepted_formats as $fmt)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-100 text-gray-700 border border-gray-200">
                                        {{ $fmt }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400 text-xs">Any</span>
                        @endif
                    </td>

                    {{-- Configured Date --}}
                    <td class="px-5 py-3.5 text-xs text-gray-500 font-medium">
                        {{ $item->created_at?->format('d M Y') ?? '—' }}
                    </td>

                    {{-- Row Actions --}}
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Edit Button (Opens Modal) --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-edit-document-enable-{{ $item->id }}')"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition-colors"
                                    title="Edit Configuration">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>

                            {{-- Delete Button --}}
                            <form method="POST"
                                  action="{{ route('admin.catalog.document-type-enables.destroy', $item) }}"
                                  onsubmit="return confirmSwal(this, 'Remove Document Enable?', 'Remove {{ addslashes($item->documentType?->name ?? 'this document') }} from {{ addslashes($item->capabilityType?->name ?? 'capability') }}?', 'warning', 'Yes, Remove')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors"
                                        title="Remove Enable Rule">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$enables" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Create Document Enable Modal --}}
    <x-backend.modal id="create-document-enable" title="Enable Document for Capability" width="max-w-lg">
        <form method="POST" action="{{ route('admin.catalog.document-type-enables.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="create_document_type_id" class="block text-xs font-semibold text-gray-700 mb-1">
                    Document Type <span class="text-rose-500">*</span>
                </label>
                <select name="document_type_id" id="create_document_type_id" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">Select Document Type...</option>
                    @foreach($documentTypes as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->name }} ({{ collect($doc->accepted_formats)->implode(', ') ?: 'Any format' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="create_capability_type_id" class="block text-xs font-semibold text-gray-700 mb-1">
                    Capability / Account Role <span class="text-rose-500">*</span>
                </label>
                <select name="capability_type_id" id="create_capability_type_id" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">Select Capability...</option>
                    @foreach($capabilityTypes as $cap)
                        <option value="{{ $cap->id }}">{{ $cap->name }} ({{ strtoupper($cap->code) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Requirement Type <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-rose-300 hover:bg-rose-50/40 cursor-pointer transition-colors has-checked:border-rose-500 has-checked:bg-rose-50/50">
                        <input type="radio" name="is_required" value="1" checked class="mt-0.5 text-rose-600 focus:ring-rose-500 border-gray-300">
                        <div class="ml-2.5 text-xs">
                            <span class="font-bold text-gray-900 block">Required</span>
                            <span class="text-gray-500 block text-[11px] mt-0.5">Mandatory for capability verification &amp; approval.</span>
                        </div>
                    </label>

                    <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/40 cursor-pointer transition-colors has-checked:border-emerald-500 has-checked:bg-emerald-50/50">
                        <input type="radio" name="is_required" value="0" class="mt-0.5 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                        <div class="ml-2.5 text-xs">
                            <span class="font-bold text-gray-900 block">Optional</span>
                            <span class="text-gray-500 block text-[11px] mt-0.5">Recommended or optional supporting document.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                    Cancel
                </button>
                <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-plus"></i> Enable Document
                </button>
            </div>
        </form>
    </x-backend.modal>

    {{-- Edit Document Enable Modals --}}
    @foreach($enables as $item)
        <x-backend.modal :id="'edit-document-enable-'.$item->id" :title="'Edit Document Enable: ' . ($item->documentType?->name ?? 'Document')" width="max-w-lg">
            <form method="POST" action="{{ route('admin.catalog.document-type-enables.update', $item) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_doc_{{ $item->id }}" class="block text-xs font-semibold text-gray-700 mb-1">
                        Document Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="document_type_id" id="edit_doc_{{ $item->id }}" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        @foreach($documentTypes as $doc)
                            <option value="{{ $doc->id }}" @selected($item->document_type_id === $doc->id)>
                                {{ $doc->name }} ({{ collect($doc->accepted_formats)->implode(', ') ?: 'Any format' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit_cap_{{ $item->id }}" class="block text-xs font-semibold text-gray-700 mb-1">
                        Capability / Account Role <span class="text-rose-500">*</span>
                    </label>
                    <select name="capability_type_id" id="edit_cap_{{ $item->id }}" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                        @foreach($capabilityTypes as $cap)
                            <option value="{{ $cap->id }}" @selected($item->capability_type_id === $cap->id)>
                                {{ $cap->name }} ({{ strtoupper($cap->code) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        Requirement Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-rose-300 hover:bg-rose-50/40 cursor-pointer transition-colors has-checked:border-rose-500 has-checked:bg-rose-50/50">
                            <input type="radio" name="is_required" value="1" @checked($item->is_required) class="mt-0.5 text-rose-600 focus:ring-rose-500 border-gray-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-gray-900 block">Required</span>
                                <span class="text-gray-500 block text-[11px] mt-0.5">Mandatory for capability verification &amp; approval.</span>
                            </div>
                        </label>

                        <label class="relative flex items-start p-3 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/40 cursor-pointer transition-colors has-checked:border-emerald-500 has-checked:bg-emerald-50/50">
                            <input type="radio" name="is_required" value="0" @checked(! $item->is_required) class="mt-0.5 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                            <div class="ml-2.5 text-xs">
                                <span class="font-bold text-gray-900 block">Optional</span>
                                <span class="text-gray-500 block text-[11px] mt-0.5">Recommended or optional supporting document.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-check"></i> Update Configuration
                    </button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

@endsection
