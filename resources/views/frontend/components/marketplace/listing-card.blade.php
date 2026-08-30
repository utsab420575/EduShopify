@props(['listing'])

@php
    $supplierProfile = $listing->supplierAccount?->supplierProfile;
    $isProduct       = $listing->listing_type === 'product';
    $productDetail   = $isProduct ? $listing->productDetail : null;
    $serviceDetail   = ! $isProduct ? $listing->serviceDetail : null;

    // Resolve thumbnail: prefer the explicitly-set primary image media, else
    // fall back to the first item from the already-loaded media collection,
    // else call getFirstMediaUrl() as a last resort (avoids N+1 when the
    // collection is already eager-loaded via ->with('media')).
    $thumbUrl = null;
    if ($listing->primaryImage) {
        $thumbUrl = $listing->primaryImage->getUrl();
    } elseif ($listing->relationLoaded('media') && $listing->media->isNotEmpty()) {
        $first    = $listing->media->where('collection_name', 'gallery')->first()
                    ?? $listing->media->first();
        $thumbUrl = $first?->getUrl();
    } else {
        $thumbUrl = $listing->getFirstMediaUrl('gallery') ?: null;
    }
@endphp

<div class="fe-card fe-card-hover rounded-2xl overflow-hidden flex flex-col h-full group">
    {{-- Product Image --}}
    <a href="{{ route('frontend.listings.show', $listing->slug) }}"
       class="block relative overflow-hidden"
       style="background:var(--fe-surface-soft);">

        @if($thumbUrl)
            <div class="relative aspect-[4/3] overflow-hidden">
                <img src="{{ $thumbUrl }}"
                     alt="{{ $listing->name }}"
                     loading="lazy"
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
            </div>
        @else
            <div class="aspect-[4/3] flex items-center justify-center" style="background:var(--fe-surface-soft);">
                <i class="fa-solid {{ $isProduct ? 'fa-box' : 'fa-briefcase' }} text-4xl" style="color:var(--fe-text-subtle);"></i>
            </div>
        @endif

        {{-- Overlay badges --}}
        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5 pointer-events-none">
            <x-frontend::common.badge>{{ $isProduct ? 'Product' : 'Service' }}</x-frontend::common.badge>
            @if($supplierProfile)
                <x-frontend::common.badge variant="verified">
                    <i class="fa-solid fa-circle-check text-[10px]"></i> Verified
                </x-frontend::common.badge>
            @endif
        </div>

        @if($listing->is_featured)
            <div class="absolute top-2.5 right-2.5 pointer-events-none">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-amber-900">
                    <i class="fa-solid fa-star text-[9px]"></i> Featured
                </span>
            </div>
        @endif

        <div class="absolute bottom-2.5 right-2.5" style="box-shadow:0 1px 4px rgba(15,23,42,.15);border-radius:9999px;">
            <x-frontend::marketplace.compare-button :listing="$listing" />
        </div>
    </a>

    {{-- Card Body --}}
    <div class="p-4 flex flex-col flex-1">
        <p class="text-xs mb-1 truncate" style="color:var(--fe-text-muted);">
            {{ $listing->mainCategory?->name ?? ($isProduct ? ($listing->brand?->name) : ucfirst($serviceDetail?->service_mode ?? '')) }}
        </p>

        <a href="{{ route('frontend.listings.show', $listing->slug) }}"
           class="fe-focus-ring text-sm font-semibold fe-line-clamp-2 mb-2 hover:underline leading-snug"
           style="color:var(--fe-text);">
            {{ $listing->name }}
        </a>

        {{-- Price --}}
        <div class="mb-2">
            @if($listing->pricing_type === 'fixed' && $listing->base_price)
                <div class="flex items-baseline gap-1.5 flex-wrap">
                    <span class="text-base font-bold" style="color:var(--fe-text);">
                        {{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}
                    </span>
                    @if($listing->unit)
                        <span class="text-xs font-normal" style="color:var(--fe-text-muted);">/ {{ $listing->unit->symbol }}</span>
                    @endif
                    @if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price)
                        <span class="text-xs line-through" style="color:var(--fe-text-subtle);">
                            {{ $listing->currency_code }} {{ number_format($listing->compare_at_price, 2) }}
                        </span>
                    @endif
                </div>
            @else
                <p class="text-sm font-semibold" style="color:var(--fe-primary);">Request Quote</p>
            @endif
        </div>

        {{-- Product/Service Meta --}}
        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs mb-3" style="color:var(--fe-text-muted);">
            @if($isProduct)
                @if($listing->min_order_quantity)
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-boxes-stacking text-[10px]"></i>
                        MOQ {{ rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.') }}
                        {{ $listing->unit?->symbol }}
                    </span>
                @endif
                @if($productDetail?->stock_status)
                    <x-frontend::common.badge :variant="$productDetail->stock_status === 'in_stock' ? 'success' : ($productDetail->stock_status === 'limited' ? 'warning' : 'neutral')">
                        {{ str_replace('_', ' ', ucfirst($productDetail->stock_status)) }}
                    </x-frontend::common.badge>
                @endif
            @else
                @if($serviceDetail?->lead_time_days)
                    <span><i class="fa-regular fa-clock text-[10px] mr-0.5"></i> {{ $serviceDetail->lead_time_days }}d lead</span>
                @endif
                @if($serviceDetail?->service_mode)
                    <span>{{ ucfirst($serviceDetail->service_mode) }}</span>
                @endif
            @endif
        </div>

        {{-- Supplier footer --}}
        <div class="mt-auto pt-3 border-t flex items-center justify-between gap-2" style="border-color:var(--fe-border);">
            <div class="min-w-0">
                <p class="text-xs font-medium truncate" style="color:var(--fe-text);">
                    {{ $supplierProfile?->display_name ?? 'Supplier' }}
                </p>
                <x-frontend::marketplace.rating-summary
                    :rating="$supplierProfile?->rating"
                    :count="$supplierProfile?->reviews_count ?? 0" />
            </div>
            <a href="{{ route('frontend.listings.show', $listing->slug) }}"
               class="fe-focus-ring shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80"
               style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                View
            </a>
        </div>
    </div>
</div>
