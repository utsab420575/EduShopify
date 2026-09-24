{{--
    Read-only accordion — one entry per buyer RFQ item, mirroring the visual
    language of the Step 1 "Buyer Requested" panel (_rfq-item-panel.blade.php)
    and offer cards (_offer-card.blade.php) from the edit form, but static
    (no Alpine form state — this is the supplier's own already-submitted/
    draft quotation, not editable here).

    Expects `$rfqItem` (RfqItem, from $quotation->rfq->items) and
    `$productResponses` (the quotation's own quotation_items scoped to this
    RFQ item — zero, one, or several Product Responses) from the parent loop
    in show.blade.php.
--}}
@php
    $isRequirement = $rfqItem->isRequirement();
    $isMarketplace = $rfqItem->isMarketplaceProduct();
    $typeLabel = $isRequirement ? 'Requirement (Quotation Only)' : ($isMarketplace ? 'Marketplace Product' : 'Custom Product');
    $typeClass = $isRequirement ? 'bg-amber-100 text-amber-900 border-amber-300' : ($isMarketplace ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-300');
    $offerCount = $productResponses->sum(fn ($pr) => $pr->offers->count());
    $itemTotal = $productResponses->sum(fn ($pr) => (float) ($pr->offers->firstWhere('is_selected', true)?->total_price
        ?? $pr->offers->firstWhere('is_primary', true)?->total_price
        ?? $pr->line_total));
@endphp
<div x-data="{ open: {{ $productResponses->isNotEmpty() ? 'true' : 'false' }} }" class="border border-gray-200 rounded-xl overflow-hidden mb-3 last:mb-0">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-3 px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
        <div class="flex items-center gap-2 flex-wrap min-w-0">
            <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform shrink-0" :class="open ? 'rotate-180' : ''"></i>
            <span class="text-sm font-bold text-gray-900 truncate">{{ $rfqItem->item_name }}</span>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $typeClass }}">{{ $typeLabel }}</span>
            @if($productResponses->isEmpty())
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">Not Quoted</span>
            @endif
        </div>
        <div class="text-xs text-gray-500 shrink-0 text-right">
            @if($productResponses->isNotEmpty())
                <span>{{ $productResponses->count() }} product{{ $productResponses->count() > 1 ? 's' : '' }} &middot; {{ $offerCount }} offer{{ $offerCount > 1 ? 's' : '' }}</span>
                <span class="block font-bold text-indigo-700">{{ $quotation->currency_code }} {{ number_format($itemTotal, 2) }}</span>
            @endif
        </div>
    </button>

    <div x-show="open" x-cloak class="p-4 space-y-4">

        {{-- Buyer Requested (read-only, mirrors _rfq-item-panel.blade.php) --}}
        <div class="bg-slate-50/90 border border-slate-200 rounded-xl p-3.5 shadow-2xs">
            <div class="flex items-center justify-between gap-3 mb-2.5 pb-2.5 border-b border-slate-200/80">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-200 text-slate-700">
                        <i class="fa-solid fa-file-contract text-slate-500 text-[10px]"></i> Buyer Requested
                    </span>
                    <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full border {{ $typeClass }}">
                        {{ $typeLabel }}
                    </span>
                </div>
                <div class="inline-flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-md border border-slate-200 shadow-2xs">
                    <span class="text-[11px] text-slate-400 font-medium">Quantity:</span>
                    <span class="text-xs font-bold text-slate-900">{{ rtrim(rtrim((string) $rfqItem->quantity, '0'), '.') }} {{ $rfqItem->unit?->symbol ?? $rfqItem->unit?->name ?? $rfqItem->custom_unit ?? 'units' }}</span>
                </div>
            </div>

            @php
                $listingImage = $rfqItem->listing?->primaryImage?->getUrl()
                    ?? ($rfqItem->listing?->relationLoaded('media') && $rfqItem->listing?->media->isNotEmpty() ? $rfqItem->listing?->media->first()?->getUrl() : null)
                    ?? $rfqItem->listing?->getFirstMediaUrl('gallery');
            @endphp
            @if($listingImage)
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 shrink-0 bg-white shadow-2xs">
                        <img src="{{ $listingImage }}" alt="{{ $rfqItem->item_name }}" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Requirement / Item Name</p>
                        <h4 class="text-sm sm:text-base font-bold text-gray-900 leading-snug">{{ $rfqItem->item_name }}</h4>
                    </div>
                </div>
            @endif

            <div class="mb-3 p-3 bg-white rounded-lg border border-slate-200/80 space-y-2.5">
                @if($rfqItem->category)
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Category</span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-800 border border-indigo-200/80 shadow-2xs">
                            <i class="fa-solid fa-folder-tree text-indigo-500 text-[10px]"></i> {{ $rfqItem->category->name }}
                        </span>
                    </div>
                @endif

                @if($rfqItem->estimated_unit_price)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 {{ $rfqItem->category ? 'pt-2 border-t border-slate-100' : '' }}">
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Target / Est. Unit Price</span>
                            <span class="text-xs font-semibold text-emerald-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-tag text-emerald-600 text-[11px] shrink-0"></i>
                                <span>{{ $quotation->currency_code }} {{ number_format($rfqItem->estimated_unit_price, 2) }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">/ {{ $rfqItem->unit?->symbol ?? $rfqItem->unit?->name ?? $rfqItem->custom_unit ?? 'unit' }}</span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Estimated Total Budget</span>
                            <span class="text-xs font-semibold text-indigo-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-coins text-amber-500 text-[11px] shrink-0"></i>
                                <span>{{ $quotation->currency_code }} {{ number_format($rfqItem->estimated_unit_price * (float)$rfqItem->quantity, 2) }}</span>
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            @if($rfqItem->description)
                <div class="mb-3">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                        <i class="fa-solid fa-align-left text-slate-400 mr-1"></i> Description / Specifications:
                    </span>
                    <p class="text-xs text-slate-700 leading-relaxed bg-white p-2.5 rounded-lg border border-slate-200/80 whitespace-pre-line">{{ $rfqItem->description }}</p>
                </div>
            @endif

            @php
                $rfqAttrs = $rfqItem->resolvedAttributes();
                $rfqGroupedSpecs = $rfqItem->groupedSpecifications();
            @endphp
            @if($rfqAttrs->isNotEmpty())
                <div class="mt-2.5 pt-2.5 border-t border-slate-200/60" x-data="{ expanded: false }">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1">
                            <i class="fa-solid fa-list-check text-indigo-500 text-[11px]"></i>
                            <span>{{ $isMarketplace ? 'Product Specifications:' : 'Category Specifications:' }}</span>
                            <span class="text-gray-400 font-normal">({{ $rfqAttrs->count() }})</span>
                        </p>
                        @if($rfqAttrs->count() > 6)
                            <button type="button"
                                    @click="expanded = !expanded"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                <span x-text="expanded ? 'Show Less' : 'Show All ({{ $rfqAttrs->count() }})'">Show All ({{ $rfqAttrs->count() }})</span>
                            </button>
                        @endif
                    </div>

                    {{-- Compact view: top 6 pills --}}
                    <div x-show="!expanded" class="flex flex-wrap gap-1.5">
                        @foreach($rfqAttrs->take(6) as $attr)
                            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-md bg-white border border-gray-200 text-gray-700 shadow-2xs">
                                <span class="text-gray-500 font-medium">{{ $attr['name'] }}:</span>
                                <span class="font-semibold text-gray-900">{{ $attr['value'] }}</span>
                            </span>
                        @endforeach
                    </div>

                    {{-- Expanded view: Group-wise detailed specification cards (NO accordion) --}}
                    <div x-show="expanded" x-cloak class="space-y-3 mt-2">
                        @foreach($rfqGroupedSpecs as $group)
                            <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
                                <div class="bg-slate-50/80 px-4 py-2 border-b border-gray-200/80">
                                    <h5 class="text-xs font-bold text-gray-800">{{ $group['group_name'] }}</h5>
                                </div>
                                <div class="divide-y divide-gray-100 text-xs">
                                    @foreach($group['attributes'] as $attr)
                                        <div class="px-4 py-2 flex items-center justify-between gap-4 hover:bg-slate-50/30 transition">
                                            <span class="text-gray-600 font-normal">{{ $attr['name'] }}</span>
                                            <span class="text-gray-900 font-semibold text-right">{{ $attr['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                $customSpecs = collect(is_array($rfqItem->specs) ? $rfqItem->specs : [])
                    ->filter(fn ($s) => is_array($s) && !empty($s['name']) && !str_starts_with($s['name'], '__') && $s['name'] !== 'is_requirement');
            @endphp
            @if($customSpecs->isNotEmpty())
                <div class="mt-2.5 pt-2.5 border-t border-slate-200/60">
                    <p class="text-[10px] font-bold text-indigo-900 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-sliders text-indigo-500 text-[11px]"></i>
                        Buyer's Custom Specifications:
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($customSpecs as $spec)
                            <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-md bg-indigo-50/90 border border-indigo-200 text-indigo-950 font-medium shadow-2xs">
                                <span class="text-indigo-600 font-semibold">{{ $spec['name'] }}:</span>
                                <span class="font-bold text-gray-900">{{ $spec['value'] ?? '—' }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($rfqItem->media->isNotEmpty())
                <div class="mt-2.5 pt-2.5 border-t border-slate-200/60">
                    <p class="text-[10px] font-bold text-amber-900 uppercase tracking-wide mb-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-paperclip text-amber-600 text-[11px]"></i>
                        Buyer's Reference Files &amp; Drawings:
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($rfqItem->media as $file)
                            <a href="{{ $file->getUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-md bg-white border border-gray-200 text-gray-700 hover:border-amber-400 hover:text-amber-950 shadow-2xs transition-colors">
                                <i class="fa-solid {{ str_starts_with($file->mime_type ?? '', 'image/') ? 'fa-file-image text-emerald-500' : 'fa-file-pdf text-red-500' }} text-xs"></i>
                                <span class="font-medium">{{ $file->file_name }}</span>
                                <i class="fa-solid fa-download text-[9px] text-gray-400 ml-0.5"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Product Responses + their Offers --}}
        @forelse($productResponses as $pr)
            <div>
                @if($productResponses->count() > 1)
                    <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1.5">{{ $pr->item_name }}</p>
                @endif
                <div class="space-y-2">
                    @foreach($pr->offers as $offer)
                        @php
                            $isSelected = (bool) $offer->is_selected;
                            $isPrimary = (bool) $offer->is_primary;
                        @endphp
                        <div class="border rounded-lg p-3 {{ $isSelected ? 'border-emerald-300 bg-emerald-50/40' : ($isPrimary ? 'border-indigo-200 bg-white' : 'border-gray-200 bg-white') }}">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                    <span class="text-sm font-semibold text-gray-900 truncate">{{ $offer->product_name }}</span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                        {{ ['marketplace' => 'Marketplace Product', 'custom' => 'Custom Offer', 'copy_spec' => 'Copied Spec', 'document' => 'Document Quotation'][$offer->offer_method] ?? $offer->offer_method }}
                                    </span>
                                    @if($isPrimary)
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200"><i class="fa-solid fa-star text-[9px]"></i> Primary</span>
                                    @endif
                                    @if($isSelected)
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-check text-[9px]"></i> Buyer Selected</span>
                                    @endif
                                </div>
                                <div class="text-right shrink-0 text-xs">
                                    <p class="text-gray-500">{{ rtrim(rtrim((string) $offer->quantity, '0'), '.') }} {{ $offer->unit?->symbol ?? $offer->unit?->name ?? $offer->custom_unit }} &times; {{ $quotation->currency_code }} {{ number_format($offer->unit_price, 2) }}</p>
                                    <p class="font-bold text-indigo-700">{{ $quotation->currency_code }} {{ number_format($offer->total_price, 2) }}</p>
                                </div>
                            </div>

                            @if($offer->marketplaceProduct)
                                <p class="text-[11px] text-emerald-700 mt-1.5"><i class="fa-solid fa-circle-check text-[10px] mr-1"></i>From your listing: <span class="font-semibold">{{ $offer->marketplaceProduct->name }}</span></p>
                            @endif

                            @if($offer->description)
                                <p class="text-xs text-gray-500 mt-1.5">{{ $offer->description }}</p>
                            @endif

                            @if($offer->delivery_time)
                                <p class="text-[11px] text-gray-500 mt-1"><i class="fa-solid fa-truck-fast text-[10px] mr-1"></i>{{ $offer->delivery_time }} days delivery</p>
                            @endif

                            {{-- Structured category/listing attributes for THIS offer
                                 (quotation_item_attribute_values, keyed per-offer),
                                 grouped by attribute group — same data shape and
                                 partial the listing preview pages use, so this
                                 renders identically to "Listing Specifications"
                                 elsewhere in the app. --}}
                            @php($offerSpecGroups = $offer->groupedSpecifications())
                            @if($offerSpecGroups->isNotEmpty())
                                <div class="mt-2.5 pt-2.5 border-t border-gray-100">
                                    @include('backend.supplier.catalog.listings.partials.preview-specifications', ['groupedSpecifications' => $offerSpecGroups])
                                </div>
                            @endif

                            @if(is_array($offer->specifications) && count($offer->specifications) > 0)
                                <div class="flex flex-wrap gap-1.5 mt-2 pt-2 border-t border-gray-100">
                                    @foreach($offer->specifications as $spec)
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-md bg-gray-50 border border-gray-200 text-gray-700">
                                            <span class="text-gray-400">{{ $spec['name'] ?? '' }}:</span>
                                            <span class="font-semibold">{{ $spec['value'] ?? '' }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if($offer->getMedia('document')->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($offer->getMedia('document') as $doc)
                                        <a href="{{ $doc->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-md bg-white border border-gray-200 text-purple-700 hover:underline">
                                            <i class="fa-solid fa-download text-[9px]"></i> {{ $doc->file_name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-xs text-gray-400 italic">This RFQ item hasn't been quoted yet.</p>
        @endforelse
    </div>
</div>
