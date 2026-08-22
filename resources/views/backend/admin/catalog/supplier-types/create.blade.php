@extends('backend.layouts.admin')

@section('title', 'New Supplier Type')
@section('breadcrumb', 'Catalog & Taxonomy / Supplier Types / New')

@section('body')

    <x-backend.page-header title="New Supplier Type" />

    <form method="POST" action="{{ route('admin.catalog.supplier-types.store') }}" class="space-y-6">
        @csrf
        @include('backend.admin.catalog.supplier-types._form', ['supplierType' => $supplierType])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.catalog.supplier-types.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Create Supplier Type</button>
        </div>
    </form>

@endsection
