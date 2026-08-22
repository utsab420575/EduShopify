@extends('backend.layouts.supplier')

@section('title', 'Request Role')
@section('breadcrumb', 'Access Control / Role Requests / Create')

@section('body')

    <x-backend.page-header title="Request Role Elevation" subtitle="Request additional permissions from your account administrator." />

    <div class="max-w-xl">
        <x-backend.form-card title="Role Request Form">
            <form method="POST" action="{{ route('supplier.role-requests.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                        <option value="">Select requested role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-backend.textarea name="justification" label="Business Justification" required placeholder="Describe why you need access to these permissions..." />
                <button type="submit" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-paper-plane"></i> Submit Request
                </button>
            </form>
        </x-backend.form-card>
    </div>

@endsection
