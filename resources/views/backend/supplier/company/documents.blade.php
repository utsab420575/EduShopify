@extends('backend.layouts.supplier')

@section('title', 'Documents & Verification')
@section('breadcrumb', 'Business Profile / Documents & Verification')

@section('body')

    <x-backend.page-header title="Documents & Verification" subtitle="Upload and manage required verification documents to maintain your active supplier capability." />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Upload form (Left or Top) --}}
        <div class="xl:col-span-5 space-y-6">
            <x-backend.form-card title="Upload Document" description="Upload official documents in PDF, JPG, or PNG format (up to 10MB).">
                <form method="POST" action="{{ route('supplier.company.documents.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Document Type <span class="text-red-500">*</span></label>
                        <select name="document_type_id" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select document type</option>
                            @foreach($requiredDocumentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} @if($type->is_required) (Required) @endif</option>
                            @endforeach
                            <option value="">Other / Additional Document</option>
                        </select>
                    </div>

                    <x-backend.input name="custom_name" label="Document Title (if Other)" placeholder="e.g. ISO 9001 Certificate" />
                    
                    <x-backend.input type="date" name="expires_at" label="Expiry Date (if applicable)" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Select File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 border border-gray-200 rounded-lg p-2 bg-gray-50">
                    </div>

                    <button type="submit" class="btn-primary w-full text-sm font-medium py-2.5 rounded-lg flex items-center justify-center gap-2 mt-4">
                        <i class="fa-solid fa-upload"></i> Upload Document
                    </button>
                </form>
            </x-backend.form-card>

            {{-- Required document checklist --}}
            <x-backend.form-card title="Required Verification Checklist">
                <ul class="space-y-3 text-sm">
                    @forelse($requiredDocumentTypes as $reqType)
                        @php
                            $uploaded = $documents->first(fn($d) => $d->document_type_id === $reqType->id && $d->is_current);
                        @endphp
                        <li class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-2">
                                @if($uploaded && $uploaded->isVerified())
                                    <i class="fa-solid fa-circle-check text-green-500"></i>
                                @elseif($uploaded && $uploaded->isPending())
                                    <i class="fa-solid fa-clock text-amber-500"></i>
                                @elseif($uploaded && $uploaded->isRejected())
                                    <i class="fa-solid fa-circle-xmark text-red-500"></i>
                                @else
                                    <i class="fa-regular fa-circle text-gray-300"></i>
                                @endif
                                <span class="text-gray-700 font-medium">{{ $reqType->name }}</span>
                            </div>
                            <div>
                                @if($uploaded)
                                    <x-backend.status-badge :status="$uploaded->status" />
                                @else
                                    <span class="text-xs text-red-500 font-medium">Missing</span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="text-xs text-gray-400">No specific required document types configured.</li>
                    @endforelse
                </ul>
            </x-backend.form-card>
        </div>

        {{-- Document list table --}}
        <div class="xl:col-span-7 space-y-6">
            <x-backend.form-card title="Uploaded Documents">
                @if($documents->isEmpty())
                    <x-backend.empty-state icon="fa-file-lines" title="No documents uploaded" description="Upload your business license, tax registration, or certification documents." />
                @else
                    <div class="overflow-x-auto -mx-5 -mb-5">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 bg-gray-50 border-y border-gray-100">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Document</th>
                                    <th class="px-3 py-3 font-semibold">Status</th>
                                    <th class="px-3 py-3 font-semibold">Expiry</th>
                                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($documents as $doc)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3">
                                            <p class="font-medium text-gray-900">{{ $doc->document_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $doc->original_name }} &middot; {{ $doc->file_size_kb }} KB</p>
                                            @if($doc->rejection_reason)
                                                <p class="text-xs text-red-600 mt-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $doc->rejection_reason }}</p>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <x-backend.status-badge :status="$doc->status" />
                                            @if(!$doc->is_current)
                                                <span class="block text-[10px] text-gray-400 mt-0.5">Archived version</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-xs text-gray-600">
                                            @if($doc->expires_at)
                                                <span class="{{ $doc->isExpired() ? 'text-red-600 font-semibold' : '' }}">{{ $doc->expires_at->format('d M Y') }}</span>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="p-1.5 text-gray-500 hover:text-indigo-600 rounded" title="View / Download">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </a>
                                                <form method="POST" action="{{ route('supplier.company.documents.destroy', $doc) }}" onsubmit="return confirm('Delete this document?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded" title="Delete">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-backend.form-card>
        </div>

    </div>

@endsection
