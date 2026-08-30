@extends('backend.layouts.admin')

@section('title', 'New Currency')
@section('breadcrumb', 'Catalog & Taxonomy / Currencies / New')

@section('body')

    <form method="POST" action="{{ route('admin.catalog.currencies.store') }}" class="space-y-6 max-w-3xl">
        @csrf

        <x-backend.page-header title="Add New Currency" subtitle="Define a currency code and exchange rate for platform pricing.">
            <x-slot:actions>
                <a href="{{ route('admin.catalog.currencies.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-check"></i>
                    <span>Save Currency</span>
                </button>
            </x-slot:actions>
        </x-backend.page-header>

        @include('backend.admin.catalog.currencies._form')
    </form>

@endsection
