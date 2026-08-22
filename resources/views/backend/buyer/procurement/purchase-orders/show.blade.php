@extends('backend.layouts.buyer')

@section('title', 'Purchase Order ' . $purchaseOrder->po_number)
@section('breadcrumb', 'Procurement / Purchase Orders / ' . $purchaseOrder->po_number)

@section('body')

    <x-backend.page-header :title="'Purchase Order ' . $purchaseOrder->po_number" :subtitle="$purchaseOrder->rfq->title">
        <x-slot:actions>
            <x-backend.status-badge :status="$purchaseOrder->status" />
            @can('complete', $purchaseOrder)
                <button @click="$dispatch('open-modal-complete-po')" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Confirm Receipt &amp; Complete</button>
            @endcan
            @if($canReview)
                <button @click="$dispatch('open-modal-review-po')" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Leave a Review</button>
            @endif
        </x-slot:actions>
    </x-backend.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <x-backend.form-card title="Items">
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
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-600 text-right">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit?->symbol }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-600 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 text-sm font-medium text-gray-900 text-right">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 mt-4 pt-4 flex justify-between text-base font-bold text-gray-900">
                    <span>Grand Total</span><span>{{ number_format($purchaseOrder->grand_total, 2) }} {{ $purchaseOrder->currency_code }}</span>
                </div>
            </x-backend.form-card>

            <x-backend.form-card title="Status History">
                @if($purchaseOrder->statusHistory->isEmpty())
                    <p class="text-sm text-gray-400">No status changes recorded yet.</p>
                @else
                    <ul class="space-y-4">
                        @foreach($purchaseOrder->statusHistory as $entry)
                            <li class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full mt-1.5 shrink-0" style="background:var(--theme-primary)"></div>
                                <div>
                                    <p class="text-sm text-gray-800">
                                        <span class="font-medium">{{ ucwords(str_replace('_', ' ', $entry->old_status ?? 'created')) }}</span>
                                        &rarr; <span class="font-medium">{{ ucwords(str_replace('_', ' ', $entry->new_status)) }}</span>
                                    </p>
                                    @if($entry->comment)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $entry->comment }}</p>
                                    @endif
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $entry->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-backend.form-card>
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Supplier">
                <a href="{{ route('buyer.suppliers.show', $purchaseOrder->supplierAccount) }}" class="text-sm font-medium" style="color:var(--theme-primary)">
                    {{ $purchaseOrder->supplierAccount?->supplierProfile?->display_name }} &rarr;
                </a>
            </x-backend.form-card>

            <x-backend.form-card title="Payment">
                <p class="text-sm text-gray-500">Payment is arranged directly between you and the supplier — it is not processed through the platform.</p>
                <div class="mt-3 flex justify-between text-sm">
                    <span class="text-gray-500">Payment Status</span>
                    <x-backend.status-badge :status="$purchaseOrder->payment_status" />
                </div>
                @if($purchaseOrder->payment_method_note)
                    <p class="text-sm text-gray-600 mt-2">{{ $purchaseOrder->payment_method_note }}</p>
                @endif
            </x-backend.form-card>

            <x-backend.form-card title="Need Help?">
                <p class="text-sm text-gray-500 mb-3">Having an issue with this order? Open a support ticket or message the supplier directly.</p>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('buyer.tickets.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-center">Open Support Ticket</a>
                </div>
            </x-backend.form-card>
        </div>
    </div>

    @can('complete', $purchaseOrder)
        <x-backend.modal id="complete-po" title="Confirm Receipt &amp; Complete">
            <form method="POST" action="{{ route('buyer.purchase-orders.complete', $purchaseOrder) }}">
                @csrf
                <p class="text-sm text-gray-600 mb-4">This confirms you have received the order and marks it as completed. This cannot be undone.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Confirm &amp; Complete</button>
                </div>
            </form>
        </x-backend.modal>
    @endcan

    @if($canReview)
        <x-backend.modal id="review-po" title="Leave a Review">
            <form method="POST" action="{{ route('buyer.reviews.store-for-purchase-order', $purchaseOrder) }}" x-data="{ rating: 5 }">
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
