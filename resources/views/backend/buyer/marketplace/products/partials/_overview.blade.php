{{-- Overview tab: description + tier pricing. Expects $listing. --}}
<div>
    <h1 class="text-lg font-bold text-gray-900">{{ $listing->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">{{ $listing->mainCategory?->name }} @if($listing->brand) &middot; {{ $listing->brand->name }} @endif</p>
</div>

@if($listing->short_description)
    <p class="text-sm text-gray-600 mt-4">{{ $listing->short_description }}</p>
@endif

@if($listing->description)
    <div class="mt-4 pt-4 border-t border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description</h3>
        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $listing->description }}</p>
    </div>
@endif

@if($listing->tierPrices->isNotEmpty())
    <div class="mt-4 pt-4 border-t border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tier Pricing</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-xs text-gray-500 uppercase"><th class="text-left py-2">Quantity</th><th class="text-right py-2">Unit Price</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($listing->tierPrices as $tier)
                    <tr>
                        <td class="py-2">{{ rtrim(rtrim((string) $tier->min_quantity, '0'), '.') }}@if($tier->max_quantity) - {{ rtrim(rtrim((string) $tier->max_quantity, '0'), '.') }}@else+@endif</td>
                        <td class="py-2 text-right font-medium">{{ number_format($tier->unit_price, 2) }} {{ $tier->currency_code }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($listing->short_description === null && $listing->description === null && $listing->tierPrices->isEmpty())
    <p class="text-sm text-gray-400">No additional overview details provided for this product.</p>
@endif
