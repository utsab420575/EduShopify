@extends('backend.layouts.admin')

@section('title', $award->award_number)
@section('breadcrumb', 'Procurement Oversight / Awards / ' . $award->award_number)

@section('body')

    <x-backend.page-header :title="$award->award_number" subtitle="Award details">
        <x-slot:actions>
            <x-backend.status-badge :status="$award->status" />
        </x-slot:actions>
    </x-backend.page-header>

    @if(!in_array($award->status, ['accepted', 'rejected', 'cancelled', 'expired']))
        <div class="mb-6">
            <button @click="$dispatch('open-modal-cancel')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Cancel Award</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="Award Details">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Attempt</dt><dd class="font-medium text-gray-900">#{{ $award->award_attempt_no }}</dd></div>
                    <div><dt class="text-gray-500">Awarded By</dt><dd class="font-medium text-gray-900">{{ $award->awardedBy?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Awarded At</dt><dd class="font-medium text-gray-900">{{ $award->awarded_at?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Response Deadline</dt><dd class="font-medium text-gray-900">{{ $award->response_deadline?->format('d M Y H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Responded At</dt><dd class="font-medium text-gray-900">{{ $award->responded_at?->format('d M Y') ?? '—' }}</dd></div>
                </dl>
                @if($award->award_note)
                    <p class="text-sm text-gray-600 mt-4 border-t border-gray-100 pt-4">{{ $award->award_note }}</p>
                @endif
                @if($award->supplier_rejection_reason)
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Supplier Rejection Reason</p>
                        <p class="text-sm text-gray-600">{{ $award->supplier_rejection_reason }}</p>
                    </div>
                @endif
            </x-backend.form-card>
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Parties">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-gray-500">Buyer</dt><dd class="font-medium text-gray-900"><a href="{{ route('admin.buyers.show', $award->buyerAccount) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $award->buyerAccount?->display_name }}</a></dd></div>
                    <div><dt class="text-gray-500">Supplier</dt><dd class="font-medium text-gray-900"><a href="{{ route('admin.suppliers.show', $award->supplierAccount) }}" class="hover:underline" style="color:var(--theme-primary)">{{ $award->supplierAccount?->supplierProfile?->display_name ?? $award->supplierAccount?->display_name }}</a></dd></div>
                </dl>
            </x-backend.form-card>

            <x-backend.form-card title="Related Records">
                <div class="space-y-2 text-sm">
                    <a href="{{ route('admin.procurement.rfqs.show', $award->rfq) }}" class="block hover:underline" style="color:var(--theme-primary)">View RFQ &rarr;</a>
                    <a href="{{ route('admin.procurement.quotations.show', $award->quotation) }}" class="block hover:underline" style="color:var(--theme-primary)">View Quotation &rarr;</a>
                    @if($award->purchaseOrder)
                        <a href="{{ route('admin.procurement.purchase-orders.show', $award->purchaseOrder) }}" class="block hover:underline" style="color:var(--theme-primary)">View Purchase Order &rarr;</a>
                    @endif
                </div>
            </x-backend.form-card>
        </div>
    </div>

    @if(!in_array($award->status, ['accepted', 'rejected', 'cancelled', 'expired']))
        <x-backend.modal id="cancel" title="Cancel Award">
            <form method="POST" action="{{ route('admin.procurement.awards.cancel', $award) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason for cancellation" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Cancel Award</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
