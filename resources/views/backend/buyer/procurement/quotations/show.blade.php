@extends('backend.layouts.buyer')

@section('title', 'Quotation ' . $quotation->quotation_number)
@section('breadcrumb', 'Procurement / Quotations / ' . $quotation->quotation_number)

@section('body')

    <x-backend.page-header :title="$quotation->supplierAccount?->supplierProfile?->display_name" :subtitle="'Quotation ' . $quotation->quotation_number . ' for ' . $quotation->rfq->title">
        <x-slot:actions>
            <x-backend.status-badge :status="$quotation->status" />
            @if($isShortlisted)
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-star"></i> Shortlisted</span>
            @endif
        </x-slot:actions>
    </x-backend.page-header>

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @can('shortlist', $quotation)
            @if($isShortlisted)
                <form method="POST" action="{{ route('buyer.quotations.unshortlist', $quotation) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Remove from Shortlist</button>
                </form>
            @else
                <form method="POST" action="{{ route('buyer.quotations.shortlist', $quotation) }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Shortlist</button>
                </form>
            @endif
        @endcan
        @can('requestRevision', $quotation)
            <button @click="$dispatch('open-modal-revision')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Request Revision</button>
        @endcan
        @can('reject', $quotation)
            <button @click="$dispatch('open-modal-reject')" class="text-sm font-medium px-4 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50">Reject</button>
        @endcan
        @can('award', $quotation)
            <button @click="$dispatch('open-modal-award')" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Award This Quotation</button>
        @endcan
        @if($canReview)
            <button @click="$dispatch('open-modal-review')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Leave a Review</button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <x-backend.form-card title="Quoted Items">
                <div class="-mx-5 -mb-5 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase">Item</th>
                                <th class="px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase text-right">Qty</th>
                                <th class="px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase text-right">Unit Price</th>
                                <th class="px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($quotation->items as $item)
                                @php
                                    $offers = $item->offers;
                                    $selectedOfferId = $offers->firstWhere('is_selected', true)?->id
                                        ?? $offers->firstWhere('is_primary', true)?->id;
                                @endphp
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                                        @if($item->rfqItem?->isRequirement())
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                                <i class="fa-solid fa-file-invoice text-[9px] mr-0.5"></i> Requirement
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-600 text-right">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit?->symbol }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-600 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 text-sm font-medium text-gray-900 text-right">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                                @if($offers->isNotEmpty())
                                    <tr class="bg-gray-50/60">
                                        <td colspan="4" class="px-5 py-3">
                                            @if($offers->count() > 1)
                                                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 mb-2">
                                                    {{ $offers->count() }} offers for this product — pick which one to award
                                                </p>
                                            @endif
                                            <div class="space-y-2">
                                                @foreach($offers as $offer)
                                                    <div class="flex items-center justify-between gap-3 bg-white border rounded-lg px-3 py-2"
                                                         style="{{ $offer->id === $selectedOfferId ? 'border-color:var(--theme-primary)' : '' }}"
                                                         class="{{ $offer->id === $selectedOfferId ? '' : 'border-gray-200' }}">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                                <span class="text-xs font-semibold text-gray-900">{{ $offer->product_name }}</span>
                                                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200">{{ ucfirst(str_replace('_', ' ', $offer->offer_method)) }}</span>
                                                                @if($offer->is_primary)
                                                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">Supplier's Primary</span>
                                                                @endif
                                                                @if($offer->id === $selectedOfferId && $offer->is_selected)
                                                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-check text-[9px]"></i> Your Selection</span>
                                                                @endif
                                                            </div>
                                                            @if($offer->description)
                                                                <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">{{ $offer->description }}</p>
                                                            @endif
                                                            @if($offer->getMedia('document')->isNotEmpty())
                                                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                                    @foreach($offer->getMedia('document') as $doc)
                                                                        <a href="{{ $doc->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-md bg-gray-50 border border-gray-200 text-gray-700 hover:border-purple-300 hover:text-purple-700">
                                                                            <i class="fa-solid fa-download text-[9px]"></i> {{ $doc->file_name }}
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="text-right shrink-0">
                                                            <p class="text-xs font-bold text-gray-900">{{ number_format($offer->total_price, 2) }}</p>
                                                            <p class="text-[10px] text-gray-400">{{ $offer->delivery_time ? $offer->delivery_time . ' days' : '—' }}</p>
                                                        </div>
                                                        @can('selectOffer', $quotation)
                                                            @if($offers->count() > 1)
                                                                <form method="POST" action="{{ route('buyer.quotations.items.offers.select', [$quotation, $item, $offer]) }}" class="shrink-0">
                                                                    @csrf
                                                                    <button type="submit" @disabled($offer->is_selected)
                                                                            class="text-[11px] font-semibold px-2.5 py-1.5 rounded-md border {{ $offer->is_selected ? 'bg-emerald-50 text-emerald-700 border-emerald-200 cursor-default' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                                                        {{ $offer->is_selected ? 'Selected' : 'Select' }}
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endcan
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-6 text-sm text-gray-400 text-center">No items quoted yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>{{ number_format($quotation->subtotal, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Tax</span><span>{{ number_format($quotation->tax_amount, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Shipping</span><span>{{ number_format($quotation->shipping_charge, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Discount</span><span>-{{ number_format($quotation->discount_amount, 2) }}</span></div>
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-1.5 border-t border-gray-100"><span>Grand Total</span><span>{{ number_format($quotation->grand_total, 2) }} {{ $quotation->currency_code }}</span></div>
                </div>
            </x-backend.form-card>

            @if($quotation->proposal)
                <x-backend.form-card title="Proposal">
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $quotation->proposal }}</p>
                </x-backend.form-card>
            @endif

            @if($quotation->getMedia('combined_document')->isNotEmpty())
                <x-backend.form-card title="Supporting Documents">
                    <div class="flex flex-wrap gap-2">
                        @foreach($quotation->getMedia('combined_document') as $doc)
                            <a href="{{ $doc->getUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1.5 rounded-md bg-gray-50 border border-gray-200 text-gray-700 hover:text-indigo-600">
                                <i class="fa-solid {{ str_starts_with($doc->mime_type ?? '', 'image/') ? 'fa-file-image text-emerald-500' : 'fa-file-pdf text-red-500' }}"></i>
                                <span class="font-medium">{{ $doc->file_name }}</span>
                                <span class="text-gray-400 font-mono">({{ $doc->human_readable_size }})</span>
                            </a>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif

            @if($quotation->rejection_comment)
                <x-backend.form-card title="Rejection Reason">
                    <p class="text-sm text-gray-600">{{ $quotation->rejection_comment }}</p>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Terms">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Lead Time</dt><dd class="text-gray-900 font-medium">{{ $quotation->lead_time_days ? $quotation->lead_time_days . ' days' : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Valid Until</dt><dd class="text-gray-900 font-medium">{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Warranty</dt><dd class="text-gray-900 font-medium text-right">{{ $quotation->warranty_terms ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Support</dt><dd class="text-gray-900 font-medium text-right">{{ $quotation->support_terms ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Payment Terms</dt><dd class="text-gray-900 font-medium text-right">{{ $quotation->payment_terms ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Revision</dt><dd class="text-gray-900 font-medium">#{{ $quotation->current_revision_no }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">RFQ Version</dt><dd class="text-gray-900 font-medium">v{{ $quotation->rfq_version_no }}</dd></div>
                </dl>
            </x-backend.form-card>

            @if($quotation->revisions->isNotEmpty())
                <x-backend.form-card title="Revision History">
                    <div x-data="{ open: null }" class="-mx-5 -mb-5 divide-y divide-gray-100">
                        @foreach($quotation->revisions as $revision)
                            <div>
                                <button type="button" @click="open = open === {{ $revision->id }} ? null : {{ $revision->id }}" class="w-full flex items-center justify-between px-5 py-3 text-left hover:bg-gray-50">
                                    <span class="text-sm font-medium text-gray-900">
                                        Revision {{ $revision->revision_no }}
                                        @if($revision->revision_no === $quotation->current_revision_no)
                                            <span class="ml-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">Current</span>
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $revision->created_at->format('d M Y') }}</span>
                                </button>
                                <div x-show="open === {{ $revision->id }}" x-cloak class="px-5 pb-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ number_format($revision->grand_total, 2) }} {{ $revision->currency_code }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Lead time: {{ $revision->lead_time_days ? $revision->lead_time_days . ' days' : '—' }} &middot; RFQ v{{ $revision->rfq_version_no }}</p>
                                    @if($revision->change_summary)
                                        <p class="text-xs text-gray-500 mt-1">{{ $revision->change_summary }}</p>
                                    @endif
                                    <ul class="mt-2 space-y-1.5">
                                        @foreach($revision->items as $item)
                                            <li class="text-xs text-gray-600">
                                                <div class="flex justify-between">
                                                    <span>{{ $item->item_name }} &times; {{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</span>
                                                    <span>{{ number_format($item->line_total, 2) }}</span>
                                                </div>
                                                @if($item->offers->count() > 1)
                                                    <ul class="mt-1 ml-3 space-y-0.5">
                                                        @foreach($item->offers as $offer)
                                                            <li class="flex justify-between text-[11px] text-gray-400">
                                                                <span>
                                                                    {{ $offer->product_name }}
                                                                    @if($offer->is_primary) <span class="text-indigo-500">(primary)</span> @endif
                                                                    @if($offer->is_selected) <span class="text-emerald-600">(selected)</span> @endif
                                                                </span>
                                                                <span>{{ number_format($offer->total_price, 2) }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                    <p class="text-[10px] text-gray-400 mt-2">By {{ $revision->createdBy?->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif

            <x-backend.form-card title="Supplier">
                <a href="{{ route('buyer.suppliers.show', $quotation->supplierAccount) }}" class="text-sm font-medium" style="color:var(--theme-primary)">
                    {{ $quotation->supplierAccount?->supplierProfile?->display_name }} &rarr;
                </a>
            </x-backend.form-card>
        </div>
    </div>

    @can('requestRevision', $quotation)
        <x-backend.modal id="revision" title="Request a Revision">
            <form method="POST" action="{{ route('buyer.quotations.request-revision', $quotation) }}">
                @csrf
                <x-backend.textarea name="requested_changes" label="What needs to change?" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Send Request</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @can('reject', $quotation)
        <x-backend.modal id="reject" title="Reject this Quotation">
            <form method="POST" action="{{ route('buyer.quotations.reject', $quotation) }}">
                @csrf
                <x-backend.textarea name="reason" label="Reason for rejection" required />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject Quotation</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @can('award', $quotation)
        <x-backend.modal id="award" title="Award this Quotation">
            <form method="POST" action="{{ route('buyer.quotations.award', $quotation) }}">
                @csrf
                <p class="text-sm text-gray-600 mb-4">The supplier will have a limited time to accept or reject this award. Once accepted, a Purchase Order will be created automatically.</p>
                <x-backend.textarea name="award_note" label="Note to supplier (optional)" />
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Confirm Award</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @if($canReview)
        <x-backend.modal id="review" title="Leave a Review">
            <form method="POST" action="{{ route('buyer.reviews.store-for-quotation', $quotation) }}" x-data="{ rating: 5 }">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rating</label>
                    <div class="flex items-center gap-1">
                        <template x-for="star in [1,2,3,4,5]" :key="star">
                            <button type="button" @click="rating = star" class="text-xl" :class="star <= rating ? 'text-amber-400' : 'text-gray-200'">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        </template>
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                </div>
                <x-backend.input name="title" label="Title (optional)" />
                <div class="mt-4">
                    <x-backend.textarea name="comment" label="Comment (optional)" />
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Submit Review</button>
                </div>
            </form>
        </x-backend.modal>
    @endif

@endsection
