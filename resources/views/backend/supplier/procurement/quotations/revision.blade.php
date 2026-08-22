@extends('backend.layouts.supplier')

@section('title', 'Revise Quotation — ' . $quotation->quotation_number)
@section('breadcrumb', 'Quotations / Revise')

@section('body')

    <x-backend.page-header title="Revise Quotation" subtitle="Submit revision #{{ $quotation->current_revision_no + 1 }} for {{ $quotation->quotation_number }}" />

    @if($revisionRequest)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-bold text-amber-900">Buyer's Revision Request Note:</h4>
            <p class="text-xs text-amber-800 mt-1">{{ $revisionRequest->requested_changes }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('supplier.quotations.revision.store', $quotation) }}"
          x-data="{
              items: [
                  @foreach($quotation->items as $i => $item)
                  {
                      id: {{ $item->id }},
                      rfq_item_id: {{ $item->rfq_item_id ?? 'null' }},
                      item_name: '{{ addslashes($item->item_name) }}',
                      quantity: {{ (float)$item->quantity }},
                      unit_price: {{ (float)$item->unit_price }},
                      lead_time_days: {{ (int)$item->lead_time_days }},
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

                <x-backend.form-card title="Revision Explanation">
                    <x-backend.textarea name="change_summary" label="What changes are you making in this revision?" required placeholder="e.g. Reduced unit prices by 5% and expedited lead time to 7 days." />
                </x-backend.form-card>

                <x-backend.form-card title="Updated Line Items &amp; Pricing">
                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-bold text-gray-900" x-text="item.item_name"></h4>
                                    <span class="text-xs text-gray-500 font-semibold" x-text="'Qty: ' + item.quantity"></span>
                                </div>
                                <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                                <input type="hidden" :name="'items['+index+'][rfq_item_id]'" :value="item.rfq_item_id">
                                <input type="hidden" :name="'items['+index+'][item_name]'" :value="item.item_name">
                                <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price <span class="text-red-500">*</span></label>
                                        <input type="number" step="0.01" min="0" required :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white font-semibold">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Lead Time (Days)</label>
                                        <input type="number" min="0" :name="'items['+index+'][lead_time_days]'" x-model.number="item.lead_time_days" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Line Total</label>
                                        <div class="text-sm font-bold text-indigo-700 pt-2" x-text="'{{ $quotation->currency_code }} ' + (item.quantity * (item.unit_price || 0)).toFixed(2)"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-backend.form-card>

                <x-backend.form-card title="Commercial Proposal">
                    <div class="space-y-4">
                        <x-backend.textarea name="proposal" label="Proposal / Remarks" :value="old('proposal', $quotation->proposal)" />
                    </div>
                </x-backend.form-card>

            </div>

            <div class="xl:col-span-4 space-y-6">
                <x-backend.form-card title="Revised Total">
                    <div class="space-y-3">
                        <div class="flex justify-between py-1 border-b border-gray-100 text-sm">
                            <span class="text-gray-600 font-medium">Grand Total</span>
                            <span class="font-bold text-indigo-700 text-base" x-text="'{{ $quotation->currency_code }} ' + calculateTotal().toFixed(2)"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full text-sm font-bold py-3 rounded-xl flex items-center justify-center gap-2 mt-6 shadow-sm">
                        <i class="fa-solid fa-paper-plane"></i> Submit Revised Quotation
                    </button>
                </x-backend.form-card>
            </div>

        </div>
    </form>

@endsection
