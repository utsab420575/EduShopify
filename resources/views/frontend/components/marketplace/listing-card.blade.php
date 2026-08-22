@props(['listing'])

@php
    $supplierProfile = $listing->supplierAccount?->supplierProfile;
    $isProduct = $listing->listing_type === 'product';
    $productDetail = $isProduct ? $listing->productDetail : null;
    $serviceDetail = ! $isProduct ? $listing->serviceDetail : null;
@endphp

<div class="fe-card fe-card-hover rounded-2xl overflow-hidden flex flex-col h-full">
    <a href="{{ route('frontend.listings.show', $listing->slug) }}" class="block relative aspect-square" style="background:var(--fe-surface-soft);">
        <div class="absolute inset-0 flex items-center justify-center">
            <i class="fa-solid {{ $isProduct ? 'fa-box' : 'fa-briefcase' }} text-3xl" style="color:var(--fe-text-subtle);"></i>
        </div>
        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5">
            <x-frontend::common.badge>{{ $isProduct ? 'Product' : 'Service' }}</x-frontend::common.badge>
            @if($supplierProfile)
                <x-frontend::common.badge variant="verified"><i class="fa-solid fa-circle-check text-[10px]"></i> Verified</x-frontend::common.badge>
            @endif
        </div>
    </a>

    <div class="p-4 flex flex-col flex-1">
        <p class="text-xs mb-1" style="color:var(--fe-text-muted);">
            {{ $listing->mainCategory?->name ?? ($isProduct ? ($listing->brand?->name) : ucfirst($serviceDetail?->service_mode ?? '')) }}
        </p>
        <a href="{{ route('frontend.listings.show', $listing->slug) }}" class="fe-focus-ring text-sm font-semibold fe-line-clamp-2 mb-2" style="color:var(--fe-text);">
            {{ $listing->name }}
        </a>

        <div class="mb-2">
            @if($listing->pricing_type === 'fixed' && $listing->base_price)
                <p class="text-base font-bold" style="color:var(--fe-text);">
                    {{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}
                    @if($listing->unit)<span class="text-xs font-normal" style="color:var(--fe-text-muted);">/{{ $listing->unit->symbol }}</span>@endif
                </p>
            @else
                <p class="text-sm font-semibold" style="color:var(--fe-primary);">Request Quote</p>
            @endif
        </div>

        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs mb-3" style="color:var(--fe-text-muted);">
            @if($isProduct)
                @if($listing->min_order_quantity)
                    <span>MOQ {{ rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.') }}</span>
                @endif
                @if($productDetail?->stock_status)
                    <x-frontend::common.badge :variant="$productDetail->stock_status === 'in_stock' ? 'success' : ($productDetail->stock_status === 'limited' ? 'warning' : 'neutral')">
                        {{ str_replace('_', ' ', ucfirst($productDetail->stock_status)) }}
                    </x-frontend::common.badge>
                @endif
            @else
                @if($serviceDetail?->lead_time_days)
                    <span>Lead time {{ $serviceDetail->lead_time_days }}d</span>
                @endif
            @endif
        </div>

        <div class="mt-auto pt-3 border-t flex items-center justify-between gap-2" style="border-color:var(--fe-border);">
            <div class="min-w-0">
                <p class="text-xs font-medium truncate" style="color:var(--fe-text);">{{ $supplierProfile?->display_name ?? 'Supplier' }}</p>
                <x-frontend::marketplace.rating-summary :rating="$supplierProfile?->rating" :count="$supplierProfile?->reviews_count ?? 0" />
            </div>
            <a href="{{ route('frontend.listings.show', $listing->slug) }}" class="fe-focus-ring shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                View
            </a>
        </div>
    </div>
</div>
