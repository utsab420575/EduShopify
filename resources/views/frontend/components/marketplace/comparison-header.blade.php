{{--
    One product column's header cell — included inside the parent's
    <template x-for="(item, idx) in listings">, so `item`/`idx` are already
    in Alpine scope (same pattern as the buyer RFQ module's _item.blade.php
    split, built earlier this session for the same Blade-compiler-size
    reason). All data here comes from the JSON the page already fetched —
    never re-requested per column.
--}}
<th class="align-top px-4 py-4 text-left" style="width:220px;min-width:220px;border-left:1px solid var(--fe-border);">
    <div class="relative">
        <button type="button" @click="remove(item.listing_id, item.variant_id)"
                class="comparison-hide-print fe-focus-ring absolute -top-1 -right-1 w-7 h-7 rounded-full flex items-center justify-center text-xs z-10"
                style="background:var(--fe-surface-soft);color:var(--fe-text-muted);"
                title="Remove from comparison" aria-label="Remove from comparison">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <a :href="'/listing/' + item.slug" class="block mb-2">
            <div class="aspect-square rounded-xl overflow-hidden flex items-center justify-center" style="background:var(--fe-surface-soft);">
                <img :src="item.thumb_url" x-show="item.thumb_url" class="w-full h-full object-contain p-2" :alt="item.name">
                <i class="fa-solid fa-box text-3xl" x-show="!item.thumb_url" style="color:var(--fe-text-subtle);"></i>
            </div>
        </a>

        <a :href="'/listing/' + item.slug" class="fe-focus-ring block text-sm font-semibold leading-snug mb-1 hover:underline" style="color:var(--fe-text);" x-text="item.name"></a>

        <p class="text-xs mb-1" style="color:var(--fe-text-muted);" x-show="item.brand" x-text="item.brand"></p>

        <a :href="item.supplier_slug ? '/supplier/' + item.supplier_slug : '#'" class="text-xs font-medium hover:underline block mb-2" style="color:var(--fe-primary);" x-text="item.supplier_name || 'Supplier'"></a>

        {{-- Variant picker --}}
        <template x-if="item.variants && item.variants.length > 1">
            <select @change="changeVariant(item.listing_id, $event.target.value)" class="fe-focus-ring w-full text-xs rounded-lg border px-2 py-1.5 mb-2" style="border-color:var(--fe-border);">
                <option value="" :selected="!item.variant_id">Base listing</option>
                <template x-for="v in item.variants" :key="v.id">
                    <option :value="v.id" :selected="v.id === item.variant_id" x-text="v.label"></option>
                </template>
            </select>
        </template>

        {{-- Price --}}
        <template x-if="item.pricing_type === 'fixed' && item.price !== null">
            <div class="mb-2">
                <p class="text-base font-bold" style="color:var(--fe-text);">
                    <span x-text="item.currency_code"></span> <span x-text="item.price.toFixed(2)"></span>
                </p>
                <p class="text-[11px] line-through" style="color:var(--fe-text-subtle);" x-show="item.compare_at_price" x-text="item.currency_code + ' ' + (item.compare_at_price ? item.compare_at_price.toFixed(2) : '')"></p>
            </div>
        </template>
        <template x-if="!(item.pricing_type === 'fixed' && item.price !== null)">
            <p class="text-sm font-semibold mb-2" style="color:var(--fe-primary);">Request Quote</p>
        </template>

        <p class="text-[11px] mb-3" style="color:var(--fe-text-muted);" x-show="item.moq" x-text="'MOQ: ' + item.moq + (item.unit ? ' ' + item.unit : '')"></p>

        {{-- Actions --}}
        <div class="comparison-hide-print space-y-1.5">
            <a :href="'/listing/' + item.slug" class="fe-focus-ring block text-center px-2 py-1.5 rounded-lg text-xs font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">View Product</a>

            <a :href="{{ auth()->check() ? "'/buyer/rfqs/create?listing=' + item.listing_id" : "'/handoff/request-quote/' + item.slug" }}"
               class="fe-btn-primary fe-focus-ring block text-center px-2 py-1.5 rounded-lg text-xs font-semibold">Request Quotation</a>

            <a :href="'/listing/' + item.slug + '?contact=1'"
               class="fe-focus-ring block w-full text-center px-2 py-1.5 rounded-lg text-xs font-semibold border"
               style="border-color:var(--fe-border-strong);color:var(--fe-text);">Message Supplier</a>
        </div>
    </div>
</th>
