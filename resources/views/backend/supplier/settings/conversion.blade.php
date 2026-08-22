@extends('backend.layouts.supplier')

@section('title', 'Convert to Organization')
@section('breadcrumb', 'Settings / Convert Account')

@section('body')

    <x-backend.page-header title="Convert to Organization" subtitle="Upgrade an individual supplier account to an organization with team members and custom roles." />

    @if($latestRequest && $latestRequest->status === 'pending')
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6 max-w-2xl">
            <h4 class="text-sm font-bold text-amber-900">Conversion Request Under Review</h4>
            <p class="text-xs text-amber-800 mt-1">Your request to convert to an organization has been submitted on {{ $latestRequest->submitted_at?->format('d M Y') }} and is awaiting admin approval.</p>
        </div>
    @else
        <div class="max-w-2xl">
            <x-backend.form-card title="Organization Upgrade Application">
                <form method="POST" action="{{ route('supplier.settings.conversion.submit') }}" class="space-y-4">
                    @csrf
                    <x-backend.input name="proposed_display_name" label="Organization Trading Name" required :value="old('proposed_display_name', $account->display_name)" />
                    <x-backend.input name="legal_name" label="Registered Corporate Legal Name" required />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-backend.input name="registration_number" label="Company Registration / Tax ID" />
                        <x-backend.input name="tax_id" label="VAT / GST Number" />
                    </div>
                    <x-backend.textarea name="notes" label="Additional Information" placeholder="Provide any details about your organization setup..." />

                    <button type="submit" class="btn-primary text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-paper-plane"></i> Submit Conversion Request
                    </button>
                </form>
            </x-backend.form-card>
        </div>
    @endif

@endsection
