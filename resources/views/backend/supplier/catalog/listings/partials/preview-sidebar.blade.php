{{--
    RIGHT SIDEBAR — three cards:
      1. Buy-Box: Status badge + Price + Pricing type
      2. Quick Info: Category, Brand, Unit, SKU, Min Order, Listing Number
      3. Listing Timeline: Created → Submitted → Approved → Published
    Photos & Media have been moved to the left column hero panel in listing-preview-tabbed.blade.php.
--}}
@php
    $hasTiers   = $listing->allTierPrices?->whereNull('listing_variant_id')->isNotEmpty();
    $hasVariants = $listing->isProduct() && $listing->variants->isNotEmpty();
    $timelineSteps = [
        [
            'icon'  => 'fa-circle-plus',
            'label' => 'Listing Created',
            'sub'   => $listing->created_at?->format('d M Y, h:i A'),
            'done'  => true,
            'alert' => null,
        ],
        [
            'icon'  => 'fa-paper-plane',
            'label' => 'Submitted for Approval',
            'sub'   => $listing->setup_completed_at?->format('d M Y, h:i A'),
            'done'  => !in_array($listing->approval_status, ['draft']),
            'alert' => null,
        ],
        [
            'icon'  => 'fa-circle-check',
            'label' => 'Approved by Platform',
            'sub'   => $listing->approved_at?->format('d M Y, h:i A'),
            'done'  => $listing->approval_status === 'approved',
            'alert' => $listing->approval_status === 'rejected' ? 'Rejected' : null,
        ],
        [
            'icon'  => 'fa-globe',
            'label' => 'Published & Live',
            'sub'   => $listing->published_at?->format('d M Y, h:i A'),
            'done'  => (bool) $listing->published_at,
            'alert' => null,
        ],
    ];
    $timelineTotal = count($timelineSteps);
@endphp

