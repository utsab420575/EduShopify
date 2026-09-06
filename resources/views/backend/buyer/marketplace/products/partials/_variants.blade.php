{{-- Variants tab: read-only list, no edit affordances (buyer view). Expects $listing. --}}
<div class="space-y-3">
    @foreach($listing->variants as $variant)
        <div class="flex items-center justify-between gap-4 p-3 rounded-lg border border-gray-100">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $variant->name }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    @foreach($variant->variantAttributes as $va)
                        <span class="text-[11px] text-gray-500">{{ $va->attribute?->name }}: <span class="font-medium text-gray-700">{{ $va->attributeValue?->value ?? $va->custom_value }}</span></span>
                    @endforeach
                </div>
                @if($variant->tierPrices->isNotEmpty())
                    <p class="text-[11px] text-indigo-600 mt-1"><i class="fa-solid fa-layer-group"></i> Volume pricing available</p>
                @endif
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-semibold text-gray-900">{{ number_format($variant->price, 2) }} {{ $variant->currency_code ?? $listing->currency_code }}</p>
                <p class="text-[11px] mt-0.5 {{ $variant->stock_status === 'out_of_stock' ? 'text-red-500' : 'text-emerald-600' }}">
                    <i class="fa-solid {{ $variant->stock_status === 'out_of_stock' ? 'fa-circle-xmark' : 'fa-circle-check' }}"></i>
                    {{ ['in_stock' => 'In Stock', 'limited' => 'Limited Stock', 'on_request' => 'Made to Order', 'out_of_stock' => 'Out of Stock'][$variant->stock_status] ?? 'Available' }}
                </p>
            </div>
        </div>
    @endforeach
</div>
