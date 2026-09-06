@props(['listing', 'animationDelay' => null])

@php
    $productUrl      = route('v2.products.show', $listing->slug);
    $img             = $listing->primaryImage?->getUrl()
                       ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
                       ?? $listing->getFirstMediaUrl('gallery')
                       ?: null;
    $supplierAccount = $listing->supplierAccount;
    $supplierProfile = $supplierAccount?->supplierProfile;
    $brand           = $listing->brand?->name
                       ?? $supplierAccount?->display_name
                       ?? '—';
    $catName         = $listing->mainCategory?->name ?? 'Other';
    $price           = $listing->base_price;
    $currency        = $listing->currency_code ?? 'USD';
    $unit            = $listing->unit?->abbreviation ?? $listing->unit?->symbol ?? '';
@endphp

<a
    href="{{ $productUrl }}"
    class="group block bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-md hover:border-emerald-200 transition-all duration-200 product-card-fade-up relative flex flex-col h-full"
    @if($animationDelay !== null) style="animation-delay: {{ $animationDelay }}ms" @endif
>
    {{-- Image Container --}}
    <div class="relative h-48 bg-gray-100 overflow-hidden shrink-0">
        @if($img)
            <img
                src="{{ $img }}"
                alt="{{ $listing->name }}"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                <svg class="w-12 h-12 mb-1" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9l4.5-4.5 4.5 4.5 4.5-4.5 4.5 4.5"/>
                    <circle cx="8.5" cy="7.5" r="1.5" fill="currentColor"/>
                </svg>
                <span class="text-xs">No image</span>
            </div>
        @endif

        {{-- Top Badges Overlay --}}
        <div class="absolute top-2.5 left-2.5 flex flex-wrap items-center gap-1.5 z-10 pointer-events-none">
            @if($supplierProfile)
                <span class="badge-verified text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-xs inline-flex items-center gap-1">
                    <i class="fa-solid fa-circle-check text-[10px] text-emerald-600"></i> Verified
                </span>
            @endif

            @if($listing->is_featured)
                <span class="bg-amber-400 text-amber-950 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs inline-flex items-center gap-1 uppercase tracking-wide">
                    <i class="fa-solid fa-star text-[9px]"></i> Featured
                </span>
            @endif
        </div>

        {{-- Floating Save Button (top-right) --}}
        <div class="absolute top-2.5 right-2.5 z-10">
            @include('frontend_new.components.listing-save-btn', ['listing' => $listing])
        </div>

        {{-- Floating Compare Button (bottom-right) --}}
        <div class="absolute bottom-2.5 right-2.5 z-10">
            <button
                type="button"
                onclick="event.preventDefault(); event.stopPropagation(); fnToggleCompare({{ (int) $listing->id }});"
                data-compare-id="{{ (int) $listing->id }}"
                data-style="icon"
                title="Add to compare"
                aria-label="Add to compare"
                class="fn-compare-btn w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 ease-in-out shrink-0 cursor-pointer shadow-sm bg-white/95 text-slate-500 border border-slate-200/90 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 hover:scale-110 hover:shadow-md backdrop-blur-xs"
            >
                <i class="fa-solid fa-arrow-right-arrow-left text-xs transition-transform duration-200"></i>
            </button>
        </div>
    </div>

    {{-- Card Body --}}
    <div class="p-3.5 flex flex-col flex-1">
        <p class="text-[10px] font-semibold text-emerald-600 uppercase tracking-widest mb-1 truncate">{{ $catName }}</p>
        <h3 class="text-sm font-semibold text-gray-900 leading-snug mb-0.5 line-clamp-2 group-hover:text-emerald-700 transition-colors">{{ $listing->name }}</h3>
        <p class="text-xs text-gray-500 mb-3 truncate">{{ $brand }}</p>

        @if($listing->product_reviews_count > 0)
            <div class="flex items-center gap-1 mb-2">
                @for($i = 1; $i <= 5; $i++)
                    <span class="{{ $i <= round($listing->product_rating) ? 'star-filled' : 'star-empty' }} text-xs">★</span>
                @endfor
                <span class="text-xs text-gray-400">({{ $listing->product_reviews_count }})</span>
            </div>
        @endif

        <div class="mt-auto pt-2 flex items-center justify-between border-t border-gray-100">
            <div>
                @if($price)
                    <p class="text-sm font-bold text-emerald-600">
                        {{ $currency }} {{ number_format((float) $price, 0) }}
                        @if($unit) <span class="text-xs font-normal text-gray-400">/ {{ $unit }}</span> @endif
                    </p>
                @else
                    <p class="text-xs text-gray-400 italic">Price on request</p>
                @endif
            </div>
            <span class="text-xs text-emerald-600 font-semibold group-hover:underline flex items-center gap-0.5">
                View &rarr;
            </span>
        </div>
    </div>
</a>
