{{-- Product Variants card, with each variant's own photo. See listing-preview.blade.php for the expected variables. --}}
@if($listing->isProduct())
    <x-backend.form-card title="Product Variants" description="{{ $listing->variants->isNotEmpty() ? $listing->variants->count() . ' variant(s). To add, edit, or remove variants, use Edit Listing above.' : '' }}">
        @if($listing->variants->isEmpty())
            <p class="text-xs text-gray-400">
                No variants configured. If this item has multiple options (sizes, colors, models), add them from
                <a href="{{ route('supplier.catalog.listings.edit', $listing) }}" class="text-indigo-600 font-semibold hover:underline">Edit Listing</a>.
            </p>
        @else
            {{-- A CSS-only lightbox (no JS): works whether this partial is loaded
                 as a normal page or injected into the Step 4 preview modal via
                 x-html, where injected <script> tags never execute but <style>
                 tags still apply. Each thumbnail links to #variant-photos-{id};
                 the matching panel below shows itself via :target. --}}
            <style>
                .variant-photos-modal { display: none; }
                .variant-photos-modal:target { display: flex; }
            </style>

            <div class="overflow-x-auto -mx-5 -mb-5">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50/80 border-y border-gray-100 text-gray-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-5 py-3 font-semibold w-16">Photo</th>
                            <th class="px-3 py-3 font-semibold">Variant Details</th>
                            <th class="px-3 py-3 font-semibold">SKU</th>
                            <th class="px-3 py-3 font-semibold">Price</th>
                            <th class="px-3 py-3 font-semibold">Stock</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($listing->variants as $variant)
                            @php($variantCover = $variant->images->firstWhere('pivot.is_primary', true) ?? $variant->images->first())
                            <tr>
                                <td class="px-5 py-3">
                                    @if($variantCover)
                                        <a href="#variant-photos-{{ $variant->id }}" class="relative block w-10 h-10" title="View all {{ $variant->images->count() }} photo(s)">
                                            <img src="{{ $variantCover->getUrl() }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-200 hover:opacity-80 transition">
                                            @if($variant->images->count() > 1)
                                                <span class="absolute -bottom-1 -right-1 text-[9px] font-bold bg-gray-900/80 text-white rounded-full w-4 h-4 flex items-center justify-center">
                                                    {{ $variant->images->count() }}
                                                </span>
                                            @endif
                                        </a>

                                        {{-- Lightbox panel for this variant's full photo set --}}
                                        <div id="variant-photos-{{ $variant->id }}" class="variant-photos-modal fixed inset-0 z-50 items-center justify-center p-4">
                                            <a href="#" class="absolute inset-0 bg-gray-900/70" aria-label="Close"></a>
                                            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[85vh] overflow-y-auto p-5">
                                                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                                                    <h4 class="text-sm font-bold text-gray-900">{{ $variant->name }} — {{ $variant->images->count() }} photo(s)</h4>
                                                    <a href="#" class="text-gray-400 hover:text-gray-600 text-sm" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
                                                </div>
                                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                                    @foreach($variant->images as $img)
                                                        <div class="relative">
                                                            <img src="{{ $img->getUrl() }}" alt="" class="w-full h-28 object-cover rounded-lg border {{ $img->pivot->is_primary ? 'border-amber-400 ring-2 ring-amber-300' : 'border-gray-200' }}">
                                                            @if($img->pivot->is_primary)
                                                                <span class="absolute top-1.5 left-1.5 text-[9px] font-bold uppercase tracking-wide bg-amber-500 text-white px-1.5 py-0.5 rounded">Cover</span>
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
                                <td class="px-3 py-3">
                                    <p class="font-bold text-gray-900">{{ $variant->name }}</p>
                                    @if($variant->variantAttributes->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($variant->variantAttributes as $va)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">
                                                    <span class="text-gray-400 mr-1">{{ $va->attribute?->name }}:</span>
                                                    <strong>{{ $va->resolvedValue() }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 font-mono text-gray-600">{{ $variant->sku ?: '—' }}</td>
                                <td class="px-3 py-3 font-bold text-indigo-700 text-sm">{{ $variant->currency_code }} {{ number_format($variant->price, 2) }}</td>
                                <td class="px-3 py-3 font-semibold text-gray-800">{{ (int) $variant->stock_quantity }}</td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ ($variant->stock_status ?? 'in_stock') === 'in_stock' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ str_replace('_', ' ', $variant->stock_status ?? 'in_stock') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-backend.form-card>
@endif
