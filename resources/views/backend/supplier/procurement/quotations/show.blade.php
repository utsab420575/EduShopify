@extends('backend.layouts.supplier')

@section('title', 'Quotation — ' . $quotation->quotation_number)
@section('breadcrumb', 'Quotations / ' . $quotation->quotation_number)

@section('body')

    @php
        $requestedItems = $quotation->items->where('is_optional_addon', false);
        $addonItems = $quotation->items->where('is_optional_addon', true);
        $sourceLabel = fn ($item) => $item->is_alternative
            ? 'Alternative Offer'
            : ($item->offered_listing_id ? 'From Your Listing' : 'Custom Offer');
        $sourceClass = fn ($item) => $item->is_alternative
            ? 'bg-amber-50 text-amber-700 border-amber-200'
            : ($item->offered_listing_id ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200');

        $quotedRfqItemIds = $quotation->items->pluck('rfq_item_id')->filter()->all();
        $unquotedRfqItems = $quotation->rfq ? $quotation->rfq->items->whereNotIn('id', $quotedRfqItemIds)->values() : collect();
        $canReviseForUpdate = ($versionChanged || $unquotedRfqItems->isNotEmpty()) && in_array($quotation->status, ['submitted', 'under_review', 'revised', 'shortlisted']);
    @endphp

    <x-backend.page-header title="Quotation {{ $quotation->quotation_number }}" subtitle="For RFQ: {{ $quotation->rfq?->title ?? 'RFQ #' . $quotation->rfq_id }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if($quotation->status === 'draft')
                    <a href="{{ route('supplier.quotations.edit', $quotation) }}" class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('supplier.quotations.submit', $quotation) }}">
                        @csrf
                        <button type="submit" class="btn-primary text-xs font-bold px-3 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Submit Quotation
                        </button>
                    </form>
                @elseif($quotation->status === 'revision_requested' || $canReviseForUpdate)
                    <a href="{{ route('supplier.quotations.revision.create', $quotation) }}" class="btn-primary text-xs font-bold px-3 py-2 rounded-lg flex items-center gap-1.5 {{ $quotation->status === 'revision_requested' ? 'animate-pulse' : '' }}">
                        <i class="fa-solid fa-rotate"></i> Revise Quote
                    </a>
                @endif
                @if(in_array($quotation->status, ['submitted', 'under_review', 'revision_requested', 'revised', 'shortlisted']))
                    <form method="POST" action="{{ route('supplier.quotations.withdraw', $quotation) }}" onsubmit="return confirm('Withdraw this quotation? You will no longer be considered for award.')">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                            Withdraw Quote
                        </button>
                    </form>
                @endif
            </div>
        </x-slot:actions>
    </x-backend.page-header>

    {{-- Unquoted Items Notice Banner --}}
    @if($unquotedRfqItems->isNotEmpty())
        <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
            <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                <div class="flex items-start gap-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fa-solid fa-bell text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-950">Additional Items Added to this RFQ</h4>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            The buyer has updated this RFQ (v{{ $quotation->rfq->current_version_no }}) with <strong>{{ $unquotedRfqItems->count() }} additional item(s)</strong> that are not in your quotation:
                        </p>
                        <div class="mt-2.5 flex flex-wrap gap-2">
                            @foreach($unquotedRfqItems as $uItem)
                                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg bg-white border border-amber-200 text-amber-900 font-medium shadow-2xs">
                                    <i class="fa-solid fa-circle-plus text-amber-600 text-[10px]"></i>
                                    <span class="font-bold">{{ $uItem->item_name }}</span>
                                    <span class="text-gray-500">({{ (float)$uItem->quantity }} {{ $uItem->unit?->name ?? $uItem->custom_unit ?? 'units' }})</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if($canReviseForUpdate)
                    <a href="{{ route('supplier.quotations.revision.create', $quotation) }}" class="btn-primary text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-1.5 shrink-0 shadow-sm whitespace-nowrap">
                        <i class="fa-solid fa-rotate"></i> Revise Quote to Include Items
                    </a>
                @elseif($quotation->status === 'draft')
                    <a href="{{ route('supplier.quotations.edit', $quotation) }}" class="btn-primary text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-1.5 shrink-0 shadow-sm whitespace-nowrap">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Quote to Include Items
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if($errors->has('rfq_version'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-bold text-red-900 flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation"></i> This RFQ Has Changed</h4>
            <p class="text-xs text-red-800 mt-1">{{ $errors->first('rfq_version') }}</p>
            <div class="mt-3 space-y-1">
                @foreach($changeLogs as $log)
                    <p class="text-xs text-red-700">
                        v{{ $log->from_version_no }} &rarr; v{{ $log->to_version_no }}:
                        {{ collect($log->changed_fields)->map(fn ($f) => ucwords(str_replace('_', ' ', $f)))->implode(', ') }}
                    </p>
                @endforeach
            </div>
            <form method="POST" action="{{ route('supplier.quotations.submit', $quotation) }}" class="mt-3">
                @csrf
                <input type="hidden" name="acknowledge_version_change" value="1">
                <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    I've Reviewed the Changes — Submit Anyway
                </button>
            </form>
        </div>
    @elseif($versionChanged && $quotation->status === 'draft')
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5"><i class="fa-solid fa-circle-info"></i> RFQ Updated Since You Started</h4>
            <p class="text-xs text-amber-800 mt-1">The RFQ moved from version {{ $quotation->rfq_version_no }} to version {{ $quotation->rfq->current_version_no }}. Review the changes before submitting.</p>
        </div>
    @elseif($versionChanged && $canReviseForUpdate && $unquotedRfqItems->isEmpty())
        <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 mb-6 shadow-sm flex flex-col sm:flex-row items-start justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5"><i class="fa-solid fa-circle-info"></i> RFQ Updated by Buyer</h4>
                <p class="text-xs text-amber-800 mt-1">The buyer updated this RFQ (moved from v{{ $quotation->rfq_version_no }} to v{{ $quotation->rfq->current_version_no }}). You can submit a revised quotation if needed.</p>
            </div>
            <a href="{{ route('supplier.quotations.revision.create', $quotation) }}" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg shrink-0">
                <i class="fa-solid fa-rotate mr-1"></i> Revise Quotation
            </a>
        </div>
    @endif

    @if($quotation->status === 'revision_requested')
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate"></i> Buyer Requested a Quotation Revision
                </h4>
                <p class="text-xs text-amber-800 mt-1">{{ $quotation->revisionRequests->first()?->requested_changes ?? 'Please review your pricing or terms and submit a revised quote.' }}</p>
            </div>
            <a href="{{ route('supplier.quotations.revision.create', $quotation) }}" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg shrink-0">
                Revise Quotation
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Left / Items & Commercials --}}
        <div class="xl:col-span-8 space-y-6">

            <x-backend.form-card title="Quotation Overview">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <x-backend.status-badge :status="$quotation->status" />
                    </div>
                    <div>
                        <span class="text-gray-400 block">Revision No.</span>
                        <span class="font-bold text-gray-800">{{ $quotation->current_revision_no > 0 ? '#'.$quotation->current_revision_no : 'Not yet submitted' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Total Quoted</span>
                        <span class="font-bold text-indigo-700 text-sm">{{ $quotation->currency_code }} {{ number_format($quotation->grand_total, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Delivery Lead Time</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->lead_time_days ? $quotation->lead_time_days . ' days' : 'As requested' }}</span>
                    </div>
                </div>

                @if($quotation->proposal)
                    <div class="text-xs text-gray-700 whitespace-pre-line bg-gray-50 p-3 rounded-lg border border-gray-100 mb-4">
                        <span class="font-bold block mb-1 text-gray-900">Proposal Summary:</span>
                        {{ $quotation->proposal }}
                    </div>
                @endif

                {{-- Terms --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-gray-400 block">Warranty:</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->warranty_terms ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Support:</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->support_terms ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Payment:</span>
                        <span class="font-semibold text-gray-800">{{ $quotation->payment_terms ?? 'Standard' }}</span>
                    </div>
                </div>
            </x-backend.form-card>

            {{-- Items Quoted, with buyer-vs-supplier attribute comparison --}}
            <x-backend.form-card title="Quoted Items">
                <div class="space-y-4">
                    @forelse($requestedItems as $item)
                        @php($rfqItem = $item->rfqItem)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-gray-900">{{ $item->item_name }}</p>
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full border {{ $sourceClass($item) }}">{{ $sourceLabel($item) }}</span>
                                    </div>
                                    @if($rfqItem)
                                        <p class="text-xs text-gray-400 mt-0.5">Responding to: {{ $rfqItem->item_name }} ({{ $rfqItem->category?->name ?? 'No category' }})</p>
                                    @endif
                                    @if($item->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ $item->description }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0 text-xs">
                                    <p class="text-gray-800 font-semibold">{{ (float) $item->quantity }} {{ $item->unit?->symbol ?? $item->custom_unit }}</p>
                                    <p class="text-gray-500 mt-0.5">{{ $quotation->currency_code }} {{ number_format($item->unit_price, 2) }} / unit</p>
                                    <p class="text-indigo-700 font-bold mt-0.5">{{ $quotation->currency_code }} {{ number_format($item->line_total, 2) }}</p>
                                </div>
                            </div>

                            @if($item->attributeValues->isNotEmpty())
                                <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5">
                                    @foreach($item->attributeValues as $value)
                                        @php($requestedValue = $rfqItem?->attributeValues->firstWhere('attribute_id', $value->attribute_id))
                                        @php($differs = $requestedValue && $requestedValue->formattedValue() !== $value->formattedValue())
                                        <div class="flex items-center justify-between text-[11px] gap-2">
                                            <span class="text-gray-500">{{ $value->attribute?->name }}</span>
                                            <span class="text-right">
                                                <span class="text-gray-400">{{ $requestedValue?->formattedValue() ?? '—' }}</span>
                                                <i class="fa-solid fa-arrow-right text-gray-300 mx-1"></i>
                                                <span class="{{ $differs ? 'text-amber-700 font-semibold' : 'text-gray-700 font-semibold' }}">{{ $value->formattedValue() }}</span>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No items quoted yet.</p>
                    @endforelse

                    @if($unquotedRfqItems->isNotEmpty())
                        <div class="mt-4 pt-4 border-t-2 border-dashed border-amber-200">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h5 class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                        Buyer's Requested Items Not in this Quotation ({{ $unquotedRfqItems->count() }})
                                    </h5>
                                    <p class="text-[11px] text-gray-500 mt-0.5">These items were added to the RFQ after or separately from this quote.</p>
                                </div>
                                @if($canReviseForUpdate)
                                    <a href="{{ route('supplier.quotations.revision.create', $quotation) }}" class="btn-primary text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                                        <i class="fa-solid fa-plus text-[10px]"></i> Add to Quote
                                    </a>
                                @endif
                            </div>
                            <div class="space-y-2">
                                @foreach($unquotedRfqItems as $uItem)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50/50 border border-amber-200 text-xs">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900">{{ $uItem->item_name }}</span>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $uItem->listing_id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                                    {{ $uItem->listing_id ? 'Marketplace Product' : 'Custom Requirement' }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $uItem->category?->name ?? 'General' }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="font-bold text-gray-800">{{ (float)$uItem->quantity }} {{ $uItem->unit?->name ?? $uItem->custom_unit ?? 'units' }}</span>
                                            @if($uItem->estimated_unit_price)
                                                <p class="text-[11px] text-indigo-600 font-semibold">Target: {{ $quotation->currency_code }} {{ number_format((float)$uItem->estimated_unit_price, 2) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </x-backend.form-card>

            @if($addonItems->isNotEmpty())
                <x-backend.form-card title="Optional Add-Ons" description="Offered in addition to the buyer's requested items.">
                    <div class="space-y-2">
                        @foreach($addonItems as $addon)
                            <div class="flex items-center justify-between p-3 bg-amber-50/40 border border-amber-200 rounded-lg text-xs">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $addon->item_name }}</p>
                                    <p class="text-gray-500">{{ (float) $addon->quantity }} {{ $addon->unit?->symbol }} &times; {{ $quotation->currency_code }} {{ number_format($addon->unit_price, 2) }}</p>
                                </div>
                                <span class="font-bold text-amber-700">{{ $quotation->currency_code }} {{ number_format($addon->line_total, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif

            <x-backend.form-card title="Commercial Summary">
                <dl class="space-y-2 text-sm max-w-xs ml-auto">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="text-gray-800">{{ $quotation->currency_code }} {{ number_format($quotation->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd class="text-gray-800">{{ $quotation->currency_code }} {{ number_format($quotation->tax_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="text-gray-800">-{{ $quotation->currency_code }} {{ number_format($quotation->discount_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Shipping</dt><dd class="text-gray-800">{{ $quotation->currency_code }} {{ number_format($quotation->shipping_charge, 2) }}</dd></div>
                    <div class="flex justify-between pt-2 border-t border-gray-100"><dt class="font-semibold text-gray-700">Grand Total</dt><dd class="font-bold text-indigo-700">{{ $quotation->currency_code }} {{ number_format($quotation->grand_total, 2) }}</dd></div>
                </dl>
            </x-backend.form-card>

            {{-- Revision History --}}
            @if($quotation->revisions->isNotEmpty())
                <x-backend.form-card title="Revision History">
                    <div class="space-y-3">
                        @foreach($quotation->revisions as $rev)
                            <div class="p-3 bg-gray-50 rounded-lg text-xs flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-gray-900">Revision #{{ $rev->revision_no }} &middot; {{ $rev->created_at->format('d M Y, h:i A') }}</p>
                                    @if($rev->change_summary)
                                        <p class="text-gray-600 mt-0.5">{{ $rev->change_summary }}</p>
                                    @endif
                                </div>
                                <span class="font-bold text-gray-800">{{ $rev->currency_code }} {{ number_format($rev->grand_total, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif

        </div>

        {{-- Right / Buyer & RFQ Card --}}
        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Buyer Details">
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-gray-900 text-sm">{{ $quotation->rfq?->buyerAccount?->buyerProfile?->organization_name ?? $quotation->rfq?->buyerAccount?->display_name }}</p>
                    <p class="text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i>{{ $quotation->rfq?->buyerAccount?->buyerProfile?->country?->name ?? 'Location specified in RFQ' }}</p>
                    <div class="pt-3 border-t border-gray-100">
                        <a href="{{ route('supplier.opportunities.show', $quotation->rfq) }}" class="text-indigo-600 font-semibold hover:underline">
                            View Original RFQ &rarr;
                        </a>
                    </div>
                </div>
            </x-backend.form-card>

            <x-backend.form-card title="RFQ Version">
                <p class="text-xs text-gray-500">This quotation responds to <span class="font-semibold text-gray-800">version {{ $quotation->rfq_version_no }}</span>.</p>
                @if($versionChanged)
                    <p class="text-xs text-amber-700 mt-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i>The RFQ is now at version {{ $quotation->rfq->current_version_no }}.</p>
                @else
                    <p class="text-xs text-emerald-700 mt-1"><i class="fa-solid fa-circle-check mr-1"></i>Up to date with the current RFQ version.</p>
                @endif
            </x-backend.form-card>
        </div>

    </div>

@endsection
