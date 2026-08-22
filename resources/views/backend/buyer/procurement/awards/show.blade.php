@extends('backend.layouts.buyer')

@section('title', 'Award ' . $award->award_number)
@section('breadcrumb', 'Procurement / Awards / ' . $award->award_number)

@section('body')

    <x-backend.page-header :title="$award->rfq->title" :subtitle="'Award ' . $award->award_number">
        <x-slot:actions>
            <x-backend.status-badge :status="$award->status" />
        </x-slot:actions>
    </x-backend.page-header>

    @if($award->status === 'pending_supplier_response')
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6">
            <i class="fa-regular fa-clock mt-0.5"></i>
            <p class="text-sm">Waiting for the supplier to respond by {{ $award->response_deadline->format('d M Y, h:i A') }}.</p>
        </div>
    @elseif($award->status === 'rejected_by_supplier')
        <div class="bg-white rounded-xl border border-red-200 p-5 mb-6">
            <p class="text-sm font-semibold text-gray-900">The supplier declined this award</p>
            <p class="text-sm text-gray-600 mt-1">{{ $award->supplier_rejection_reason }}</p>
            <a href="{{ route('buyer.quotations.compare', $award->rfq) }}" class="inline-block text-sm font-medium mt-3" style="color:var(--theme-primary)">Choose another quotation to re-award &rarr;</a>
        </div>
    @elseif($award->status === 'accepted')
        <div class="flex items-start justify-between gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6">
            <p class="text-sm"><i class="fa-solid fa-circle-check mt-0.5 mr-2"></i>The supplier accepted this award.</p>
            @if($award->purchaseOrder)
                <a href="{{ route('buyer.purchase-orders.show', $award->purchaseOrder) }}" class="text-sm font-medium whitespace-nowrap" style="color:var(--theme-primary)">View Purchase Order &rarr;</a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Awarded Quotation">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $award->supplierAccount?->supplierProfile?->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ $award->quotation->quotation_number }}</p>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($award->quotation->grand_total, 2) }} {{ $award->quotation->currency_code }}</p>
                </div>
                <a href="{{ route('buyer.quotations.show', $award->quotation) }}" class="inline-block text-sm font-medium mt-4" style="color:var(--theme-primary)">View full quotation &rarr;</a>
            </x-backend.form-card>

            @if($award->award_note)
                <x-backend.form-card title="Your Note to the Supplier">
                    <p class="text-sm text-gray-600">{{ $award->award_note }}</p>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Attempt</dt><dd class="text-gray-900 font-medium">#{{ $award->award_attempt_no }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Awarded</dt><dd class="text-gray-900 font-medium">{{ $award->awarded_at->format('d M Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Response Deadline</dt><dd class="text-gray-900 font-medium">{{ $award->response_deadline->format('d M Y') }}</dd></div>
                    @if($award->responded_at)
                        <div class="flex justify-between"><dt class="text-gray-500">Responded</dt><dd class="text-gray-900 font-medium">{{ $award->responded_at->format('d M Y') }}</dd></div>
                    @endif
                </dl>
            </x-backend.form-card>
        </div>
    </div>

@endsection
