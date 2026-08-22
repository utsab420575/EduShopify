@extends('backend.layouts.admin')

@section('title', $inquiry->subject ?? 'Inquiry')
@section('breadcrumb', 'Communication / Inquiries / ' . ($inquiry->subject ?? 'Detail'))

@section('body')

    <x-backend.page-header :title="$inquiry->subject ?? 'Contact Inquiry'" :subtitle="'From ' . $inquiry->name">
        <x-slot:actions>
            <x-backend.status-badge :status="$inquiry->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Message">
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $inquiry->message }}</p>
            </x-backend.form-card>

            <x-backend.form-card title="Update Status">
                <form method="POST" action="{{ route('admin.communication.inquiries.status', $inquiry) }}" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <x-backend.select name="status" :selected="$inquiry->status" :options="['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'closed' => 'Closed']" />
                    </div>
                    <button type="submit" class="btn-primary text-sm font-medium px-5 py-2.5 rounded-lg">Update</button>
                </form>
            </x-backend.form-card>
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Contact Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd class="font-medium text-gray-900">{{ $inquiry->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $inquiry->email }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $inquiry->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Organization</dt><dd class="font-medium text-gray-900">{{ $inquiry->organization ?? '—' }}</dd></div>
                </dl>
            </x-backend.form-card>

            @if(!$inquiry->isPlatformInquiry())
                <x-backend.form-card title="Target Supplier">
                    <p class="text-sm font-medium text-gray-900">{{ $inquiry->supplierAccount?->supplierProfile?->display_name }}</p>
                    <a href="{{ route('admin.suppliers.show', $inquiry->supplierAccount) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Supplier &rarr;</a>
                </x-backend.form-card>
            @endif

            @if($inquiry->listing)
                <x-backend.form-card title="Related Listing">
                    <a href="{{ route('admin.catalog.listings.show', $inquiry->listing) }}" class="text-sm font-medium" style="color:var(--theme-primary)">{{ $inquiry->listing->name }} &rarr;</a>
                </x-backend.form-card>
            @endif
        </div>
    </div>

@endsection
