@props(['supplier'])

@php
    $location = collect([$supplier->city?->name, $supplier->country?->name])->filter()->implode(', ');
    $primaryType = $supplier->account?->supplierTypes?->first();
@endphp

<div class="fe-card fe-card-hover rounded-2xl p-5 flex flex-col h-full">
    <div class="flex items-start gap-3 mb-3">
        <span class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0 text-lg font-bold" style="background:var(--fe-primary-soft);color:var(--fe-primary);font-family:var(--font-display);">
            {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
        </span>
        <div class="min-w-0">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('frontend.suppliers.show', $supplier->slug) }}" class="fe-focus-ring text-sm font-semibold fe-line-clamp-2" style="color:var(--fe-text);">{{ $supplier->display_name }}</a>
            </div>
            <x-frontend::common.badge variant="verified" class="mt-1"><i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier</x-frontend::common.badge>
        </div>
    </div>

    @if($location)
        <p class="text-xs mb-1 flex items-center gap-1.5" style="color:var(--fe-text-muted);">
            <i class="fa-solid fa-location-dot text-[10px]"></i> {{ $location }}
        </p>
    @endif

    @if($primaryType)
        <p class="text-xs mb-3" style="color:var(--fe-text-muted);">{{ $primaryType->name }}</p>
    @endif

    @if($supplier->description)
        <p class="text-sm fe-line-clamp-2 mb-3" style="color:var(--fe-text-muted);">{{ $supplier->description }}</p>
    @endif

    <div class="mt-auto pt-3 border-t flex items-center justify-between gap-2" style="border-color:var(--fe-border);">
        <x-frontend::marketplace.rating-summary :rating="$supplier->rating" :count="$supplier->reviews_count ?? 0" />
        <a href="{{ route('frontend.suppliers.show', $supplier->slug) }}" class="fe-focus-ring shrink-0 text-xs font-semibold px-3 py-1.5 rounded-lg border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
            View Supplier
        </a>
    </div>
</div>
