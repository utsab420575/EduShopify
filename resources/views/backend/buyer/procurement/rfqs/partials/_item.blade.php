{{--
    One RFQ item card — extracted out of _form.blade.php's items x-for loop
    (a near-identical large Blade file hit a real Blade-compiler truncation
    bug earlier this session, so large item forms are kept split into
    includes rather than grown inline). `item` and `index` come from the
    parent x-for="(item, index) in items" scope.
--}}
<div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-gray-500">Item <span x-text="index + 1"></span></span>
            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                  :class="item.listing_id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200'"
                  x-text="sourceTypeLabel(item)"></span>
        </div>
        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-red-500 hover:text-red-700 text-xs">
            <i class="fa-solid fa-trash"></i> Remove
        </button>
    </div>

    <input type="hidden" :name="'items['+index+'][id]'" :value="item.id ?? ''">
    <input type="hidden" :name="'items['+index+'][listing_id]'" :value="item.listing_id ?? ''">

    <div class="relative mb-3">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Search Existing Listing <span class="text-gray-400 font-normal">(optional)</span></label>
        <div class="flex items-center gap-2">
            <input type="text" x-model="item._listingQuery" @input.debounce.400ms="searchListingsForItem(item)"
                   placeholder="Search marketplace products/services to prefill this item..."
                   class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            <button type="button" x-show="item.listing_id" @click="clearListingForItem(item)" class="text-xs text-gray-500 hover:text-gray-700 whitespace-nowrap px-2">
                Clear link
            </button>
        </div>
        <div x-show="item._listingResults.length > 0" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
            <template x-for="l in item._listingResults" :key="l.id">
                <button type="button" @click="selectListingForItem(item, l)" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center justify-between gap-2">
                    <span x-text="l.name"></span>
                    <span class="text-[11px] text-gray-400" x-text="l.category_name"></span>
                </button>
            </template>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
            <select :name="'items['+index+'][item_type]'" x-model="item.item_type" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                <option value="product">Product</option>
                <option value="service">Service</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Category <span class="text-gray-400 font-normal">(optional but recommended)</span>
            </label>
            <select :name="'items['+index+'][category_id]'" x-model="item.category_id" @change="onItemCategoryChange(item)" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                <option value="">Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-3">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Item Name</label>
        <input type="text" :name="'items['+index+'][item_name]'" x-model="item.item_name" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>

    <div class="mt-3">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Specifications / Description</label>
        <textarea :name="'items['+index+'][description]'" x-model="item.description" rows="2" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm"></textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity</label>
            <input type="number" step="0.001" min="0.001" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit</label>
            <select :name="'items['+index+'][unit_id]'" x-model="item.unit_id" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                <option value="">Select unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }} @if($unit->symbol)({{ $unit->symbol }})@endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Est. Unit Price</label>
            <input type="number" step="0.01" min="0" :name="'items['+index+'][estimated_unit_price]'" x-model="item.estimated_unit_price" class="focus-accent w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div x-show="item.category_id" x-cloak class="mt-3">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Specifications</label>
        @include('backend.buyer.procurement.rfqs.partials._item-attributes')
    </div>
</div>
