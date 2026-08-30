@extends('backend.layouts.supplier')

@section('title', 'RFQ Opportunities')
@section('breadcrumb', 'RFQ Opportunities / Available RFQs')

@section('body')

    <x-backend.page-header title="RFQ Opportunities" subtitle="Discover procurement requests from educational institutions seeking quotations." />

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex items-center gap-2 flex-wrap">
            @foreach($filterOptions as $key => $label)
                <a href="{{ route('supplier.opportunities.index', $key === 'all' ? [] : ['filter' => $key]) }}"
                   class="text-xs font-semibold px-3 py-2 rounded-lg {{ $filter === $key ? 'btn-primary' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Opportunities List --}}
    <div class="space-y-4">
        @forelse($opportunities as $queueEntry)
            @php $rfq = $queueEntry->rfq; @endphp
            @if($rfq)
                @php($visibilityCode = $rfq->getRelationValue('visibilityType')?->code)
                <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="text-xs font-mono font-bold text-gray-400">{{ $rfq->rfq_number }}</span>
                                @if($visibilityCode === 'direct')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-star mr-1"></i> Direct
                                    </span>
                                @elseif($visibilityCode === 'invited')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-envelope-open mr-1"></i> Invited
                                    </span>
                                @elseif($visibilityCode === 'open_matching')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Open Matching</span>
                                @elseif($visibilityCode === 'broadcast_all')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-700">Broadcast</span>
                                @endif
                                @if(!$queueEntry->seen_at)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">New</span>
                                @endif
                                <x-backend.status-badge :status="$rfq->status" />
                            </div>
                            <h3 class="text-base font-bold text-gray-900">
                                <a href="{{ route('supplier.opportunities.show', $rfq) }}" class="hover:text-indigo-600">
                                    {{ $rfq->title }}
                                </a>
                            </h3>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-xs text-gray-400 block">Quotation Deadline</span>
                            <span class="text-xs font-bold text-amber-700">
                                <i class="fa-regular fa-clock mr-1"></i>{{ $rfq->quotation_deadline->format('d M Y, h:i A') }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="text-xs text-gray-500 space-y-1">
                            <p><i class="fa-solid fa-building mr-1.5 text-gray-400"></i>Buyer: <span class="font-medium text-gray-800">{{ $rfq->buyerAccount?->buyerProfile?->organization_name ?? $rfq->buyerAccount?->display_name }}</span></p>
                            <p><i class="fa-solid fa-list-check mr-1.5 text-gray-400"></i>{{ $rfq->items->count() }} item(s) requested &middot; Delivery by: {{ $rfq->expected_delivery_date?->format('d M Y') ?? 'Flexible' }}</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('supplier.opportunities.show', $rfq) }}" class="btn-primary text-xs font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                                View &amp; Submit Quote <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <x-backend.empty-state icon="fa-magnifying-glass-chart" title="No RFQ opportunities right now" description="Check back soon or ensure your service areas and catalog match buyer requirements." />
            </div>
        @endforelse

        @if($opportunities->hasPages())
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                {{ $opportunities->links() }}
            </div>
        @endif
    </div>

@endsection
