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
                            @foreach($quotation->items as $item)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $item->item_name }}</p>
                                        @if($item->is_alternative)
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">Alternative</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-600 text-right">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit?->symbol }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-600 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 text-sm font-medium text-gray-900 text-right">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
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
                                    <ul class="mt-2 space-y-1">
                                        @foreach($revision->items as $item)
                                            <li class="text-xs text-gray-600 flex justify-between">
                                                <span>{{ $item->item_name }} &times; {{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</span>
                                                <span>{{ number_format($item->line_total, 2) }}</span>
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
