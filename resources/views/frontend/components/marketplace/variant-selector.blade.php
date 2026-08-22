@props(['variants'])

@if($variants->isNotEmpty())
    <div x-data="variantSelector({{ $variants->first()->id }})">
        <p class="text-sm font-medium mb-2" style="color:var(--fe-text);">Select an option</p>
        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Variant">
            @foreach($variants as $variant)
                <button
                    type="button"
                    role="radio"
                    :aria-checked="isSelected({{ $variant->id }}).toString()"
                    @click="select({{ $variant->id }})"
                    :class="isSelected({{ $variant->id }}) ? 'border-[--fe-primary] bg-[--fe-primary-soft] text-[--fe-primary]' : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                    class="fe-focus-ring px-3.5 py-2 rounded-lg border text-sm font-medium transition-colors"
                    @if($variant->stock_status === 'out_of_stock') disabled aria-disabled="true" style="opacity:.5;cursor:not-allowed;" @endif
                >
                    {{ $variant->name }}
                </button>
            @endforeach
        </div>

        @foreach($variants as $variant)
            <div x-show="isSelected({{ $variant->id }})" x-cloak class="mt-4 text-sm" style="color:var(--fe-text-muted);">
                <div class="flex flex-wrap gap-x-5 gap-y-1">
                    @if($variant->price)
                        <span class="font-semibold" style="color:var(--fe-text);">{{ $variant->currency_code }} {{ number_format($variant->price, 2) }}</span>
                    @endif
                    @if($variant->sku)
                        <span>SKU: {{ $variant->sku }}</span>
                    @endif
                    @if($variant->min_order_quantity)
                        <span>MOQ {{ rtrim(rtrim(number_format($variant->min_order_quantity, 2), '0'), '.') }}</span>
                    @endif
                    @if($variant->lead_time_days)
                        <span>Lead time {{ $variant->lead_time_days }}d</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
