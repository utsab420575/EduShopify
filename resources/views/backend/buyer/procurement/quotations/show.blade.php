@extends('backend.layouts.buyer')

@section('title', 'Quotation ' . $quotation->quotation_number . ' — ' . ($quotation->supplierAccount?->supplierProfile?->display_name ?? 'Supplier'))
@section('breadcrumb', 'Procurement / Quotations / ' . $quotation->quotation_number)

@push('styles')
    <style>
        .custom-vertical-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .custom-vertical-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-vertical-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 9999px;
        }
        .custom-vertical-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-vertical-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush

@section('body')

@php
    $statusColors = [
        'submitted'          => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
        'shortlisted'        => ['bg' => 'bg-amber-50',   'border' => 'border-amber-200',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
        'awarded'            => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',    'text' => 'text-blue-700',    'dot' => 'bg-blue-500'],
        'accepted'           => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
        'draft'              => ['bg' => 'bg-gray-100',   'border' => 'border-gray-200',    'text' => 'text-gray-600',    'dot' => 'bg-gray-400'],
        'under_review'       => ['bg' => 'bg-indigo-50',  'border' => 'border-indigo-200',  'text' => 'text-indigo-700',  'dot' => 'bg-indigo-500'],
        'revision_requested' => ['bg' => 'bg-purple-50',  'border' => 'border-purple-200',  'text' => 'text-purple-700',  'dot' => 'bg-purple-500'],
        'revised'            => ['bg' => 'bg-indigo-50',  'border' => 'border-indigo-200',  'text' => 'text-indigo-700',  'dot' => 'bg-indigo-500'],
        'rejected'           => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'text' => 'text-red-700',     'dot' => 'bg-red-400'],
        'withdrawn'          => ['bg' => 'bg-gray-100',   'border' => 'border-gray-200',    'text' => 'text-gray-600',    'dot' => 'bg-gray-400'],
        'expired'            => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'text' => 'text-red-700',     'dot' => 'bg-red-400'],
    ];
    $sc = $statusColors[$quotation->status] ?? ['bg' => 'bg-gray-100', 'border' => 'border-gray-200', 'text' => 'text-gray-600', 'dot' => 'bg-gray-400'];
    $validityPassed = $quotation->valid_until && $quotation->valid_until->isPast();
