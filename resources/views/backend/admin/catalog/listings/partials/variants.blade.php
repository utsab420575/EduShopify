{{-- Product Variants with inline tier pricing. Tab 3 (Pricing & Variants).
     See listings/_panel.blade.php for expected variables. --}}
@if($listing->variants->isEmpty())
    <div class="py-8 text-center text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
        <i class="fa-solid fa-layer-group text-2xl mb-2"></i>
        <p class="text-sm font-medium text-gray-500">No Variants</p>
        <p class="text-xs mt-1">This is a simple listing with a single SKU — pricing shown in the section above.</p>
    </div>
@else
    <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                <tr>
                    <th class="px-4 py-3 font-semibold w-14">Photo</th>
                    <th class="px-4 py-3 font-semibold">Variant & Attributes</th>
                    <th class="px-4 py-3 font-semibold">SKU</th>
                    <th class="px-4 py-3 font-semibold">Price</th>
                    <th class="px-4 py-3 font-semibold">Stock</th>
                    <th class="px-4 py-3 font-semibold text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($listing->variants as $variant)
                    @php
                        $variantCover  = $variant->images->firstWhere('pivot.is_primary', true) ?? $variant->images->first();
                        $variantTiers  = $variant->tierPrices ?? collect();
                    @endphp

                    {{-- Main variant row --}}
                    <tr class="hover:bg-gray-50/50 align-top">
                        <td class="px-4 py-3">
                            @if($variantCover)
                                <a href="#variant-photos-{{ $variant->id }}" class="relative block w-10 h-10 flex-shrink-0" title="{{ $variant->images->count() }} photo(s)">
                                    <img src="{{ $variantCover->getUrl() }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-200 hover:opacity-80 transition">
                                    @if($variant->images->count() > 1)
                                        <span class="absolute -bottom-1 -right-1 text-[9px] font-bold bg-gray-900/80 text-white rounded-full w-4 h-4 flex items-center justify-center">
                                            {{ $variant->images->count() }}
                                        </span>
                                    @endif
                                </a>
                                {{-- Lightbox for this variant's photos --}}
                                <div id="variant-photos-{{ $variant->id }}" class="listing-lightbox fixed inset-0 z-50 items-center justify-center p-4">
                                    <a href="#" class="absolute inset-0 bg-gray-900/70" aria-label="Close"></a>
                                    <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[85vh] overflow-y-auto p-5">
                                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                                            <h4 class="text-sm font-bold text-gray-900">{{ $variant->name }} — {{ $variant->images->count() }} photo(s)</h4>
                                            <a href="#" class="text-gray-400 hover:text-gray-600" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            @foreach($variant->images as $img)
                                                <div class="relative">
                                                    <img src="{{ $img->getUrl() }}" alt="" class="w-full h-28 object-cover rounded-lg border {{ $img->pivot->is_primary ? 'border-amber-400 ring-2 ring-amber-300' : 'border-gray-200' }}">
                                                    @if($img->pivot->is_primary)
                                                        <span class="absolute top-1.5 left-1.5 text-[9px] font-bold uppercase bg-amber-500 text-white px-1.5 py-0.5 rounded">Cover</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center text-gray-300">
                                    <i class="fa-solid fa-image text-xs"></i>
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <p class="font-bold text-gray-900 mb-1">{{ $variant->name }}</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($variant->variantAttributes as $varAttr)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700 border border-gray-200">
                                        <span class="text-gray-400">{{ $varAttr->attribute?->name }}:</span>
                                        <strong class="font-semibold">{{ $varAttr->resolvedValue() }}</strong>
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-4 py-3 font-mono text-gray-600">{{ $variant->sku ?? '—' }}</td>

                        <td class="px-4 py-3">
                            <span class="font-bold text-indigo-700">
                                {{ $listing->currency_code }} {{ number_format($variant->price, 2) }}
                            </span>
                            @if($variant->compare_at_price)
                                <span class="block text-[10px] text-gray-400 line-through">{{ $listing->currency_code }} {{ number_format($variant->compare_at_price, 2) }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="font-semibold text-gray-800">{{ $variant->stock_quantity }}</span>
                            <span class="block text-[10px] text-gray-400">{{ str_replace('_', ' ', $variant->stock_status) }}</span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <span @class([
                                'px-2 py-0.5 rounded-full text-[10px] font-bold',
                                'bg-emerald-100 text-emerald-700' => $variant->is_active,
                                'bg-gray-100 text-gray-500' => !$variant->is_active,
                            ])>{{ $variant->is_active ? 'Active' : 'Disabled' }}</span>
                        </td>
                    </tr>

                    {{-- Tier pricing sub-rows for this variant (if any) --}}
                    @if($variantTiers->isNotEmpty())
                        <tr class="bg-indigo-50/30">
                            <td class="px-4 py-0" colspan="1"></td>
                            <td class="px-4 py-2.5" colspan="5">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <i class="fa-solid fa-layer-group text-indigo-400 text-[10px]"></i>
                                    <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider">Tier / Volume Pricing for this Variant</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($variantTiers as $tp)
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white border border-indigo-100 shadow-xs text-xs">
                                            <span class="text-gray-500">{{ (int)$tp->min_quantity }}–{{ $tp->max_quantity ? (int)$tp->max_quantity : '∞' }} units</span>
                                            <span class="font-bold text-indigo-700">{{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}</span>
                                            @php($disc = $variant->price > 0 ? round((1 - $tp->unit_price / $variant->price) * 100) : null)
                                            @if($disc && $disc > 0)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">−{{ $disc }}%</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@endif