{{-- ═══════════════════════════════════════════════════════
     CARD 1 — Buy Box (Status + Price)
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    {{-- Card header with status --}}
    <div class="px-5 pt-4 pb-3 border-b border-gray-100 flex items-center justify-between">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Listing Status</span>
        <x-backend.status-badge :status="$listing->approval_status" />
    </div>

    <div class="px-5 py-4">
        {{-- Primary price --}}
        @if($listing->base_price)
            <div class="mb-1">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-gray-900 tracking-tight">
                        {{ number_format($listing->base_price, 2) }}
                    </span>
                    <span class="text-sm font-semibold text-gray-500">{{ $listing->currency_code }}</span>
                </div>
                @if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price)
                    <span class="text-xs text-gray-400 line-through">{{ $listing->currency_code }} {{ number_format($listing->compare_at_price, 2) }}</span>
                @endif
            </div>
            <div class="flex items-center gap-1.5 mt-1">
                <i class="fa-solid fa-tag text-indigo-400 text-[10px]"></i>
                <span class="text-xs text-gray-500">
                    {{ $listing->pricingType?->name ?? ucfirst(str_replace('_', ' ', $listing->pricing_type ?? 'Fixed Price')) }}
                </span>
            </div>
        @else
            <div class="mb-1">
                <span class="text-xl font-extrabold text-gray-700">Request a Quote</span>
            </div>
            <div class="flex items-center gap-1.5 mt-1">
                <i class="fa-solid fa-envelope text-indigo-400 text-[10px]"></i>
                <span class="text-xs text-gray-500">Price provided on request (RFQ)</span>
            </div>
        @endif

        {{-- Tier pricing indicator --}}
        @if($hasTiers)
            <div class="mt-3 flex items-center gap-1.5 py-2 px-3 rounded-lg bg-indigo-50 border border-indigo-100">
                <i class="fa-solid fa-layer-group text-indigo-500 text-[10px]"></i>
                <span class="text-xs text-indigo-700 font-medium">Volume pricing tiers available — see Overview tab</span>
            </div>
        @endif

        {{-- Variants indicator --}}
        @if($hasVariants)
            <div class="mt-2 flex items-center gap-1.5 py-2 px-3 rounded-lg bg-purple-50 border border-purple-100">
                <i class="fa-solid fa-cubes text-purple-500 text-[10px]"></i>
                <span class="text-xs text-purple-700 font-medium">{{ $listing->variants->count() }} variant{{ $listing->variants->count() !== 1 ? 's' : '' }} available — see Variants tab</span>
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     CARD 2 — Quick Info
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Quick Info</h3>
    </div>
    <dl class="divide-y divide-gray-50 text-xs">
        {{-- Type --}}
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500">
                <i class="fa-solid fa-tag w-3.5 text-gray-400 text-center"></i> Type
            </dt>
            <dd>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $listing->isProduct() ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                    {{ $listing->listingType?->name ?? ucfirst($listing->listing_type ?? 'Product') }}
                </span>
            </dd>
        </div>

        {{-- Category --}}
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500">
                <i class="fa-solid fa-folder w-3.5 text-gray-400 text-center"></i> Category
            </dt>
            <dd class="font-semibold text-gray-800 text-right max-w-[55%] truncate">
                {{ $listing->mainCategory?->name ?? '—' }}
            </dd>
        </div>

        {{-- Brand --}}
        @if($listing->brand)
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-regular fa-registered w-3.5 text-gray-400 text-center"></i> Brand
                </dt>
                <dd class="font-semibold text-gray-800">{{ $listing->brand->name }}</dd>
            </div>
        @endif

        {{-- Unit --}}
        @if($listing->unit)
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-solid fa-ruler w-3.5 text-gray-400 text-center"></i> Unit
                </dt>
                <dd class="font-semibold text-gray-800">{{ $listing->unit->symbol ?? $listing->unit->name }}</dd>
            </div>
        @endif

        {{-- Min Order Quantity --}}
        @if($listing->min_order_quantity)
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-solid fa-boxes-stacked w-3.5 text-gray-400 text-center"></i> Min. Order
                </dt>
                <dd class="font-semibold text-gray-800">{{ number_format($listing->min_order_quantity, 0) }} units</dd>
            </div>
        @endif

        {{-- SKU --}}
        @if($listing->sku)
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-solid fa-barcode w-3.5 text-gray-400 text-center"></i> SKU
                </dt>
                <dd class="font-mono font-semibold text-gray-800">{{ $listing->sku }}</dd>
            </div>
        @endif

        {{-- Listing Number --}}
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500">
                <i class="fa-solid fa-hashtag w-3.5 text-gray-400 text-center"></i> Listing ID
            </dt>
            <dd class="font-mono text-gray-600">{{ $listing->listing_number }}</dd>
        </div>
    </dl>
</div>

{{-- ═══════════════════════════════════════════════════════
     CARD 3 — Listing Timeline
═══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Listing Timeline</h3>
    </div>
    <div class="px-5 py-4">
        <ol class="relative space-y-0">
            @foreach($timelineSteps as $stepIdx => $tStep)
                <li class="flex gap-3 {{ $stepIdx < $timelineTotal - 1 ? 'pb-4' : '' }} relative">
                    {{-- Connector line --}}
                    @if($stepIdx < $timelineTotal - 1)
                        <div class="absolute left-[13px] top-7 bottom-0 w-0.5 {{ $tStep['done'] ? 'bg-indigo-200' : 'bg-gray-200' }}"></div>
                    @endif

                    {{-- Icon dot --}}
                    <div class="flex-shrink-0 z-10 w-7 h-7 rounded-full flex items-center justify-center mt-0.5
                        {{ $tStep['alert'] ? 'bg-red-100' : ($tStep['done'] ? 'bg-indigo-100' : 'bg-gray-100') }}">
                        <i class="fa-solid {{ $tStep['icon'] }} text-[11px]
                            {{ $tStep['alert'] ? 'text-red-500' : ($tStep['done'] ? 'text-indigo-600' : 'text-gray-400') }}"></i>
                    </div>

                    {{-- Text --}}
                    <div class="flex-1 min-w-0 pt-1">
                        <p class="text-xs font-semibold
                            {{ $tStep['alert'] ? 'text-red-600' : ($tStep['done'] ? 'text-gray-900' : 'text-gray-400') }}">
                            {{ $tStep['alert'] ?? $tStep['label'] }}
                        </p>
                        @if($tStep['sub'])
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $tStep['sub'] }}</p>
                        @elseif(!$tStep['done'])
                            <p class="text-[11px] text-gray-300 mt-0.5">Pending</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
