@extends('frontend.layouts.master')

@section('title', $listing->name.' — EduShopify')
@section('meta_description', Str::limit(strip_tags($listing->short_description ?? $listing->description ?? $listing->name), 155))

@section('content')
    @php
        $isProduct = $listing->listing_type === 'product';
        $supplierProfile = $listing->supplierAccount?->supplierProfile;
        $globalTiers = $listing->allTierPrices->whereNull('listing_variant_id');
        $hasVariants = $isProduct && $listing->variants->isNotEmpty();
        $hasAnyTiers = $listing->allTierPrices->isNotEmpty();
    @endphp

    <div class="fe-container py-6 sm:py-8">
        <x-frontend::navigation.breadcrumbs :items="[
            ($isProduct ? 'Products' : 'Services') => route($isProduct ? 'frontend.products.index' : 'frontend.services.index'),
            $listing->name => null,
        ]" />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Media --}}
            <div class="lg:col-span-4">
                @if($listing->getMedia('gallery')->isNotEmpty())
                    <div class="space-y-3" x-data="{ activeImg: '{{ $listing->getMedia('gallery')->first()->getUrl() }}' }">
                        <div class="aspect-square rounded-2xl border overflow-hidden bg-white shadow-xs flex items-center justify-center" style="border-color:var(--fe-border);">
                            <img :src="activeImg" alt="{{ $listing->name }}" class="w-full h-full object-contain p-2">
                        </div>
                        @if($listing->getMedia('gallery')->count() > 1)
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($listing->getMedia('gallery') as $media)
                                    <button type="button" @click="activeImg = '{{ $media->getUrl() }}'" class="aspect-square rounded-xl border overflow-hidden p-1 bg-white hover:opacity-80 transition-opacity" :class="activeImg === '{{ $media->getUrl() }}' ? 'ring-2 ring-indigo-500 border-transparent' : ''" style="border-color:var(--fe-border);">
                                        <img src="{{ $media->getUrl() }}" alt="{{ $listing->name }}" class="w-full h-full object-contain">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="aspect-square rounded-2xl border flex items-center justify-center" style="border-color:var(--fe-border);background:var(--fe-surface-soft);">
                        <i class="fa-solid {{ $isProduct ? 'fa-box' : 'fa-briefcase' }} text-5xl" style="color:var(--fe-text-subtle);"></i>
                    </div>
                @endif
            </div>

            {{-- Commercial details --}}
            <div class="lg:col-span-5">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <x-frontend::common.badge>{{ $isProduct ? 'Product' : 'Service' }}</x-frontend::common.badge>
                    @if($supplierProfile)
                        <x-frontend::common.badge variant="verified"><i class="fa-solid fa-circle-check text-[10px]"></i> Verified Supplier</x-frontend::common.badge>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-[26px] font-bold tracking-tight" style="font-family:var(--font-display);color:var(--fe-text);">{{ $listing->name }}</h1>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm" style="color:var(--fe-text-muted);">
                    @if($listing->brand)<span>Brand: <strong style="color:var(--fe-text);">{{ $listing->brand->name }}</strong></span>@endif
                    @if($listing->sku)<span>SKU: {{ $listing->sku }}</span>@endif
                    @if($listing->mainCategory)<span>{{ $listing->mainCategory->name }}</span>@endif
                </div>

                @if($listing->short_description)
                    <p class="mt-4 text-sm leading-relaxed" style="color:var(--fe-text-muted);">{{ $listing->short_description }}</p>
                @endif

                @if($hasVariants)
                    <div class="mt-6">
                        <x-frontend::marketplace.variant-selector :variants="$listing->variants" />
                    </div>
                @endif

                @if($hasAnyTiers)
                    <div class="mt-6">
                        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Volume Discount / Tier Pricing</p>
                        @if($globalTiers->isNotEmpty())
                            <x-frontend::marketplace.tier-pricing-table :tiers="$globalTiers" :currency="$listing->currency_code" />
                        @elseif($listing->allTierPrices->isNotEmpty())
                            <x-frontend::marketplace.tier-pricing-table :tiers="$listing->allTierPrices" :currency="$listing->currency_code" />
                        @endif
                    </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-6">
                    @if($isProduct)
                        @if($listing->productDetail?->stock_status)
                            <div class="fe-card rounded-xl p-3 text-center">
                                <p class="text-xs" style="color:var(--fe-text-muted);">Stock</p>
                                <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ str_replace('_', ' ', ucfirst($listing->productDetail->stock_status)) }}</p>
                            </div>
                        @endif
                        @if($listing->productDetail?->lead_time_days)
                            <div class="fe-card rounded-xl p-3 text-center">
                                <p class="text-xs" style="color:var(--fe-text-muted);">Lead Time</p>
                                <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ $listing->productDetail->lead_time_days }} days</p>
                            </div>
                        @endif
                        @if($listing->min_order_quantity)
                            <div class="fe-card rounded-xl p-3 text-center">
                                <p class="text-xs" style="color:var(--fe-text-muted);">MOQ</p>
                                <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ rtrim(rtrim(number_format($listing->min_order_quantity, 2), '0'), '.') }} {{ $listing->unit?->symbol }}</p>
                            </div>
                        @endif
                    @else
                        @if($listing->serviceDetail?->service_mode)
                            <div class="fe-card rounded-xl p-3 text-center">
                                <p class="text-xs" style="color:var(--fe-text-muted);">Mode</p>
                                <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ ucfirst($listing->serviceDetail->service_mode) }}</p>
                            </div>
                        @endif
                        @if($listing->serviceDetail?->duration_value)
                            <div class="fe-card rounded-xl p-3 text-center">
                                <p class="text-xs" style="color:var(--fe-text-muted);">Duration</p>
                                <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ rtrim(rtrim(number_format($listing->serviceDetail->duration_value, 2), '0'), '.') }} {{ $listing->serviceDetail->duration_unit }}</p>
                            </div>
                        @endif
                        @if($listing->serviceDetail?->lead_time_days)
                            <div class="fe-card rounded-xl p-3 text-center">
                                <p class="text-xs" style="color:var(--fe-text-muted);">Lead Time</p>
                                <p class="text-sm font-semibold mt-0.5" style="color:var(--fe-text);">{{ $listing->serviceDetail->lead_time_days }} days</p>
                            </div>
                        @endif
                    @endif
                </div>

                <div x-data="{ tab: 'description' }" class="mt-8">
                    <div class="flex items-center gap-1 border-b overflow-x-auto" style="border-color:var(--fe-border);">
                        @foreach(($isProduct ? ['description' => 'Description', 'specifications' => 'Specifications', 'coverage' => 'Shipping & Coverage'] : ['description' => 'Description', 'specifications' => 'Service Details', 'coverage' => 'Coverage / Terms']) as $key => $label)
                            <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'font-semibold' : ''" :style="tab === '{{ $key }}' ? 'color:var(--fe-primary);border-color:var(--fe-primary)' : 'color:var(--fe-text-muted)'" class="px-4 py-2.5 text-sm border-b-2 whitespace-nowrap" style="border-color:transparent;">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="py-5">
                        <div x-show="tab === 'description'" class="text-sm leading-relaxed" style="color:var(--fe-text-muted);">
                            {!! nl2br(e($listing->description ?? 'No description provided.')) !!}
                        </div>
                        <div x-show="tab === 'specifications'" x-cloak>
                            @if(isset($groupedSpecifications) && $groupedSpecifications->isNotEmpty())
                                <div class="space-y-6">
                                    @foreach($groupedSpecifications as $group)
                                        <div>
                                            <h4 class="text-xs font-bold uppercase tracking-wider mb-3 pb-1 border-b" style="color:var(--fe-text);border-color:var(--fe-border);">{{ $group['group_name'] }}</h4>
                                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
                                                @foreach($group['items'] as $item)
                                                    <div class="flex justify-between items-center py-1.5 border-b" style="border-color:var(--fe-border);">
                                                        <dt style="color:var(--fe-text-muted);" class="flex items-center gap-1.5">
                                                            <span>{{ $item->attribute?->name }}</span>
                                                            @if($item->attribute?->unit)
                                                                <span class="text-xs text-gray-400 font-normal">({{ $item->attribute->unit->symbol ?? $item->attribute->unit->name }})</span>
                                                            @endif
                                                        </dt>
                                                        <dd class="font-medium flex items-center gap-1.5" style="color:var(--fe-text);">
                                                            @if($item->attribute?->input_type === 'color' && $item->attributeValue?->color_hex)
                                                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 inline-block shadow-xs" style="background-color: {{ $item->attributeValue->color_hex }}"></span>
                                                            @endif
                                                            <span>{{ $item->formattedValue() }}</span>
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($listing->attributeValues->isNotEmpty())
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                    @foreach($listing->attributeValues as $value)
                                        <div class="flex justify-between border-b pb-2" style="border-color:var(--fe-border);">
                                            <dt style="color:var(--fe-text-muted);">{{ $value->attribute?->name }}</dt>
                                            <dd class="font-medium" style="color:var(--fe-text);">{{ $value->formattedValue() }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @else
                                <p class="text-sm" style="color:var(--fe-text-muted);">No additional specifications listed.</p>
                            @endif
                        </div>
                        <div x-show="tab === 'coverage'" x-cloak class="text-sm leading-relaxed" style="color:var(--fe-text-muted);">
                            @if($isProduct && $listing->productDetail?->warranty_terms)
                                <p class="mb-2"><strong style="color:var(--fe-text);">Warranty:</strong> {{ $listing->productDetail->warranty_terms }}</p>
                            @endif
                            @if($isProduct && $listing->productDetail?->support_terms)
                                <p class="mb-2"><strong style="color:var(--fe-text);">Support:</strong> {{ $listing->productDetail->support_terms }}</p>
                            @endif
                            @if(!$isProduct && $listing->serviceDetail?->service_terms)
                                <p class="mb-2"><strong style="color:var(--fe-text);">Service Terms:</strong> {{ $listing->serviceDetail->service_terms }}</p>
                            @endif
                            @if(!$isProduct && $listing->serviceDetail?->support_terms)
                                <p class="mb-2"><strong style="color:var(--fe-text);">Support:</strong> {{ $listing->serviceDetail->support_terms }}</p>
                            @endif
                            @if((!$isProduct && !$listing->serviceDetail?->service_terms) || ($isProduct && !$listing->productDetail?->warranty_terms))
                                <p>No additional terms provided by the supplier.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quote / Supplier panel --}}
            <div class="lg:col-span-3">
                <div class="lg:sticky lg:top-24 space-y-4">
                    <div class="fe-card rounded-2xl p-5">
                        @if($listing->pricing_type === 'fixed' && $listing->base_price)
                            <p class="text-2xl font-bold" style="color:var(--fe-text);">{{ $listing->currency_code }} {{ number_format($listing->base_price, 2) }}</p>
                            @if($listing->unit)<p class="text-xs" style="color:var(--fe-text-muted);">per {{ $listing->unit->symbol }}</p>@endif
                        @else
                            <p class="text-lg font-semibold" style="color:var(--fe-primary);">Request Quote</p>
                            <p class="text-xs mt-1" style="color:var(--fe-text-muted);">Pricing available on request.</p>
                        @endif

                        <div class="mt-4 space-y-2">
                            @auth
                                <a href="{{ route('buyer.rfqs.create', ['listing_id' => $listing->id]) }}" class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold">Request Quotation</a>
                            @else
                                <a href="{{ route('frontend.handoff.request-quote-listing', $listing->slug) }}" class="fe-btn-primary fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold">Request Quotation</a>
                            @endauth

                            <button type="button" @click="$dispatch('open-inquiry-listing')" class="fe-focus-ring block w-full text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                                Contact Supplier
                            </button>

                            <a href="{{ auth()->check() ? route('buyer.saved-items.toggle') : route('frontend.handoff.save-listing', $listing->slug) }}"
                               @if(auth()->check())
                               onclick="event.preventDefault(); document.getElementById('fe-save-listing-form').submit();"
                               @endif
                               class="fe-focus-ring block w-full text-center px-4 py-2.5 rounded-lg text-sm font-medium" style="color:var(--fe-text-muted);">
                                <i class="fa-regular fa-bookmark mr-1.5"></i> Save Listing
                            </a>
                            @auth
                                <form id="fe-save-listing-form" method="POST" action="{{ route('buyer.saved-items.toggle') }}" class="hidden">
                                    @csrf
                                    <input type="hidden" name="type" value="listing">
                                    <input type="hidden" name="id" value="{{ $listing->id }}">
                                </form>
                            @endauth
                        </div>
                    </div>

                    @if($supplierProfile)
                        <div class="fe-card rounded-2xl p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="w-12 h-12 rounded-xl flex items-center justify-center text-base font-bold shrink-0" style="background:var(--fe-primary-soft);color:var(--fe-primary);font-family:var(--font-display);">
                                    {{ strtoupper(substr($supplierProfile->display_name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold fe-line-clamp-2" style="color:var(--fe-text);">{{ $supplierProfile->display_name }}</p>
                                    <x-frontend::marketplace.rating-summary :rating="$supplierProfile->rating" :count="$supplierProfile->reviews_count ?? 0" />
                                </div>
                            </div>
                            @if($supplierProfile->city || $supplierProfile->country)
                                <p class="text-xs mb-3" style="color:var(--fe-text-muted);"><i class="fa-solid fa-location-dot text-[10px] mr-1"></i>{{ collect([$supplierProfile->city?->name, $supplierProfile->country?->name])->filter()->implode(', ') }}</p>
                            @endif
                            <a href="{{ route('frontend.suppliers.show', $supplierProfile->slug) }}" class="fe-focus-ring block text-center px-4 py-2.5 rounded-lg text-sm font-semibold border" style="border-color:var(--fe-border-strong);color:var(--fe-text);">
                                View Full Storefront
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($related->isNotEmpty())
            <div class="mt-14">
                <x-frontend::common.section-heading title="Related listings" />
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($related as $item)
                        <x-frontend::marketplace.listing-card :listing="$item" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($supplierProfile)
        <x-frontend::marketplace.inquiry-modal
            trigger-id="listing"
            :action="route('frontend.inquiries.listing', $listing->slug)"
            :context="$supplierProfile->display_name"
        />
    @endif

    @auth
        @if(request('save_intent'))
            @push('scripts')
                <script>document.getElementById('fe-save-listing-form')?.submit();</script>
            @endpush
        @endif
    @endauth
@endsection
