{{--
    Read-only recap for Step 3 ("Preview & Submit") — pure display, reads the
    same Alpine state (items/offers, title/description/delivery/terms) every
    other step already writes to. No new calculation logic; formatMoney()/
    grandTotal() are the same functions the sidebar "Quotation Summary" card
    already uses.
--}}
<dl class="divide-y divide-gray-100 text-sm">
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Title</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="title || '—'"></dd>
    </div>
    <div class="py-3" x-show="description">
        <dt class="text-gray-500 mb-1">Description</dt>
        <dd class="text-gray-800 leading-relaxed" x-text="description"></dd>
    </div>

    <div class="py-3">
        <dt class="text-gray-500 mb-2">Items (<span x-text="items.length"></span>)</dt>
        <div class="space-y-2">
            <template x-for="item in items" :key="item._localKey">
                <div class="border border-gray-200 rounded-lg p-2.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="item.item_name || 'Untitled item'"></p>
                        <p class="text-[11px] text-gray-400" x-text="(item.offers && item.offers.length > 1) ? (item.offers.length + ' offers') : methodLabel(item._responseMethod)"></p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold text-gray-900" x-text="item.quantity + ' @ ' + formatMoney(item.unit_price || 0)"></p>
                        <p class="text-[11px] text-gray-400" x-text="formatMoney((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0))"></p>
                    </div>
                </div>
            </template>
            <template x-if="items.length === 0">
                <p class="text-gray-400 italic">No items priced yet.</p>
            </template>
        </div>
    </div>

    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Overall Delivery Time</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="expectedDeliveryDate || '—'"></dd>
    </div>
    <div class="py-3">
        <dt class="text-gray-500 mb-2">Delivery Address(es)</dt>
        <ul class="space-y-1 text-right">
            <template x-for="(addr, idx) in deliveryAddresses" :key="idx">
                <li class="text-gray-900" x-text="'Address ' + (idx + 1) + ': ' + (addr.address || 'not specified')"></li>
            </template>
        </ul>
    </div>

    <div class="py-3 flex items-start justify-between gap-4" x-show="warrantyTerms">
        <dt class="text-gray-500 shrink-0">Warranty Terms</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="warrantyTerms"></dd>
    </div>
    <div class="py-3 flex items-start justify-between gap-4" x-show="supportTerms">
        <dt class="text-gray-500 shrink-0">Support Terms</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="supportTerms"></dd>
    </div>
    <div class="py-3 flex items-start justify-between gap-4" x-show="paymentTerms">
        <dt class="text-gray-500 shrink-0">Payment Terms</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="paymentTerms"></dd>
    </div>
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-700 font-semibold shrink-0">Grand Total</dt>
        <dd class="font-bold text-indigo-700 text-base text-right" x-text="formatMoney(grandTotal())"></dd>
    </div>
</dl>
