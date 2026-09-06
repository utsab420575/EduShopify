@extends('frontend_new.layouts.app')

@section('title', 'Compare Products – Edushopify')
@section('body_class', 'bg-gray-50')

@push('head')
    {{-- Dynamic, user-selected combinations — never indexed as permanent pages. --}}
    <meta name="robots" content="noindex, follow">
    <style>
        .comparison-table { border-collapse: separate; border-spacing: 0; }
        .comparison-table th.sticky, .comparison-table td.sticky { position: sticky; left: 0; }
        .comparison-table thead th { position: sticky; top: 64px; z-index: 15; }
        .comparison-table thead th.sticky { z-index: 25; }
        @media print {
            header, footer, .comparison-hide-print { display: none !important; }
            #comparison-print-area { border: none !important; }
        }
    </style>
@endpush

@section('content')
<div id="v2-compare-page" class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8" data-max-items="{{ $maxItems }}">

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('v2.home') }}" class="hover:text-gray-900">Home</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-700">Compare Products</span>
    </div>

    <div data-compare-loading class="hidden py-20 text-center text-sm text-gray-500">
        <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Loading comparison…
    </div>

    <div data-compare-empty>
        <div class="sidebar-card text-center py-14">
            <i class="fa-solid fa-arrow-right-arrow-left text-3xl text-gray-300 mb-3"></i>
            <p class="text-base font-semibold text-gray-900 mb-1">No products selected for comparison.</p>
            <p class="text-sm text-gray-500 mb-5">Browse products and add them to compare.</p>
            <a href="{{ route('v2.home') }}" class="inline-flex items-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">Browse Products</a>
        </div>
    </div>

    <div data-compare-loaded class="hidden">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-5 mt-2">
            <h1 class="text-xl font-bold text-gray-900">
                Compare Products
                <span data-compare-count class="text-sm font-normal text-gray-500"></span>
            </h1>
            <div class="comparison-controls comparison-hide-print flex items-center gap-3 flex-wrap">
                <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 cursor-pointer">
                    <input type="checkbox" onchange="fnCompareToggleDiffs(this)" class="rounded accent-emerald-500">
                    Show Differences Only
                </label>
                <button type="button" onclick="window.print()" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-print mr-1"></i> Print
                </button>
                <button type="button" onclick="fnCompareClearAll()" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-300 text-red-600 hover:bg-red-50">
                    <i class="fa-solid fa-trash mr-1"></i> Clear All
                </button>
            </div>
        </div>

        {{-- Bulk RFQ --}}
        <div class="comparison-hide-print mb-5 flex items-center justify-between gap-3 flex-wrap rounded-xl px-4 py-3 bg-emerald-50 border border-emerald-100">
            <p class="text-xs font-medium text-gray-600">
                <i class="fa-solid fa-file-invoice mr-1 text-emerald-600"></i>
                Request a quote for all <span data-compare-rfq-count>0</span> compared products at once.
            </p>
            <a data-compare-rfq-link href="#" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-4 py-2 rounded-lg inline-flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-paper-plane"></i> Send RFQ to All Suppliers
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 overflow-hidden" id="comparison-print-area">
            <div class="overflow-x-auto">
                <table class="w-full comparison-table" style="min-width:720px;">
                    <thead>
                        <tr data-compare-header-row></tr>
                    </thead>
                    <tbody data-compare-body></tbody>
                </table>
            </div>
        </div>

        <div class="comparison-hide-print text-center mt-5">
            <button type="button" data-compare-toggle-additional onclick="fnCompareToggleAdditional()" class="text-sm font-semibold px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fa-solid fa-chevron-down mr-1.5"></i> Show Additional Information
            </button>
        </div>
    </div>

</div>
@endsection
