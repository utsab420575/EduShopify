@extends('backend.layouts.buyer')

@php
    $isEdit = (bool) $existingItem;
@endphp

@section('title', $isEdit ? 'Edit Requirement (Quotation Only)' : 'Add Requirement (Quotation Only)')
@section('breadcrumb', 'Procurement / RFQs / ' . ($isEdit ? 'Edit Requirement' : 'Add Requirement'))

@push('styles')
<style>
    .dropzone-hover {
        border-color: var(--theme-primary, #4f46e5) !important;
        background-color: var(--theme-primary-soft, #eef2ff) !important;
    }
</style>
@endpush

@section('body')
@php
    $cancelUrl = $returnUrl;
    $sep = str_contains($cancelUrl, '?') ? '&' : '?';
    $cancelUrl .= $sep . 'restore_items=1';

    // Pre-fill state for edit mode — category ids come pre-resolved from
    // the controller (RequirementController::resolveCategoryIds()); specs
    // just needs the requirement-metadata keys filtered back out.
    $initialSpecs = [];
    if ($isEdit) {
        $specs = is_array($existingItem->specs) ? $existingItem->specs : [];
        foreach ($specs as $s) {
            if (is_array($s) && ! in_array($s['name'] ?? '', ['__is_requirement', '__category_ids'], true)) {
                $initialSpecs[] = ['name' => $s['name'] ?? '', 'value' => $s['value'] ?? ''];
            }
        }
    }
    if (empty($initialSpecs)) {
        $initialSpecs[] = ['name' => '', 'value' => ''];
    }
@endphp
<div x-data="requirementForm({
        categoryNodes: {{ json_encode($categoryNodes) }},
        initialCategoryIds: {{ json_encode($existingCategoryIds) }},
        existingAttachments: {{ json_encode($existingAttachments) }},
        deleteAttachmentUrlBase: {{ json_encode($isEdit ? url('/buyer/rfqs/requirements/' . $existingItem->id . '/attachments') : '') }},
        csrfToken: {{ json_encode(csrf_token()) }},
    })"
     x-init="init()"
     class="pb-16 w-full">

    {{-- Top Navigation & Header Bar --}}
    <x-backend.page-header :title="$isEdit ? 'Edit Requirement' : 'Add Requirement'" subtitle="Request quotations for custom products, services, fabrication, or project scopes without selecting a catalog product.">
        <x-slot:actions>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    <i class="fa-solid fa-file-invoice text-gray-500 text-[11px]"></i>
                    Quotation Only
                </span>
                <a href="{{ $cancelUrl }}" class="px-4 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px] text-gray-500"></i>
                    <span>Cancel &amp; Back to RFQ</span>
                </a>
                <button type="button" @click="submitForm()" :disabled="isSubmitting"
                        class="btn-primary px-5 py-2 text-xs font-bold rounded-lg shadow-sm flex items-center gap-2 transition-colors disabled:opacity-60">
                    <i class="fa-solid fa-check" x-show="!isSubmitting"></i>
                    <i class="fa-solid fa-spinner fa-spin" x-show="isSubmitting" x-cloak></i>
                    <span x-text="isSubmitting ? 'Saving…' : '{{ $isEdit ? 'Save Changes' : 'Add Requirement to RFQ' }}'"></span>
                </button>
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Error Banner if validation fails --}}
    <template x-if="errorMessage">
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-red-500 text-sm"></i>
            <span x-text="errorMessage"></span>
        </div>
    </template>

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">
            <p class="font-bold mb-1">Please fix the following issues:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Form Grid --}}
    <form id="requirementForm" method="POST"
          action="{{ $isEdit ? route('buyer.rfqs.update-requirement', $existingItem) : route('buyer.rfqs.store-requirement') }}"
          enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        <input type="hidden" name="return_url" value="{{ $returnUrl }}">
        @if($rfqId)
            <input type="hidden" name="rfq_id" value="{{ $rfqId }}">
        @endif
        <template x-for="id in categoryIds" :key="id">
            <input type="hidden" name="category_ids[]" :value="id">
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- ══════════════════════════════════════════════════════════
                 LEFT COLUMN: Title, Type, Category, Scope, Files, Specs (8 cols)
                 ══════════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- CARD 1: Requirement Identity --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 sm:p-6 space-y-5">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold" style="background: var(--theme-primary-soft, #eef2ff); color: var(--theme-primary, #4f46e5);">1</span>
                            <h2 class="text-sm font-bold text-gray-900">Requirement Overview</h2>
                        </div>
                        <span class="text-[11px] text-gray-400">Essential Details</span>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                            Requirement Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="item_name" x-model="title" required
                               placeholder="e.g. Science Laboratory Fume Hood & Chemical Storage Unit"
                               class="focus-accent w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm bg-white font-medium placeholder-gray-400">
                        <p class="text-[11px] text-gray-400 mt-1">A concise, descriptive name for the product, work, or service you are requesting.</p>
                    </div>

                    {{-- Type Toggle & Category in 2 columns --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Type Selector --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                Requirement Nature <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-lg">
                                <button type="button" @click="itemType = 'product'"
                                        class="py-2 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-1.5"
                                        :class="itemType === 'product' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-700'">
                                    <i class="fa-solid fa-box text-[11px]" style="color: var(--theme-primary, #4f46e5);"></i> Product / Goods
                                </button>
                                <button type="button" @click="itemType = 'service'"
                                        class="py-2 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-1.5"
                                        :class="itemType === 'service' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-700'">
                                    <i class="fa-solid fa-screwdriver-wrench text-[11px]" style="color: var(--theme-primary, #4f46e5);"></i> Service / Project
                                </button>
                            </div>
                            <input type="hidden" name="item_type" :value="itemType">
                        </div>

                        {{-- Category Selector — searchable hierarchy-path tree, multi-select --}}
                        <div class="relative" @click.away="categoryPickerOpen = false">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                Relevant Categories <span class="text-gray-400 font-normal normal-case">(optional)</span>
                            </label>

                            {{-- Selected chips --}}
                            <div x-show="categoryIds.length > 0" x-cloak class="flex flex-wrap gap-1.5 mb-1.5">
                                <template x-for="id in categoryIds" :key="id">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium pl-2 pr-1 py-1 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <span x-text="getCategoryNode(id) ? getCategoryNode(id).name : ('#' + id)"></span>
                                        <button type="button" @click="toggleCategoryId(id)" class="text-indigo-400 hover:text-red-600 p-0.5 rounded">
                                            <i class="fa-solid fa-xmark text-[10px]"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>

                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400 text-xs">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" x-model="categorySearch" @focus="categoryPickerOpen = true"
                                       placeholder="Search category (e.g. Electronics > Laptops)..."
                                       class="focus-accent w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg text-xs bg-white">

                                <div x-show="categoryPickerOpen" x-cloak
                                     class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto overscroll-contain p-1 [scrollbar-gutter:stable]">
                                    <div class="space-y-0.5">
                                        <template x-for="node in filteredCategoryNodes().slice(0, 50)" :key="node.id">
                                            <button type="button" @click="toggleCategoryId(node.id)"
                                                    class="w-full flex items-center justify-between gap-2 py-2.5 pr-3 text-left text-xs hover:bg-indigo-50 rounded-md transition-colors"
                                                    :class="isCategorySelected(node.id) ? 'bg-indigo-50/70' : ''"
                                                    :style="'padding-left:' + (10 + node.depth * 14) + 'px'"
                                                    :title="node.path">
                                                <span class="flex items-center gap-2 min-w-0">
                                                    <span class="w-4 h-4 rounded border flex items-center justify-center shrink-0"
                                                          :class="isCategorySelected(node.id) ? 'bg-indigo-600 border-indigo-600' : 'bg-white border-gray-300'">
                                                        <i class="fa-solid fa-check text-white text-[9px]" x-show="isCategorySelected(node.id)"></i>
                                                    </span>
                                                    <i class="fa-solid text-[10px] shrink-0" :class="node.depth > 0 ? 'fa-turn-up fa-rotate-90 text-gray-300' : 'fa-folder text-indigo-400'"></i>
                                                    <span class="min-w-0">
                                                        <span class="block truncate font-medium text-gray-800" x-text="node.name"></span>
                                                        <span x-show="node.depth > 0" class="block truncate text-[10px] text-gray-400" x-text="node.path"></span>
                                                    </span>
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                    <p x-show="filteredCategoryNodes().length === 0" class="text-xs text-gray-400 text-center py-4">
                                        No categories match "<span x-text="categorySearch"></span>".
                                    </p>
                                    <p x-show="filteredCategoryNodes().length > 50" class="text-[10px] text-gray-400 text-center py-1.5 mt-1 border-t border-gray-100">
                                        Showing first 50 — keep typing to narrow it down.
                                    </p>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">Helps matching eligible suppliers by category specialization — pick as many as apply.</p>
                        </div>
                    </div>

                    {{-- Scope / Detailed Description --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                            Description &amp; Detailed Scope of Work
                        </label>
                        <textarea name="description" x-model="description" rows="5"
                                  placeholder="Describe your exact specifications, technical parameters, required deliverables, dimensions, material standards, or installation requirements in detail..."
                                  class="focus-accent w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-xs leading-relaxed bg-white placeholder-gray-400 resize-y"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">The more thorough your description, the more accurate suppliers' quotations will be.</p>
                    </div>
                </div>

                {{-- CARD 2: Reference Images & Documents Dropzone --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold" style="background: var(--theme-primary-soft, #eef2ff); color: var(--theme-primary, #4f46e5);">2</span>
                            <h2 class="text-sm font-bold text-gray-900">Reference Images &amp; Documents</h2>
                        </div>
                        <span class="text-[11px] text-gray-400 font-medium">Drawings, Specs, PDFs, Photos</span>
                    </div>

                    {{-- Already-uploaded files (edit mode only) — view + delete --}}
                    <div x-show="existingAttachments.length > 0" x-cloak class="space-y-2">
                        <p class="text-xs font-bold text-gray-700">Already Uploaded (<span x-text="existingAttachments.length"></span>)</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <template x-for="att in existingAttachments" :key="att.id">
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-gray-200 bg-gray-50/80 gap-3">
                                    <a :href="att.url" target="_blank" class="flex items-center gap-2.5 min-w-0 hover:underline">
                                        <img x-show="att.is_image" :src="att.url" class="w-8 h-8 rounded object-cover shrink-0 border border-gray-200">
                                        <i x-show="!att.is_image" class="fa-solid fa-file-lines text-gray-500 text-base shrink-0"></i>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-gray-800 truncate" x-text="att.name"></p>
                                            <p class="text-[10px] text-gray-400 font-mono" x-text="att.size"></p>
                                        </div>
                                    </a>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <a :href="att.url" target="_blank" class="text-gray-400 hover:text-indigo-600 p-1 transition-colors" title="View file">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <button type="button" @click="deleteExistingAttachment(att)" :disabled="deletingAttachmentId === att.id"
                                                class="text-gray-400 hover:text-red-600 p-1 transition-colors disabled:opacity-40" title="Delete file">
                                            <i class="fa-solid text-xs" :class="deletingAttachmentId === att.id ? 'fa-spinner fa-spin' : 'fa-trash-can'"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Drag and Drop Area --}}
                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-gray-400 transition-colors bg-gray-50/50"
                         :class="{ 'dropzone-hover': isDragging }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleFileDrop($event)">

                        <input type="file" id="fileInput" name="attachments[]" multiple
                               accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip,.csv,.txt"
                               @change="handleFileSelect($event)"
                               class="hidden">

                        <div class="flex flex-col items-center justify-center cursor-pointer" @click="triggerFileInput()">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background: var(--theme-primary-soft, #eef2ff); color: var(--theme-primary, #4f46e5);">
                                <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                            </div>
                            <p class="text-xs font-bold text-gray-800">
                                Click to upload or drag &amp; drop reference files
                            </p>
                            <p class="text-[11px] text-gray-500 mt-1">
                                Supported formats: PDF, Word (DOC/DOCX), Excel (XLS/XLSX), Images (PNG/JPG), ZIP up to 10MB each
                            </p>
                        </div>
                    </div>

                    {{-- Selected Files Queue --}}
                    <template x-if="selectedFiles.length > 0">
                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between text-xs font-bold text-gray-700">
                                <span>New Files to Upload (<span x-text="selectedFiles.length"></span>)</span>
                                <button type="button" @click="selectedFiles = []" class="text-red-500 hover:text-red-700 font-normal">
                                    Clear all
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <template x-for="(file, fIdx) in selectedFiles" :key="fIdx">
                                    <div class="flex items-center justify-between p-2.5 rounded-lg border border-gray-200 bg-gray-50/80 gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <template x-if="file.isImage">
                                                <i class="fa-solid fa-file-image text-emerald-600 text-base shrink-0"></i>
                                            </template>
                                            <template x-if="file.isPdf">
                                                <i class="fa-solid fa-file-pdf text-red-600 text-base shrink-0"></i>
                                            </template>
                                            <template x-if="file.isDoc">
                                                <i class="fa-solid fa-file-word text-blue-600 text-base shrink-0"></i>
                                            </template>
                                            <template x-if="!file.isImage && !file.isPdf && !file.isDoc">
                                                <i class="fa-solid fa-file-lines text-gray-500 text-base shrink-0"></i>
                                            </template>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-800 truncate" x-text="file.name"></p>
                                                <p class="text-[10px] text-gray-400 font-mono" x-text="formatFileSize(file.size)"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFile(fIdx)"
                                                class="text-gray-400 hover:text-red-600 p-1 transition-colors shrink-0" title="Remove file">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- CARD 3: Additional Specifications & Acceptance Criteria --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 sm:p-6 space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold" style="background: var(--theme-primary-soft, #eef2ff); color: var(--theme-primary, #4f46e5);">3</span>
                            <h2 class="text-sm font-bold text-gray-900">Specifications &amp; Acceptance Criteria</h2>
                        </div>
                        <button type="button" @click="addSpec()"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                            <i class="fa-solid fa-plus text-[10px]" style="color: var(--theme-primary, #4f46e5);"></i> Add Specification
                        </button>
                    </div>

                    <p class="text-xs text-gray-500">
                        Add key criteria that suppliers must comply with (e.g., Warranty, Material Grade, Brand Preference, or Delivery Timeline).
                    </p>

                    {{-- Empty State --}}
                    <div x-show="specs.length === 0" class="py-4 px-4 rounded-xl bg-gray-50 border border-dashed border-gray-200 text-center">
                        <p class="text-xs text-gray-500 flex items-center justify-center gap-1.5">
                            <i class="fa-regular fa-lightbulb text-gray-400"></i>
                            No custom specifications added. Click "Add Specification" to add parameters.
                        </p>
                    </div>

                    {{-- Specs Rows --}}
                    <div x-show="specs.length > 0" class="space-y-2.5">
                        <template x-for="(s, sIdx) in specs" :key="sIdx">
                            <div class="flex items-center gap-2.5 p-2 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="w-2/5">
                                    <input type="text" :name="'specs['+sIdx+'][name]'" x-model="s.name"
                                           placeholder="Specification (e.g. Warranty)"
                                           class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white font-medium">
                                </div>
                                <div class="flex-1">
                                    <input type="text" :name="'specs['+sIdx+'][value]'" x-model="s.value"
                                           placeholder="Required value (e.g. 2 Years Onsite)"
                                           class="focus-accent w-full text-xs rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                </div>
                                <button type="button" @click="removeSpec(sIdx)"
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0" title="Delete specification">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            {{-- ══════════════════════════════════════════════════════════
                 RIGHT COLUMN: Quantity, Unit, Price, Tips (4 cols)
                 ══════════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Quantity & Unit Card --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-5 space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold" style="background: var(--theme-primary-soft, #eef2ff); color: var(--theme-primary, #4f46e5);">4</span>
                        <h3 class="text-sm font-bold text-gray-900">Estimated Quantity &amp; Units</h3>
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                            Estimated Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.001" min="0.001" name="quantity" x-model="quantity" required
                               placeholder="1"
                               class="focus-accent w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-base font-bold text-gray-900 bg-white text-center">
                    </div>

                    {{-- Unit of Measure --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                            Unit of Measure
                        </label>
                        <select name="unit_id" x-model="unitId"
                                class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-xs bg-white text-gray-800">
                            <option value="">Select Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}@if($u->symbol) ({{ $u->symbol }})@endif</option>
                            @endforeach
                            <option value="__custom__">Other / Custom Unit</option>
                        </select>
                    </div>

                    {{-- Custom Unit if selected --}}
                    <div x-show="unitId === '__custom__'" x-cloak>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                            Custom Unit Name
                        </label>
                        <input type="text" name="custom_unit" x-model="customUnit"
                               placeholder="e.g. Lot, Job, Set, Package, Project"
                               class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-white">
                    </div>

                    {{-- Target Price (Optional) --}}
                    <div class="pt-2 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                            Estimated Target Unit Price <span class="text-gray-400 font-normal normal-case">(optional)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-gray-400">$</span>
                            <input type="number" step="0.01" min="0" name="estimated_unit_price" x-model="estimatedUnitPrice"
                                   placeholder="0.00"
                                   class="focus-accent w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg text-xs bg-white text-gray-900 font-medium">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Gives suppliers a ballpark expectation without locking them in.</p>
                    </div>
                </div>

                {{-- Guidance / Tips Card --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h4 class="text-xs font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-lightbulb" style="color: var(--theme-primary, #4f46e5);"></i>
                        Tips for High-Quality Quotations
                    </h4>
                    <ul class="text-xs text-gray-600 space-y-2 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-[11px] mt-1 shrink-0"></i>
                            <span><strong>Reference Drawings:</strong> Attach architectural sketches, CAD exports, or sample product photos.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-[11px] mt-1 shrink-0"></i>
                            <span><strong>Target Standards:</strong> List certifications, voltage, material grades, or compliance requirements.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-[11px] mt-1 shrink-0"></i>
                            <span><strong>Realistic Quantities:</strong> State the estimated volume to receive volume discounts.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
    function requirementForm(config) {
        return {
            title: {!! json_encode($isEdit ? $existingItem->item_name : '') !!},
            itemType: {!! json_encode($isEdit ? $existingItem->item_type : 'product') !!},
            description: {!! json_encode($isEdit ? $existingItem->description : '') !!},
            quantity: {!! json_encode($isEdit ? (string) $existingItem->quantity : '1') !!},
            unitId: {!! json_encode($isEdit ? ($existingItem->unit_id ?: '') : '') !!},
            customUnit: {!! json_encode($isEdit ? ($existingItem->custom_unit ?: '') : '') !!},
            estimatedUnitPrice: {!! json_encode($isEdit ? ($existingItem->estimated_unit_price ?? '') : '') !!},
            specs: {!! json_encode($initialSpecs) !!},
            selectedFiles: [],
            isDragging: false,
            isSubmitting: false,
            errorMessage: '',

            // ── Category picker (searchable hierarchy-path tree, multi-select) ──
            categoryNodes: config.categoryNodes || [],
            categoryIds: config.initialCategoryIds || [],
            categorySearch: '',
            categoryPickerOpen: false,

            // ── Already-uploaded files (edit mode) ──
            existingAttachments: config.existingAttachments || [],
            deletingAttachmentId: null,

            init() {},

            filteredCategoryNodes() {
                const q = this.categorySearch.trim().toLowerCase();
                if (!q) return this.categoryNodes;
                return this.categoryNodes.filter(n =>
                    n.name.toLowerCase().includes(q) || (n.path && n.path.toLowerCase().includes(q))
                );
            },
            getCategoryNode(id) {
                return this.categoryNodes.find(n => n.id === id);
            },
            isCategorySelected(id) {
                return this.categoryIds.includes(id);
            },
            toggleCategoryId(id) {
                const idx = this.categoryIds.indexOf(id);
                if (idx === -1) this.categoryIds.push(id); else this.categoryIds.splice(idx, 1);
            },

            async deleteExistingAttachment(att) {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Remove this file?',
                    text: att.name,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    confirmButtonColor: '#dc2626',
                });
                if (!result.isConfirmed) return;

                this.deletingAttachmentId = att.id;
                try {
                    const res = await fetch(config.deleteAttachmentUrlBase + '/' + att.id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.existingAttachments = this.existingAttachments.filter(a => a.id !== att.id);
                    }
                } finally {
                    this.deletingAttachmentId = null;
                }
            },

            addSpec() {
                this.specs.push({ name: '', value: '' });
            },
            removeSpec(index) {
                this.specs.splice(index, 1);
            },

            triggerFileInput() {
                document.getElementById('fileInput').click();
            },

            handleFileSelect(e) {
                this.processFiles(e.target.files);
            },

            handleFileDrop(e) {
                this.isDragging = false;
                const dt = e.dataTransfer;
                if (dt && dt.files) {
                    const input = document.getElementById('fileInput');
                    input.files = dt.files;
                    this.processFiles(dt.files);
                }
            },

            processFiles(files) {
                for (let i = 0; i < files.length; i++) {
                    const f = files[i];
                    const ext = f.name.split('.').pop().toLowerCase();
                    this.selectedFiles.push({
                        name: f.name,
                        size: f.size,
                        isImage: ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext),
                        isPdf: ext === 'pdf',
                        isDoc: ['doc', 'docx', 'odt'].includes(ext),
                    });
                }
            },

            removeFile(index) {
                this.selectedFiles.splice(index, 1);
            },

            formatFileSize(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
            },

            submitForm() {
                this.errorMessage = '';
                if (!this.title.trim()) {
                    this.errorMessage = 'Please provide a Requirement Title before submitting.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.quantity || parseFloat(this.quantity) <= 0) {
                    this.errorMessage = 'Please enter a valid estimated quantity greater than zero.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                this.isSubmitting = true;
                document.getElementById('requirementForm').submit();
            }
        };
    }
</script>
@endpush
@endsection
