{{-- Quantity Break / Tier Pricing card. See listing-preview.blade.php for the expected variables. --}}
@php
    $globalTiers = $listing->allTierPrices->whereNull('listing_variant_id')->values();
    $variantTierGroups = $listing->allTierPrices->whereNotNull('listing_variant_id')->groupBy('listing_variant_id');
@endphp
@if($globalTiers->isNotEmpty() || $variantTierGroups->isNotEmpty())
    <x-backend.form-card title="Quantity Break / Tier Pricing" description="To change tier pricing, use Edit Listing above.">
        @if($globalTiers->isNotEmpty())
            <div class="mb-5 last:mb-0">
                <h4 class="text-xs font-bold text-gray-800 mb-2">Global Tiers</h4>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                                <th class="px-4 py-2.5 font-semibold">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($globalTiers as $tp)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-gray-800">{{ number_format($tp->min_quantity, 0) }} &ndash; {{ $tp->max_quantity ? number_format($tp->max_quantity, 0) : '∞' }} units</td>
                                    <td class="px-4 py-2.5 font-bold text-indigo-700">{{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @foreach($variantTierGroups as $variantId => $tiers)
            @php($variant = $listing->variants->firstWhere('id', $variantId))
            <div class="mb-5 last:mb-0">
                <h4 class="text-xs font-bold text-gray-800 mb-2">{{ $variant?->name ?? 'Variant #' . $variantId }}</h4>
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-4 py-2.5 font-semibold">Quantity Range</th>
                                <th class="px-4 py-2.5 font-semibold">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tiers as $tp)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-gray-800">{{ number_format($tp->min_quantity, 0) }} &ndash; {{ $tp->max_quantity ? number_format($tp->max_quantity, 0) : '∞' }} units</td>
                                    <td class="px-4 py-2.5 font-bold text-indigo-700">{{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </x-backend.form-card>
@endif
