@extends('backend.layouts.buyer')

@section('title', 'Convert to Organization')
@section('breadcrumb', 'Settings / Convert to Organization')

@section('body')

    <x-backend.page-header title="Convert to Organization" subtitle="Upgrade your individual account to an organization account." />

    @if($latestRequest && in_array($latestRequest->status, ['pending', 'draft']))
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6">
            <i class="fa-regular fa-clock mt-0.5"></i>
            <p class="text-sm">Your conversion request is pending admin review.</p>
        </div>
    @elseif($latestRequest?->status === 'revision_required')
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6">
            <p class="text-sm">{{ $latestRequest->review_comment ?? 'Please update your submission and resubmit.' }}</p>
        </div>
    @elseif($latestRequest?->status === 'rejected')
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6">
            <p class="text-sm">{{ $latestRequest->review_comment ?? 'Your previous request was not approved.' }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('buyer.settings.conversion.submit') }}" class="max-w-2xl">
        @csrf
        <x-backend.form-card title="Organization Details">
            <div class="space-y-4">
                <x-backend.input name="proposed_display_name" label="Organization Name" required :value="old('proposed_display_name', $latestRequest?->proposed_display_name)" />
                <x-backend.input name="legal_name" label="Legal Name" :value="old('legal_name', $latestRequest?->proposed_organization_data['legal_name'] ?? null)" />
                <x-backend.input name="registration_number" label="Registration Number" :value="old('registration_number', $latestRequest?->proposed_organization_data['registration_number'] ?? null)" />
                <x-backend.input name="tax_id" label="Tax ID" :value="old('tax_id', $latestRequest?->proposed_organization_data['tax_id'] ?? null)" />
                <x-backend.textarea name="notes" label="Additional Notes" :value="old('notes', $latestRequest?->proposed_organization_data['notes'] ?? null)" />
            </div>
        </x-backend.form-card>
        <div class="flex justify-end mt-4">
            <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Submit for Review</button>
        </div>
    </form>

@endsection
