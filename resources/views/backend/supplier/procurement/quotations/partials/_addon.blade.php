{{--
    One optional add-on line — a product/service the supplier offers that
    the buyer didn't request. No rfq_item_id, no buyer-requested comparison;
    kept visually and structurally separate so it's never mistaken for a
    response to (or a deviation from) an actual RFQ item. `addon` and
    `index` come from the parent x-for="(addon, index) in addons" scope.
--}}
<div class="border border-amber-200 bg-amber-50/30 rounded-lg p-4 mb-3 last:mb-0">
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-amber-700"><i class="fa-solid fa-plus-circle mr-1"></i>Optional Add-On <span x-text="index + 1"></span></span>
        <button type="button" @click="removeAddon(index)" class="text-red-500 hover:text-red-700 text-xs">
            <i class="fa-solid fa-trash"></i> Remove
        </button>
    </div>

    <input type="hidden" :name="'addons['+index+'][id]'" :value="addon.id ?? ''">
    <input type="hidden" :name="'addons['+index+'][is_optional_addon]'" value="1">
    <input type="hidden" :name="'addons['+index+'][is_alternative]'" value="0">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Item / Service Name</label>
            <input type="text" required :name="'addons['+index+'][item_name]'" x-model="addon.item_name" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Lead Time (Days)</label>
            <input type="number" min="0" :name="'addons['+index+'][lead_time_days]'" x-model.number="addon.lead_time_days" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="mt-2">
        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
        <textarea :name="'addons['+index+'][description]'" x-model="addon.description" rows="2" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-2">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
            <input type="number" step="0.001" min="0.001" :name="'addons['+index+'][quantity]'" x-model="addon.quantity" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
            <select :name="'addons['+index+'][unit_id]'" x-model="addon.unit_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-2 py-2 bg-white">
                <option value="">Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->symbol ?: $unit->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" required :name="'addons['+index+'][unit_price]'" x-model.number="addon.unit_price" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Tax Rate %</label>
            <input type="number" step="0.01" min="0" max="100" :name="'addons['+index+'][tax_rate]'" x-model.number="addon.tax_rate" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Discount</label>
            <input type="number" step="0.01" min="0" :name="'addons['+index+'][discount_amount]'" x-model.number="addon.discount_amount" class="focus-accent w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="mt-2 text-right text-xs text-gray-500">
        Line total: <span class="font-bold text-amber-700" x-text="formatMoney(lineTotal(addon))"></span>
    </div>
</div>
