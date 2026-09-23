@extends('backend.layouts.supplier')

@section('title', 'Submit Quotation — ' . $rfq->title)
@section('breadcrumb', 'Quotations / ' . $quotation->quotation_number . ' / Draft')

@section('body')

    <x-backend.page-header title="Submit Quotation" subtitle="RFQ: {{ $rfq->rfq_number }} — {{ $rfq->title }}" />

    @if(isset($cloneSource) && $cloneSource)
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-5 flex items-start gap-3">
            <i class="fa-solid fa-wand-magic-sparkles text-emerald-600 mt-0.5"></i>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-emerald-900">
                    Pre-filled from Quotation #{{ $cloneSource->quotation_number }} for "{{ $cloneSource->rfq?->title }}"
                </p>
                <p class="text-xs text-emerald-700 mt-0.5">Matched pricing and terms were carried over where items looked similar — review everything before submitting.</p>
            </div>
        </div>
    @elseif(isset($previousQuotations) && $previousQuotations->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                <i class="fa-solid fa-clock-rotate-left"></i> Start from a previous quotation
                <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-cloak class="mt-3 space-y-1.5">
                @foreach($previousQuotations as $pq)
                    <a href="{{ route('supplier.quotations.create', $rfq) }}?clone_from={{ $pq->id }}"
                       class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/40 text-sm">
                        <span class="min-w-0 truncate">
                            <span class="font-medium text-gray-800">{{ $pq->quotation_number }}</span>
                            <span class="text-gray-400"> — {{ $pq->rfq?->title }}</span>
                        </span>
                        <span class="text-xs text-gray-400 shrink-0">{{ $pq->created_at->format('d M Y') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @include('backend.supplier.procurement.quotations.partials._form')

@endsection
