{{-- Shared by step 4's Review card and the Preview modal — reads the same Alpine state either way. --}}
<dl class="divide-y divide-gray-100 text-sm">
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Title</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="title || '—'"></dd>
    </div>
    <div class="py-3">
        <dt class="text-gray-500 mb-2">Items (<span x-text="items.length"></span>)</dt>
        <ul class="space-y-1.5">
            <template x-for="(item, idx) in items" :key="idx">
                <li class="flex items-center justify-between gap-4 text-sm">
                    <span class="text-gray-900" x-text="item.item_name || 'Untitled item'"></span>
                    <span class="text-gray-500 shrink-0" x-text="item.quantity + (item.custom_unit ? ' ' + item.custom_unit : '')"></span>
                </li>
            </template>
        </ul>
    </div>
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Quotation Deadline</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="quotationDeadline || '—'"></dd>
    </div>
    <div class="py-3">
        <dt class="text-gray-500 mb-2">Delivery Address(es)</dt>
        <ul class="space-y-1 text-right">
            <li class="text-gray-900" x-text="deliveryAddress || 'Address 1: not specified'"></li>
            <template x-for="(addr, idx) in additionalAddresses" :key="idx">
                <li class="text-gray-900" x-text="'Address ' + (idx + 2) + ': ' + (addr.address || 'not specified')"></li>
            </template>
        </ul>
    </div>
    <div class="py-3 flex items-start justify-between gap-4">
        <dt class="text-gray-500 shrink-0">Suppliers</dt>
        <dd class="font-medium text-gray-900 text-right" x-text="isOpenMatchingMode() ? 'Open to eligible suppliers' : (suppliers.map(s => s.name).join(', ') || 'None selected yet')"></dd>
    </div>
</dl>
