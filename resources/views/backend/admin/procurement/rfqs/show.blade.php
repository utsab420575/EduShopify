@extends('backend.layouts.admin')

@section('title', $rfq->title)
@section('breadcrumb', 'Procurement Oversight / RFQs / ' . $rfq->title)

@section('body')

    <x-backend.page-header :title="$rfq->title" :subtitle="$rfq->rfq_number">
        <x-slot:actions>
            <x-backend.status-badge :status="$rfq->status" />
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @if($rfq->status === 'pending_approval')
            <form method="POST" action="{{ route('admin.procurement.rfqs.approve', $rfq) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Approve &amp; Publish</button>
            </form>
        @endif
        @if(!in_array($rfq->status, ['cancelled', 'completed', 'closed']))
            <button @click="$dispatch('open-modal-cancel')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Cancel RFQ</button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-backend.form-card title="RFQ Details">
                <p class="text-sm text-gray-600 mb-4">{{ $rfq->description }}</p>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Visibility</dt><dd class="font-medium text-gray-900">{{ $rfq->getRelationValue('visibilityType')?->name ?? ucfirst(str_replace('_', ' ', $rfq->visibility_type ?? '')) }}</dd></div>
                    <div><dt class="text-gray-500">Budget</dt><dd class="font-medium text-gray-900">{{ $rfq->budget_min ? number_format($rfq->budget_min, 2) : '—' }} &ndash; {{ $rfq->budget_max ? number_format($rfq->budget_max, 2) : '—' }} {{ $rfq->currency_code }}</dd></div>
                    <div><dt class="text-gray-500">Quotation Deadline</dt><dd class="font-medium text-gray-900">{{ $rfq->quotation_deadline?->format('d M Y H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Expected Delivery</dt><dd class="font-medium text-gray-900">{{ $rfq->expected_delivery_date?->format('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Delivery Location</dt><dd class="font-medium text-gray-900">{{ $rfq->deliveryCity?->name ?? $rfq->deliveryState?->name ?? $rfq->deliveryCountry?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Version</dt><dd class="font-medium text-gray-900">v{{ $rfq->current_version_no }}</dd></div>
                </dl>
            </x-backend.form-card>

            @if($rfq->items->isNotEmpty())
                <x-backend.form-card title="Line Items">
                    <div class="overflow-x-auto -mx-5">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-500 uppercase">
                                    <th class="px-5 py-2 text-left">Item</th>
                                    <th class="px-5 py-2 text-left">Category</th>
                                    <th class="px-5 py-2 text-left">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rfq->items as $item)
                                    <tr class="border-b border-gray-50">
                                        <td class="px-5 py-2.5 text-gray-900">{{ $item->item_name }}</td>
                                        <td class="px-5 py-2.5 text-gray-600">{{ $item->category?->name ?? '—' }}</td>
                                        <td class="px-5 py-2.5 text-gray-600">{{ $item->quantity }} {{ $item->custom_unit ?? $item->unit?->symbol }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-backend.form-card>
            @endif

            @if($rfq->quotations->isNotEmpty())
                <x-backend.form-card title="Quotations Received">
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($rfq->quotations as $quotation)
                            <li class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <p class="text-sm text-gray-800">{{ $quotation->supplierAccount?->supplierProfile?->display_name }}</p>
                                    <p class="text-xs text-gray-400">{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-backend.status-badge :status="$quotation->status" />
                                    <a href="{{ route('admin.procurement.quotations.show', $quotation) }}" class="text-sm font-medium" style="color:var(--theme-primary)">View</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif

            @if($rfq->cancellation_reason)
                <x-backend.form-card title="Cancellation Reason"><p class="text-sm text-gray-600">{{ $rfq->cancellation_reason }}</p></x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Buyer">
                <p class="text-sm font-medium text-gray-900">{{ $rfq->buyerAccount?->display_name }}</p>
                <a href="{{ route('admin.buyers.show', $rfq->buyerAccount) }}" class="text-sm font-medium mt-2 inline-block" style="color:var(--theme-primary)">View Buyer &rarr;</a>
            </x-backend.form-card>

            @if($rfq->awards->isNotEmpty())
                <x-backend.form-card title="Awards">
                    <ul class="space-y-3">
                        @foreach($rfq->awards as $award)
                            <li class="text-sm">
                                <div class="flex justify-between"><span class="text-gray-700">Attempt #{{ $award->award_attempt_no }}</span><x-backend.status-badge :status="$award->status" /></div>
                                <a href="{{ route('admin.procurement.awards.show', $award) }}" class="text-xs" style="color:var(--theme-primary)">View award &rarr;</a>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif
        </div>
    </div>

    @if(!in_array($rfq->status, ['cancelled', 'completed', 'closed']))
        <x-backend.modal id="cancel" title="Cancel RFQ">
            <form method="POST" action="{{ route('admin.procurement.rfqs.cancel', $rfq) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason for cancellation" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Cancel RFQ</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
