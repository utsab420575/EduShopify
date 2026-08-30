{{-- Volume Discount / Tier Pricing. See listings/_panel.blade.php for expected variables. --}}
@if($listing->allTierPrices->isNotEmpty())
    <x-backend.form-card title="Quantity Break / Tier Pricing (Volume Discounts)">
        <div class="overflow-x-auto -mx-5 -mb-5">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 border-y border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-5 py-2.5 font-semibold">Scope / Variant</th>
                        <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Unit Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($listing->allTierPrices as $tp)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-2.5 font-medium text-gray-700">
                                @if($tp->listing_variant_id && $tp->listingVariant)
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-semibold text-[11px] border border-indigo-100">
                                        Variant: {{ $tp->listingVariant->name }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-medium text-[11px]">
                                        Global (All Variants)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-800">
                                {{ (int)$tp->min_quantity }} &ndash; {{ $tp->max_quantity ? (int)$tp->max_quantity : '∞' }} units
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold text-indigo-700">
                                {{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-backend.form-card>
@endif
