@props(['tiers', 'currency' => null, 'highlightQuantity' => null])

@if($tiers->isNotEmpty())
    <div class="overflow-x-auto rounded-xl border" style="border-color:var(--fe-border);">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="background:var(--fe-surface-soft);">
                    <th class="px-4 py-2.5 font-semibold" style="color:var(--fe-text-muted);">Quantity</th>
                    <th class="px-4 py-2.5 font-semibold text-right" style="color:var(--fe-text-muted);">Unit Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tiers as $tier)
                    @php
                        $isHighlighted = $highlightQuantity && $highlightQuantity >= $tier->min_quantity && ($tier->max_quantity === null || $highlightQuantity <= $tier->max_quantity);
                    @endphp
                    <tr class="border-t" style="border-color:var(--fe-border); {{ $isHighlighted ? 'background:var(--fe-primary-soft);' : '' }}">
                        <td class="px-4 py-2.5" style="color:var(--fe-text);">
                            {{ rtrim(rtrim(number_format($tier->min_quantity, 2), '0'), '.') }}{{ $tier->max_quantity ? ' – '.rtrim(rtrim(number_format($tier->max_quantity, 2), '0'), '.') : '+' }}
                        </td>
                        <td class="px-4 py-2.5 text-right font-semibold" style="color:var(--fe-text);">
                            {{ $currency ?? $tier->currency_code }} {{ number_format($tier->unit_price, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
