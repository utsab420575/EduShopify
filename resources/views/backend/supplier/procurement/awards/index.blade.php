@extends('backend.layouts.supplier')

@section('title', 'Awards')
@section('breadcrumb', 'Procurement / Awards')

@section('body')

    <x-backend.page-header title="Awards" subtitle="Review RFQ contracts awarded to your business by institutional buyers." />

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('supplier.awards.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="status" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Statuses</option>
                <option value="pending_supplier_response" @selected($status === 'pending_supplier_response')>Awaiting Response</option>
                <option value="accepted" @selected($status === 'accepted')>Accepted</option>
                <option value="rejected_by_supplier" @selected($status === 'rejected_by_supplier')>Rejected</option>
                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
            </select>
            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2.5 rounded-lg">
                Filter
            </button>
            @if($status)
                <a href="{{ route('supplier.awards.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2">Reset</a>
            @endif
        </form>
    </div>

    {{-- Awards Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($awards->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-trophy" title="No awards yet" description="When buyers accept your quotations, your awards will appear here for confirmation." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Award / RFQ</th>
                            <th class="px-3 py-3.5 font-semibold">Buyer</th>
                            <th class="px-3 py-3.5 font-semibold">Quotation</th>
                            <th class="px-3 py-3.5 font-semibold">Response Deadline</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($awards as $award)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('supplier.awards.show', $award) }}" class="font-semibold text-gray-900 hover:text-indigo-600 truncate block max-w-xs">
                                        {{ $award->rfq?->title ?? 'RFQ #' . $award->rfq_id }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $award->award_number }} &middot; {{ $award->awarded_at?->format('d M Y') }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-600">
                                    {{ $award->buyerAccount?->buyerProfile?->organization_name ?? $award->buyerAccount?->display_name }}
                                </td>
                                <td class="px-3 py-3.5 text-xs font-semibold text-indigo-700">
                                    {{ $award->quotation?->quotation_number }}
                                </td>
                                <td class="px-3 py-3.5 text-xs">
                                    @if($award->isAwaitingResponse())
                                        <span class="font-bold text-red-600">{{ $award->response_deadline?->format('d M Y, h:i A') ?? 'Immediate' }}</span>
                                    @else
                                        <span class="text-gray-400">Responded</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-backend.status-badge :status="$award->status" />
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('supplier.awards.show', $award) }}" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg inline-block">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($awards->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $awards->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