@endphp

    {{-- ── 1. Hero Header (Matching RFQ Show Standard) ─────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs mb-5 overflow-hidden">
        {{-- Top colour stripe --}}
        <div class="h-1 w-full" style="background: linear-gradient(90deg, var(--theme-primary) 0%, var(--theme-primary-soft, #6366f1) 100%)"></div>

        <div class="px-5 py-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                {{-- Title & Meta details --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                        {{-- Status chip --}}
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full border {{ $sc['bg'] }} {{ $sc['border'] }} {{ $sc['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} inline-block"></span>
                            {{ ucwords(str_replace('_', ' ', $quotation->status)) }}
                        </span>

                        {{-- Quotation number badge --}}
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $quotation->quotation_number }}
                        </span>

                        @if($isShortlisted)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fa-solid fa-star text-amber-500 text-[10px]"></i> Shortlisted
                            </span>
                        @endif

                        @if($quotation->status === 'awarded')
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-trophy text-emerald-600 text-[10px]"></i> Awarded
                            </span>
                        @endif

                        @if($validityPassed)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 border border-red-200">
                                <i class="fa-solid fa-clock text-[10px]"></i> Validity Expired
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl font-bold text-gray-900 leading-snug mb-1">
                        {{ $quotation->supplierAccount?->supplierProfile?->display_name ?? 'Supplier Quotation' }}
                    </h1>

                    <p class="text-xs text-gray-500 mb-2.5">
                        Quotation for RFQ:
                        <a href="{{ route('buyer.rfqs.show', $quotation->rfq) }}" class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                            {{ $quotation->rfq?->title ?? 'RFQ #' . $quotation->rfq_id }}
                        </a>
                        <span class="font-mono text-gray-400">({{ $quotation->rfq?->rfq_number }})</span>
                    </p>

                    {{-- Compact info strip --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-gray-400"></i>
                            Submitted {{ $quotation->submitted_at?->format('d M Y') ?? $quotation->created_at->format('d M Y') }}
                        </span>

                        <span class="flex items-center gap-1.5 font-bold text-indigo-700">
                            <i class="fa-solid fa-coins text-indigo-500"></i>
                            {{ $quotation->currency_code }} {{ number_format($quotation->grand_total, 2) }}
                        </span>

                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-boxes-stacked text-gray-400"></i>
                            {{ $quotation->items->count() }} {{ Str::plural('item', $quotation->items->count()) }}
                        </span>

                        @if($quotation->lead_time_days)
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-truck-fast text-gray-400"></i>
                                {{ $quotation->lead_time_days }} days lead time
                            </span>
                        @endif

                        @if($quotation->valid_until)
                            <span class="flex items-center gap-1.5 {{ $validityPassed ? 'text-red-600 font-semibold' : '' }}">
                                <i class="fa-regular fa-clock {{ $validityPassed ? 'text-red-500' : 'text-gray-400' }}"></i>
                                Valid until {{ $quotation->valid_until->format('d M Y') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Quick navigation links --}}
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('buyer.quotations.compare', $quotation->rfq) }}"
                       class="inline-flex items-center gap-1.5 text-xs text-indigo-700 font-semibold px-3 py-2 rounded-lg border border-indigo-200 bg-indigo-50/60 hover:bg-indigo-100 transition-colors">
                        <i class="fa-solid fa-scale-balanced text-[10px]"></i> Compare Quotes
                    </a>
                    <a href="{{ route('buyer.rfqs.show', $quotation->rfq) }}"
                       class="inline-flex items-center gap-1.5 text-xs text-gray-600 hover:text-gray-900 font-medium px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Return to RFQ
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Alpine Tab Controller wrapping Main Layout ───────────────────── --}}
    <div x-data="{ tab: '{{ request('_tab', 'items') }}' }">

        {{-- ── Full-Width Tab Bar ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-xs mb-5 overflow-hidden">
            <div class="flex items-center gap-0.5 overflow-x-auto px-2 py-1.5">
                <button type="button" @click="tab = 'items'"
                        :class="tab === 'items' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm whitespace-nowrap transition-all">
                    <i class="fa-solid fa-boxes-stacked text-[11px]"></i>
                    Quoted Items ({{ $quotation->items->count() }})
                </button>

                <button type="button" @click="tab = 'terms'"
                        :class="tab === 'terms' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm whitespace-nowrap transition-all">
                    <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                    Commercial &amp; Terms
                </button>

                <button type="button" @click="tab = 'proposal'"
                        :class="tab === 'proposal' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm whitespace-nowrap transition-all">
                    <i class="fa-solid fa-file-lines text-[11px]"></i>
                    Proposal &amp; Documents
                    @php $docCount = $quotation->getMedia('combined_document')->count(); @endphp
                    @if($docCount > 0)
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-indigo-100 text-indigo-700">{{ $docCount }}</span>
                    @endif
                </button>

                <button type="button" @click="tab = 'activity'"
                        :class="tab === 'activity' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm whitespace-nowrap transition-all">
                    <i class="fa-solid fa-clock-rotate-left text-[11px]"></i>
                    Supplier Activity ({{ $supplierActivity->count() }})
                </button>

                @if($quotation->revisions->isNotEmpty())
                    <button type="button" @click="tab = 'revisions'"
                            :class="tab === 'revisions' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm whitespace-nowrap transition-all">
                        <i class="fa-solid fa-history text-[11px]"></i>
                        Revisions ({{ $quotation->revisions->count() }})
                    </button>
                @endif
            </div>
        </div>

        {{-- ── 3. 8 / 4 Grid Layout ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

            {{-- ═══════ LEFT: Tab Content (8 cols) ═══════ --}}
            <div class="lg:col-span-8 min-w-0 space-y-5">

                {{-- ── TAB 1: Quoted Items ── --}}
                <div x-show="tab === 'items'" class="space-y-5">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-boxes-stacked text-indigo-600 text-sm"></i>
                                <h3 class="text-sm font-bold text-gray-900">Quoted Line Items &amp; Pricing</h3>
                            </div>
                            <span class="text-xs text-gray-500">{{ $quotation->items->count() }} item(s)</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-50/80 text-[11px] font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
                                    <tr>
                                        <th class="px-5 py-3">Item Details</th>
                                        <th class="px-4 py-3 text-right">Quantity</th>
                                        <th class="px-4 py-3 text-right">Unit Price</th>
                                        <th class="px-5 py-3 text-right">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($quotation->items as $item)
                                        @php
                                            $offers = $item->offers;
                                            $selectedOfferId = $offers->firstWhere('is_selected', true)?->id
                                                ?? $offers->firstWhere('is_primary', true)?->id;
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-5 py-3.5">
                                                <div class="flex items-start gap-2.5">
                                                    <span class="w-6 h-6 rounded-md bg-gray-100 text-gray-700 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-semibold text-gray-900 leading-snug">{{ $item->item_name }}</p>
                                                        @if($item->rfqItem?->isRequirement())
                                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 mt-1">
                                                                <i class="fa-solid fa-file-invoice text-amber-600 text-[9px]"></i> Requirement
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3.5 text-sm text-gray-700 text-right whitespace-nowrap">
                                                {{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}
                                                <span class="text-gray-500 text-xs">{{ $item->unit?->symbol ?? $item->unit?->name ?? 'units' }}</span>
                                            </td>
                                            <td class="px-4 py-3.5 text-sm text-gray-700 text-right font-medium whitespace-nowrap">
                                                {{ number_format($item->unit_price, 2) }}
                                            </td>
                                            <td class="px-5 py-3.5 text-sm font-bold text-gray-900 text-right whitespace-nowrap">
                                                {{ number_format($item->line_total, 2) }}
                                            </td>
                                        </tr>

                                        {{-- Offers for this product --}}
                                        @if($offers->isNotEmpty())
                                            <tr class="bg-gray-50/50">
                                                <td colspan="4" class="px-5 py-3.5">
                                                    @if($offers->count() > 1)
                                                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-2.5 flex items-center gap-1.5">
                                                            <i class="fa-solid fa-layer-group text-indigo-500"></i>
                                                            {{ $offers->count() }} offers provided for this product &mdash; choose which one to award:
                                                        </p>
                                                    @endif

                                                    <div class="space-y-2">
                                                        @foreach($offers as $offer)
                                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border rounded-xl p-3 shadow-2xs transition-all"
                                                                 style="{{ $offer->id === $selectedOfferId ? 'border-color:var(--theme-primary); background-color: #faf5ff;' : '' }}">
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                                        <span class="text-xs font-bold text-gray-900">{{ $offer->product_name }}</span>
                                                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200">
                                                                            {{ ucfirst(str_replace('_', ' ', $offer->offer_method)) }}
                                                                        </span>
                                                                        @if($offer->is_primary)
                                                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                                                <i class="fa-solid fa-star text-[9px] mr-0.5"></i> Supplier's Primary
                                                                            </span>
                                                                        @endif
                                                                        @if($offer->id === $selectedOfferId && $offer->is_selected)
                                                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                                <i class="fa-solid fa-check text-[9px] mr-0.5"></i> Selected For Award
                                                                            </span>
                                                                        @endif
                                                                    </div>

                                                                    @if($offer->description)
                                                                        <p class="text-xs text-gray-600 mt-1 line-clamp-2 leading-relaxed">{{ $offer->description }}</p>
                                                                    @endif

                                                                    @if($offer->getMedia('document')->isNotEmpty())
                                                                        <div class="flex flex-wrap gap-2 mt-2">
                                                                            @foreach($offer->getMedia('document') as $doc)
                                                                                <a href="{{ $doc->getUrl() }}" target="_blank"
                                                                                   class="inline-flex items-center gap-1.5 text-[11px] font-medium px-2 py-0.5 rounded-md bg-gray-50 border border-gray-200 text-gray-700 hover:border-purple-300 hover:text-purple-700 transition-colors">
                                                                                    <i class="fa-solid fa-paperclip text-purple-500 text-[10px]"></i>
                                                                                    <span>{{ $doc->file_name }}</span>
                                                                                    <span class="text-gray-400">({{ $doc->human_readable_size }})</span>
                                                                                </a>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                                                                    <div class="text-left sm:text-right">
                                                                        <p class="text-xs font-bold text-gray-900">{{ number_format($offer->total_price, 2) }} {{ $quotation->currency_code }}</p>
                                                                        <p class="text-[11px] text-gray-400">{{ $offer->delivery_time ? $offer->delivery_time . ' days delivery' : 'Delivery: standard' }}</p>
                                                                    </div>

                                                                    @if(\Illuminate\Support\Facades\Gate::allows('selectOffer', $quotation) && $offers->count() > 1)
                                                                        <form method="POST" action="{{ route('buyer.quotations.items.offers.select', [$quotation, $item, $offer]) }}" class="shrink-0">
                                                                            @csrf
                                                                            <button type="submit" {{ $offer->is_selected ? 'disabled' : '' }}
                                                                                    class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-all {{ $offer->is_selected ? 'bg-emerald-600 text-white border-emerald-600 cursor-default shadow-2xs' : 'border-gray-300 text-gray-700 hover:bg-gray-100' }}">
                                                                                {{ $offer->is_selected ? 'Selected' : 'Select Offer' }}
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">
                                                No items quoted in this quotation.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Item Financial Summary Footer --}}
                        <div class="bg-gray-50/70 border-t border-gray-100 p-5">
                            <div class="max-w-xs ml-auto space-y-2 text-xs">
                                <div class="flex justify-between text-gray-600">
                                    <span>Items Subtotal</span>
                                    <span class="font-semibold text-gray-800">{{ number_format($quotation->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Applicable Tax</span>
                                    <span class="font-semibold text-gray-800">{{ number_format($quotation->tax_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping &amp; Logistics</span>
                                    <span class="font-semibold text-gray-800">{{ number_format($quotation->shipping_charge, 2) }}</span>
                                </div>
                                @if($quotation->discount_amount > 0)
                                    <div class="flex justify-between text-emerald-700">
                                        <span>Discount</span>
                                        <span class="font-semibold">-{{ number_format($quotation->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-sm font-bold text-gray-900 pt-2 border-t border-gray-200">
                                    <span>Grand Total</span>
                                    <span class="text-indigo-700">{{ $quotation->currency_code }} {{ number_format($quotation->grand_total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── TAB 2: Commercial & Terms ── --}}
                <div x-show="tab === 'terms'" x-cloak class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Delivery & Logistics Card --}}
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-gray-100">
                                <i class="fa-solid fa-truck-ramp-box text-indigo-600 text-sm"></i>
                                <h3 class="text-sm font-bold text-gray-900">Delivery &amp; Logistics</h3>
                            </div>
                            <dl class="space-y-3 text-xs">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Lead Time</dt>
                                    <dd class="text-gray-900 font-bold">{{ $quotation->lead_time_days ? $quotation->lead_time_days . ' days' : 'As agreed' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Expected Delivery</dt>
                                    <dd class="text-gray-900 font-semibold">{{ $quotation->expected_delivery_date?->format('d M Y') ?? 'Not specified' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Quotation Valid Until</dt>
                                    <dd class="text-gray-900 font-semibold {{ $validityPassed ? 'text-red-600' : '' }}">{{ $quotation->valid_until?->format('d M Y') ?? 'Not specified' }}</dd>
                                </div>
                                @if($quotation->deliveryAddresses->isNotEmpty())
                                    <div class="pt-2 border-t border-gray-100">
                                        <dt class="text-gray-500 mb-1">Delivery Address</dt>
                                        <dd class="text-gray-800 bg-gray-50 p-2.5 rounded-lg border border-gray-100 leading-relaxed">
                                            {{ $quotation->deliveryAddresses->first()->address_line_1 }}
                                            @if($quotation->deliveryAddresses->first()->city), {{ $quotation->deliveryAddresses->first()->city }}@endif
                                            @if($quotation->deliveryAddresses->first()->postal_code) {{ $quotation->deliveryAddresses->first()->postal_code }}@endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Terms & Conditions Card --}}
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-gray-100">
                                <i class="fa-solid fa-file-contract text-indigo-600 text-sm"></i>
                                <h3 class="text-sm font-bold text-gray-900">Commercial Terms</h3>
                            </div>
                            <dl class="space-y-3 text-xs">
                                <div>
                                    <dt class="text-gray-500 mb-0.5">Payment Terms</dt>
                                    <dd class="text-gray-900 font-medium bg-gray-50 p-2 rounded-lg border border-gray-100">{{ $quotation->payment_terms ?? 'Standard commercial terms' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 mb-0.5">Warranty Terms</dt>
                                    <dd class="text-gray-900 font-medium bg-gray-50 p-2 rounded-lg border border-gray-100">{{ $quotation->warranty_terms ?? 'Manufacturer warranty' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 mb-0.5">Support &amp; SLA Terms</dt>
                                    <dd class="text-gray-900 font-medium bg-gray-50 p-2 rounded-lg border border-gray-100">{{ $quotation->support_terms ?? 'Standard support' }}</dd>
                                </div>
                                <div class="flex justify-between pt-1 border-t border-gray-100">
                                    <dt class="text-gray-500">RFQ Version Responded</dt>
                                    <dd class="text-gray-900 font-bold">v{{ $quotation->rfq_version_no }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- ── TAB 3: Proposal & Documents ── --}}
                <div x-show="tab === 'proposal'" x-cloak class="space-y-5">
                    @if($quotation->proposal)
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                            <div class="flex items-center gap-2 pb-3 mb-3 border-b border-gray-100">
                                <i class="fa-solid fa-file-lines text-indigo-600 text-sm"></i>
                                <h3 class="text-sm font-bold text-gray-900">Proposal Summary</h3>
                            </div>
                            <div class="text-xs text-gray-700 leading-relaxed whitespace-pre-line bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                                {{ $quotation->proposal }}
                            </div>
                        </div>
                    @endif

                    {{-- Supporting Documents --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-paperclip text-indigo-600 text-sm"></i>
                                <h3 class="text-sm font-bold text-gray-900">Supporting Documents</h3>
                            </div>
                            <span class="text-xs text-gray-400">Official attachments</span>
                        </div>

                        @if($quotation->getMedia('combined_document')->isEmpty())
                            <p class="text-xs text-gray-400">No overall quotation documents attached by the supplier.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($quotation->getMedia('combined_document') as $doc)
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ str_starts_with($doc->mime_type ?? '', 'image/') ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                                <i class="fa-solid {{ str_starts_with($doc->mime_type ?? '', 'image/') ? 'fa-file-image' : 'fa-file-pdf' }} text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-gray-900 truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</p>
                                                <p class="text-[11px] text-gray-400 font-mono">{{ $doc->human_readable_size }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ $doc->getUrl() }}" target="_blank"
                                           class="shrink-0 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors inline-flex items-center gap-1">
                                            <i class="fa-solid fa-download text-[10px]"></i> View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($quotation->rejection_comment)
                        <div class="bg-red-50 rounded-xl border border-red-200 p-5">
                            <h4 class="text-sm font-bold text-red-900 flex items-center gap-2 mb-2">
                                <i class="fa-solid fa-circle-exclamation text-red-600"></i> Rejection Reason
                            </h4>
                            <p class="text-xs text-red-800 leading-relaxed">{{ $quotation->rejection_comment }}</p>
                            @if($quotation->rejected_at)
                                <p class="text-[11px] text-red-600 mt-2 font-medium">Recorded {{ $quotation->rejected_at->format('d M Y, h:i A') }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ── TAB 4: Supplier Activity ── --}}
                <div x-show="tab === 'activity'" x-cloak class="space-y-5">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left text-indigo-600 text-sm"></i>
                                <h3 class="text-sm font-bold text-gray-900">Supplier Activity Timeline</h3>
                            </div>
                            <span class="text-xs text-gray-400">{{ $supplierActivity->count() }} action(s)</span>
                        </div>

                        @if($supplierActivity->isEmpty())
                            <p class="text-xs text-gray-400">No supplier activity recorded yet.</p>
                        @else
                            <div class="max-h-[500px] overflow-y-auto pr-2 custom-vertical-scrollbar [scrollbar-gutter:stable]">
                                <ul class="relative pl-1 -mb-1">
                                    @foreach($supplierActivity as $activity)
                                        <li class="flex items-start gap-3 pb-5 relative group">
                                            @if(!$loop->last)
                                                <span class="absolute left-3.5 top-7 bottom-0 w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 {{ $activity->colorClass() }} shadow-xs relative z-10 ring-2 ring-white">
                                                <i class="fa-solid {{ $activity->icon() }} text-[11px]"></i>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-0.5">
                                                <p class="text-xs font-semibold text-gray-900 leading-snug">
                                                    {{ $activity->label() }}
                                                    @if($activity->user)
                                                        <span class="text-[11px] font-normal text-gray-500">by {{ $activity->user->name }}</span>
                                                    @endif
                                                </p>
                                                @if($activity->message)
                                                    <p class="mt-1 text-[11px] text-gray-600 bg-gray-50 border border-gray-100 rounded px-2.5 py-1.5 italic">
                                                        {{ $activity->message }}
                                                    </p>
                                                @endif
                                                <p class="text-[11px] text-gray-400 mt-0.5" title="{{ $activity->created_at->format('M d, Y H:i') }}">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── TAB 5: Revisions (if any) ── --}}
                @if($quotation->revisions->isNotEmpty())
                    <div x-show="tab === 'revisions'" x-cloak class="space-y-4">
                        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-5">
                            <div class="flex items-center gap-2 pb-3 mb-4 border-b border-gray-100">
                                <i class="fa-solid fa-history text-indigo-600 text-sm"></i>
                                <h3 class="text-sm font-bold text-gray-900">Quotation Revision History</h3>
                            </div>

                            <div x-data="{ openRev: null }" class="divide-y divide-gray-100 -mx-5 -mb-5">
                                @foreach($quotation->revisions as $rev)
                                    <div>
                                        <button type="button" @click="openRev = openRev === {{ $rev->id }} ? null : {{ $rev->id }}"
                                                class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-gray-50 transition-colors">
                                            <div>
                                                <span class="text-xs font-bold text-gray-900">Revision #{{ $rev->revision_no }}</span>
                                                <span class="text-xs text-gray-400 ml-2">&middot; {{ $rev->created_at->format('d M Y, h:i A') }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-bold text-indigo-700">{{ $rev->currency_code }} {{ number_format($rev->grand_total, 2) }}</span>
                                                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="openRev === {{ $rev->id }} && 'rotate-180'"></i>
                                            </div>
                                        </button>
                                        <div x-show="openRev === {{ $rev->id }}" x-cloak class="px-5 pb-4 bg-gray-50/50">
                                            @if($rev->change_summary)
                                                <p class="text-xs text-gray-600 mb-2 italic">Summary: {{ $rev->change_summary }}</p>
                                            @endif
                                            <ul class="space-y-1 text-xs text-gray-700">
                                                @foreach($rev->items as $rItem)
                                                    <li class="flex justify-between py-1 border-b border-gray-100 last:border-0">
                                                        <span>{{ $rItem->item_name }} &times; {{ (float)$rItem->quantity }}</span>
                                                        <span class="font-medium">{{ number_format($rItem->line_total, 2) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>{{-- end left col --}}

            {{-- ═══════ RIGHT: Sidebar (4 cols) ═══════ --}}
            <div class="lg:col-span-4 space-y-4">

                {{-- ── 1. Actions Card (Right Side as requested) ───────────── --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-amber-500 text-sm"></i>
                            <h3 class="text-sm font-bold text-gray-900">Quotation Actions</h3>
                        </div>
                        <span class="text-[11px] text-gray-400">Decisions</span>
                    </div>

                    <div class="p-3 space-y-2">
                        {{-- Primary Award Button --}}
                        @can('award', $quotation)
                            <button type="button" @click="$dispatch('open-modal-award')"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all group">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-trophy text-amber-300"></i>
                                    Award This Quotation
                                </span>
                                <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-0.5 transition-transform"></i>
                            </button>
                        @endcan

                        {{-- Shortlist Toggle --}}
                        @can('shortlist', $quotation)
                            @if($isShortlisted)
                                <form method="POST" action="{{ route('buyer.quotations.unshortlist', $quotation) }}" class="w-full">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-colors text-left">
                                        <i class="fa-solid fa-star text-amber-500 w-4 text-center"></i>
                                        Remove from Shortlist
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('buyer.quotations.shortlist', $quotation) }}" class="w-full">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-bold text-gray-700 hover:bg-amber-50 hover:text-amber-800 border border-gray-200 transition-colors text-left">
                                        <i class="fa-regular fa-star text-amber-500 w-4 text-center"></i>
                                        Add to Shortlist
                                    </button>
                                </form>
                            @endif
                        @endcan

                        {{-- Request Revision --}}
                        @can('requestRevision', $quotation)
                            <button type="button" @click="$dispatch('open-modal-revision')"
                                    class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 border border-gray-200 transition-colors text-left">
                                <i class="fa-solid fa-rotate text-indigo-500 w-4 text-center"></i>
                                Request Revision
                            </button>
                        @endcan

                        {{-- Reject Quotation --}}
                        @can('reject', $quotation)
                            <button type="button" @click="$dispatch('open-modal-reject')"
                                    class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-bold text-red-600 hover:bg-red-50 border border-red-200 transition-colors text-left">
                                <i class="fa-solid fa-circle-xmark text-red-500 w-4 text-center"></i>
                                Reject Quotation
                            </button>
                        @endcan

                        {{-- Leave Review --}}
                        @if($canReview)
                            <button type="button" @click="$dispatch('open-modal-review')"
                                    class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-bold text-purple-700 hover:bg-purple-50 border border-purple-200 transition-colors text-left">
                                <i class="fa-solid fa-comment-dots text-purple-500 w-4 text-center"></i>
                                Leave a Review
                            </button>
                        @endif

                        {{-- Secondary Actions Divider --}}
                        <div class="pt-2 border-t border-gray-100 space-y-1.5">
                            <a href="{{ route('buyer.quotations.compare', $quotation->rfq) }}"
                               class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 hover:text-indigo-600 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-scale-balanced text-gray-400 w-4 text-center"></i>
                                Compare with Other Quotes
                            </a>
                            <a href="{{ route('buyer.rfqs.show', $quotation->rfq) }}"
                               class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                                <i class="fa-solid fa-arrow-left text-gray-400 w-4 text-center"></i>
                                Return to RFQ
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ── 2. Financial Summary Card ────────────────────────────── --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-indigo-600 text-sm"></i>
                            <h3 class="text-sm font-bold text-gray-900">Financial Summary</h3>
                        </div>
                        <span class="text-[11px] font-mono text-gray-400">{{ $quotation->currency_code }}</span>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="text-center py-2 bg-indigo-50/50 rounded-xl border border-indigo-100">
                            <span class="text-[11px] text-gray-500 font-medium block">Total Quoted</span>
                            <span class="text-xl font-bold text-indigo-700">
                                {{ $quotation->currency_code }} {{ number_format($quotation->grand_total, 2) }}
                            </span>
                        </div>

                        <dl class="space-y-2 text-xs pt-1">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-gray-900">{{ number_format($quotation->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span class="font-semibold text-gray-900">{{ number_format($quotation->tax_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                <span class="font-semibold text-gray-900">{{ number_format($quotation->shipping_charge, 2) }}</span>
                            </div>
                            @if($quotation->discount_amount > 0)
                                <div class="flex justify-between text-emerald-700">
                                    <span>Discount</span>
                                    <span class="font-semibold">-{{ number_format($quotation->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-gray-500 pt-2 border-t border-gray-100">
                                <span>Lead Time</span>
                                <span class="font-medium text-gray-900">{{ $quotation->lead_time_days ? $quotation->lead_time_days . ' days' : 'As agreed' }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Validity</span>
                                <span class="font-medium {{ $validityPassed ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $quotation->valid_until?->format('d M Y') ?? '—' }}
                                </span>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- ── 3. Supplier Card ─────────────────────────────────────── --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-building text-gray-600 text-sm"></i>
                            <h3 class="text-sm font-bold text-gray-900">Supplier Profile</h3>
                        </div>
                    </div>

                    <div class="p-4 space-y-3 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm shrink-0">
                                {{ strtoupper(substr($quotation->supplierAccount?->supplierProfile?->display_name ?? 'S', 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-gray-900 text-sm truncate">
                                    {{ $quotation->supplierAccount?->supplierProfile?->display_name ?? 'Supplier' }}
                                </h4>
                                @if($quotation->supplierAccount?->supplierProfile?->legal_name)
                                    <p class="text-gray-400 text-[11px] truncate">{{ $quotation->supplierAccount->supplierProfile->legal_name }}</p>
                                @endif
                            </div>
                        </div>

                        @if($quotation->supplierAccount?->supplierProfile?->country)
                            <div class="flex items-center gap-1.5 text-gray-500">
                                <i class="fa-solid fa-location-dot text-gray-400"></i>
                                <span>{{ $quotation->supplierAccount->supplierProfile->country->name }}</span>
                            </div>
                        @endif

                        <div class="pt-2 border-t border-gray-100">
                            <a href="{{ route('buyer.suppliers.show', $quotation->supplierAccount) }}"
                               class="w-full text-center block py-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-indigo-700 font-semibold transition-colors">
                                View Full Supplier Profile &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ── 4. Supplier Activity Widget ─────────────────────────── --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-gray-500 text-sm"></i>
                            <h3 class="text-sm font-bold text-gray-900">Supplier Activity</h3>
                        </div>
                        @if($supplierActivity->isNotEmpty())
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-gray-100 text-gray-700 rounded-full">{{ $supplierActivity->count() }}</span>
                        @endif
                    </div>

                    <div class="p-4">
                        @if($supplierActivity->isEmpty())
                            <p class="text-xs text-gray-400">No supplier activity recorded yet.</p>
                        @else
                            <div class="max-h-[300px] overflow-y-auto pr-2 custom-vertical-scrollbar [scrollbar-gutter:stable]">
                                <ul class="relative pl-1 -mb-1">
                                    @foreach($supplierActivity as $activity)
                                        <li class="flex items-start gap-2.5 pb-3.5 relative group">
                                            @if(!$loop->last)
                                                <span class="absolute left-3 top-6 bottom-0 w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $activity->colorClass() }} shadow-2xs relative z-10 ring-2 ring-white">
                                                <i class="fa-solid {{ $activity->icon() }} text-[10px]"></i>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-0.5">
                                                <p class="text-xs font-semibold text-gray-900 leading-snug">
                                                    {{ $activity->label() }}
                                                </p>
                                                @if($activity->message)
                                                    <p class="mt-1 text-[11px] text-gray-600 bg-gray-50 border border-gray-100 rounded px-2 py-1 italic">
                                                        {{ $activity->message }}
                                                    </p>
                                                @endif
                                                <p class="text-[10px] text-gray-400 mt-0.5" title="{{ $activity->created_at->format('M d, Y H:i') }}">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <button type="button" @click="tab = 'activity'"
                                    class="w-full text-center text-xs font-semibold text-indigo-600 hover:text-indigo-800 pt-3 mt-1 border-t border-gray-100 block">
                                View Full Activity Log &rarr;
                            </button>
                        @endif
                    </div>
                </div>

            </div>{{-- end right col --}}

        </div>
    </div>

    {{-- ── 4. Action Modals ────────────────────────────────────────────────── --}}

    @can('requestRevision', $quotation)
        <x-backend.modal id="revision" title="Request a Quotation Revision">
            <form method="POST" action="{{ route('buyer.quotations.request-revision', $quotation) }}">
                @csrf
                <p class="text-xs text-gray-600 mb-3 leading-relaxed">
                    Specify the modifications or pricing adjustments required. The supplier will be notified and can submit an updated quotation.
                </p>
                <x-backend.textarea name="requested_changes" label="What changes are required?" required placeholder="e.g. Please reconsider unit pricing for item #2, or adjust delivery schedule to within 14 days." />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-xs font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg">Send Request</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @can('reject', $quotation)
        <x-backend.modal id="reject" title="Reject this Quotation">
            <form method="POST" action="{{ route('buyer.quotations.reject', $quotation) }}">
                @csrf
                <p class="text-xs text-gray-600 mb-3 leading-relaxed">
                    Rejecting this quotation will mark it as not selected. The supplier will be informed.
                </p>
                <x-backend.textarea name="reason" label="Reason for rejection" required placeholder="e.g. Budget exceeded or delivery time does not align with requirement." />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-xs font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-xs font-bold px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">Confirm Rejection</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @can('award', $quotation)
        <x-backend.modal id="award" title="Award Quotation to Supplier">
            <form method="POST" action="{{ route('buyer.quotations.award', $quotation) }}">
                @csrf
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-3.5 mb-4 text-xs text-amber-900">
                    <p class="font-bold flex items-center gap-1.5 mb-1">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Next Steps After Award
                    </p>
                    <p class="leading-relaxed">
                        The supplier will have a limited deadline to accept or decline this award. Upon acceptance, a Purchase Order will automatically be created.
                    </p>
                </div>

                <x-backend.textarea name="award_note" label="Note to supplier (optional)" placeholder="e.g. Congratulations, we look forward to working with you on this project." />

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-xs font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg flex items-center gap-1.5">
                        <i class="fa-solid fa-trophy text-amber-300"></i> Confirm Award
                    </button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @if($canReview)
        <x-backend.modal id="review" title="Leave a Supplier Review">
            <form method="POST" action="{{ route('buyer.reviews.store-for-quotation', $quotation) }}" x-data="{ rating: 5 }">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Rating</label>
                    <div class="flex items-center gap-1">
                        <template x-for="star in [1,2,3,4,5]" :key="star">
                            <button type="button" @click="rating = star" class="text-xl transition-colors" :class="star <= rating ? 'text-amber-400' : 'text-gray-200'">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        </template>
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                </div>
                <x-backend.input name="title" label="Title (optional)" placeholder="e.g. Prompt responses and competitive pricing" />
                <div class="mt-3">
                    <x-backend.textarea name="comment" label="Comment (optional)" placeholder="Write your feedback regarding this supplier's proposal..." />
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-xs font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg">Submit Review</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
