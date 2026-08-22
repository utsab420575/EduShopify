@extends('backend.layouts.supplier')

@section('title', 'My Quotations')
@section('breadcrumb', 'Quotations / All Quotations')

@section('body')

    <x-backend.page-header title="My Quotations" subtitle="Track quotation submissions, buyer reviews, shortlist status, and revision requests." />

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('supplier.quotations.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="status" class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Statuses</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
                <option value="submitted" @selected($status === 'submitted')>Submitted</option>
                <option value="under_review" @selected($status === 'under_review')>Under Review</option>
                <option value="revision_requested" @selected($status === 'revision_requested')>Revision Requested</option>
                <option value="shortlisted" @selected($status === 'shortlisted')>Shortlisted</option>
                <option value="awarded" @selected($status === 'awarded')>Awarded</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                <option value="withdrawn" @selected($status === 'withdrawn')>Withdrawn</option>
            </select>
            <button type="submit" class="btn-primary text-xs font-semibold px-4 py-2.5 rounded-lg">
                Filter
            </button>
            @if($status)
                <a href="{{ route('supplier.quotations.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2">Reset</a>
            @endif
        </form>
    </div>

    {{-- Quotations Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($quotations->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-file-invoice" title="No quotations found" description="Browse available RFQ opportunities and submit your proposals." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Quotation / RFQ</th>
                            <th class="px-3 py-3.5 font-semibold">Buyer</th>
                            <th class="px-3 py-3.5 font-semibold">Total Amount</th>
                            <th class="px-3 py-3.5 font-semibold">Revision</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($quotations as $quote)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('supplier.quotations.show', $quote) }}" class="font-semibold text-gray-900 hover:text-indigo-600 truncate block max-w-xs">
                                        {{ $quote->rfq?->title ?? 'RFQ #' . $quote->rfq_id }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $quote->quotation_number }} &middot; {{ $quote->submitted_at?->format('d M Y') ?? $quote->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-600">
                                    {{ $quote->rfq?->buyerAccount?->buyerProfile?->organization_name ?? $quote->rfq?->buyerAccount?->display_name }}
                                </td>
                                <td class="px-3 py-3.5 font-bold text-indigo-700 text-xs">
                                    {{ $quote->currency_code }} {{ number_format($quote->grand_total, 2) }}
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-500">
                                    Rev #{{ $quote->current_revision_no }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <x-backend.status-badge :status="$quote->status" />
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('supplier.quotations.show', $quote) }}" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg inline-block">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($quotations->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $quotations->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
