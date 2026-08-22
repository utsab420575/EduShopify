@extends('backend.layouts.supplier')

@section('title', 'Submit Quotation — ' . $rfq->title)
@section('breadcrumb', 'Quotations / Submit Quotation')

@section('body')

    <x-backend.page-header title="Submit Quotation" subtitle="RFQ: {{ $rfq->rfq_number }} — {{ $rfq->title }}" />

    <form method="POST" action="{{ route('supplier.quotations.store', $rfq) }}"
          x-data="{
              items: [
                  @foreach($rfq->items as $i => $item)
                  {
                      rfq_item_id: {{ $item->id }},
                      item_name: '{{ addslashes($item->item_name) }}',
                      quantity: {{ (float)$item->quantity }},
                      unit_price: 0,
                      lead_time_days: 0,
                      is_alternative: false
                  },
                  @endforeach
              ],
              calculateTotal() {
                  return this.items.reduce((sum, item) => sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)), 0);
              }
          }">
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            <div class="xl:col-span-8 space-y-6">

                {{-- Items Quoted Table --}}
                <x-backend.form-card title="Line Items &amp; Pricing">
                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-bold text-gray-900" x-text="item.item_name"></h4>
                                    <span class="text-xs text-gray-500 font-semibold" x-text="'Qty: ' + item.quantity"></span>
                                </div>
                                <input type="hidden" :name="'items['+index+'][rfq_item_id]'" :value="item.rfq_item_id">
                                <input type="hidden" :name="'items['+index+'][item_name]'" :value="item.item_name">
                                <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price ({{ $rfq->currency_code ?? 'USD' }}) <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" min="0" required :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" placeholder="0.00" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white font-semibold">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Lead Time (Days)</label>
                                        <input type="number" min="0" :name="'items['+index+'][lead_time_days]'" x-model.number="item.lead_time_days" placeholder="0" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Line Subtotal</label>
                                        <div class="text-sm font-bold text-indigo-700 pt-2" x-text="'{{ $rfq->currency_code ?? 'USD' }} ' + (item.quantity * (item.unit_price || 0)).toFixed(2)"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-backend.form-card>

                {{-- Terms & Commercials --}}
                <x-backend.form-card title="Commercial Proposal &amp; Terms">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-backend.input type="number" name="lead_time_days" label="Overall Delivery Time (Days)" placeholder="e.g. 14" />
                            <x-backend.input type="date" name="valid_until" label="Quotation Validity Date" />
                        </div>
                        <x-backend.textarea name="proposal" label="Executive Summary / Proposal" placeholder="Explain your proposal, brand advantages, quality assurances..." />
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <x-backend.input name="warranty_terms" label="Warranty Terms" placeholder="e.g. 1 Year Standard" />
                            <x-backend.input name="support_terms" label="Support Terms" placeholder="e.g. 24/7 Phone Support" />
                            <x-backend.input name="payment_terms" label="Payment Terms" placeholder="e.g. Net 30" />
                        </div>
                    </div>
                </x-backend.form-card>

            </div>

            {{-- Summary Sidebar --}}
            <div class="xl:col-span-4 space-y-6">
                <x-backend.form-card title="Quotation Summary">
                    <div class="space-y-3">
                        <div class="flex justify-between py-1 border-b border-gray-100 text-xs">
                            <span class="text-gray-500">RFQ Currency</span>
                            <span class="font-bold text-gray-900">{{ $rfq->currency_code ?? 'USD' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-100 text-sm">
                            <span class="text-gray-600 font-medium">Grand Total</span>
                            <span class="font-bold text-indigo-700 text-base" x-text="'{{ $rfq->currency_code ?? 'USD' }} ' + calculateTotal().toFixed(2)"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full text-sm font-bold py-3 rounded-xl flex items-center justify-center gap-2 mt-6 shadow-sm">
                        <i class="fa-solid fa-paper-plane"></i> Submit Quotation to Buyer
                    </button>
                </x-backend.form-card>
            </div>

        </div>
    </form>

@endsection
