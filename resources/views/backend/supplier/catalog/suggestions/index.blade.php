@extends('backend.layouts.supplier')

@section('title', 'Category & Attribute Suggestions')
@section('breadcrumb', 'Catalog / Suggestions')

@section('body')

    <x-backend.page-header
        title="Category & Attribute Suggestions"
        subtitle="Propose new categories or attributes to the platform team for approval." />

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">

        {{-- Category Suggestion Form --}}
        <x-backend.form-card title="Suggest a New Category">
            <form method="POST" action="{{ route('supplier.catalog.suggestions.category.store') }}" class="space-y-4">
                @csrf
                <x-backend.input name="proposed_name" label="Category Name" required
                    placeholder="e.g. Interactive Whiteboards" />
                <x-backend.select name="proposed_type" label="Applies To" required>
                    <option value="product">Products</option>
                    <option value="service">Services</option>
                    <option value="both">Both Products & Services</option>
                </x-backend.select>
                <x-backend.select name="parent_category_id" label="Parent Category (optional)">
                    <option value="">— Top Level —</option>
                    @foreach($parentCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </x-backend.select>
                <x-backend.textarea name="description" label="Why this category?" rows="3"
                    placeholder="Describe what products/services belong in this category and why it's needed." />
                <button type="submit" class="btn-primary text-sm font-semibold px-5 py-2.5 rounded-lg">
                    Submit Category Suggestion
                </button>
            </form>
        </x-backend.form-card>

        {{-- Attribute Suggestion Form --}}
        <x-backend.form-card title="Suggest a New Attribute">
            <form method="POST" action="{{ route('supplier.catalog.suggestions.attribute.store') }}" class="space-y-4">
                @csrf
                <x-backend.input name="proposed_name" label="Attribute Name" required
                    placeholder="e.g. Screen Resolution" />
                <x-backend.select name="proposed_input_type" label="Input Type" required>
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="number">Number</option>
                    <option value="select">Select (single choice)</option>
                    <option value="multi_select">Multi-Select (multiple choices)</option>
                    <option value="boolean">Yes / No</option>
                    <option value="date">Date</option>
                    <option value="color">Color</option>
                </x-backend.select>
                <x-backend.select name="category_id" label="Related Category (optional)">
                    <option value="">— Any Category —</option>
                    @foreach($parentCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </x-backend.select>
                <x-backend.input name="proposed_unit_name" label="Unit (optional)" placeholder="e.g. kg, cm, inch" />
                <x-backend.textarea name="reason" label="Reason / Use Case" rows="3"
                    placeholder="How would buyers use this attribute to filter or compare products?" />
                <button type="submit" class="btn-primary text-sm font-semibold px-5 py-2.5 rounded-lg">
                    Submit Attribute Suggestion
                </button>
            </form>
        </x-backend.form-card>

    </div>

    {{-- Previous Category Suggestions --}}
    <h3 class="text-sm font-bold text-gray-700 mb-3 mt-4">My Category Suggestions</h3>
    <x-backend.table>
        @if($categorySuggestions->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-folder-plus" title="No category suggestions yet" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proposed Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Parent</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                </tr>
            </x-slot:head>
            @foreach($categorySuggestions as $sug)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $sug->proposed_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 capitalize">{{ $sug->proposed_type }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $sug->parentCategory?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$sug->status" /></td>
                    <td class="px-5 py-3.5 text-xs text-gray-400">{{ $sug->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        @endif
        <x-slot:pagination>
            <x-backend.pagination :paginator="$categorySuggestions" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Previous Attribute Suggestions --}}
    <h3 class="text-sm font-bold text-gray-700 mb-3 mt-8">My Attribute Suggestions</h3>
    <x-backend.table>
        @if($attributeSuggestions->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-tag" title="No attribute suggestions yet" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proposed Name</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Input Type</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                </tr>
            </x-slot:head>
            @foreach($attributeSuggestions as $sug)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $sug->proposed_name }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600 capitalize">{{ str_replace('_', '-', $sug->proposed_input_type) }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $sug->category?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$sug->status" /></td>
                    <td class="px-5 py-3.5 text-xs text-gray-400">{{ $sug->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        @endif
        <x-slot:pagination>
            <x-backend.pagination :paginator="$attributeSuggestions" />
        </x-slot:pagination>
    </x-backend.table>

@endsection
