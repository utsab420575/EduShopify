@extends('backend.layouts.admin')

@section('title', 'New Benefit')
@section('breadcrumb', 'Subscription & Billing / Benefits / New')

@section('body')

    <x-backend.page-header title="New Benefit" subtitle="Add a facility plans and supplier overrides can grant." />

    <form method="POST" action="{{ route('admin.billing.benefits.store') }}" class="space-y-6">
        @csrf
        @include('backend.admin.billing.benefits._form', ['benefit' => $benefit])

        <div class="flex items-center justify-end gap-2 bg-white rounded-xl border border-gray-200 p-4">
            <a href="{{ route('admin.billing.benefits.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="btn-primary text-sm font-medium px-5 py-2 rounded-lg">Create Benefit</button>
        </div>
    </form>

@endsection
