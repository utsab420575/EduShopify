{{--
    Overview + Global (base-product) Tier Pricing.
    The 4-up stats (Type/Category/Price/Status) have been moved to the sidebar
    buy-box card. This partial now focuses on the listing descriptions and tiers.
    See listing-preview.blade.php for the expected variables.
--}}

{{-- Description Section --}}
@if($listing->short_description)
    <div class="mb-5 p-4 rounded-lg bg-indigo-50/60 border border-indigo-100">
        <p class="text-sm font-semibold text-gray-800 leading-relaxed">{{ $listing->short_description }}</p>
    </div>
@endif

@if($listing->description)
    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $listing->description }}</div>
@else
    <p class="text-sm text-gray-400 italic">No description added yet.
        <a href="{{ route('supplier.catalog.listings.edit', $listing) }}" class="text-indigo-600 font-semibold hover:underline not-italic">Add one in Edit Listing →</a>
    </p>
@endif

{{-- Global Tier Pricing Table --}}
@php
    $globalTiers = $listing->allTierPrices->whereNull('listing_variant_id')->values();
@endphp
@if($globalTiers->isNotEmpty())
    <div class="mt-6 pt-5 border-t border-gray-100">
        <div class="flex items-center gap-2 mb-3">
            <i class="fa-solid fa-layer-group text-indigo-500 text-xs"></i>
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Quantity Break / Tier Pricing</h4>
        </div>
        <p class="text-xs text-gray-400 mb-3">Volume pricing for the base product. To modify, use Edit Listing.</p>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                        <th class="px-4 py-2.5 font-semibold">Unit Price</th>
                        <th class="px-4 py-2.5 font-semibold text-right">Discount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($globalTiers as $tp)
                        @php
                            $discount = $listing->base_price && $tp->unit_price
                                ? round((1 - ($tp->unit_price / $listing->base_price)) * 100, 0)
                                : null;
                        @endphp
                        <tr class="{{ $loop->index % 2 === 0 ? 'bg-white' : 'bg-gray-50/40' }}">
                            <td class="px-4 py-2.5 font-medium text-gray-800">
                                {{ number_format($tp->min_quantity, 0) }} &ndash; {{ $tp->max_quantity ? number_format($tp->max_quantity, 0) : '∞' }} units
                            </td>
                            <td class="px-4 py-2.5 font-bold text-indigo-700">
                                {{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                @if($discount !== null && $discount > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                        −{{ $discount }}%
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
