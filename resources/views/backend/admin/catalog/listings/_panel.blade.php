{{--
    Full listing review content, shared by the standalone show.blade.php page
    and the Approval Center's "Review" modal (fetched as an HTML fragment via
    ListingController::panel and injected by <x-backend.ajax-modal>). Expects
    $listing (with variants.images, variants.tierPrices, allTierPrices,
    attributeValues... eager loaded via loadForReview()) and
    $groupedSpecifications.

    Split into several @include'd sub-partials rather than one large file —
    the supplier-side equivalent (listings/partials/listing-preview.blade.php)
    hit a repeatable Blade compiler bug once a single template accumulated
    enough nested directives (silently truncated compiled output, no compile
    error, only a runtime ParseError) — splitting stayed well clear of it.
--}}

{{-- Shared CSS for every :target lightbox panel in gallery.blade.php and
     variants.blade.php — defined once here since both are always included
     together on this page. --}}
<style>
    .listing-lightbox { display: none; }
    .listing-lightbox:target { display: flex; }
</style>

{{-- ════════════════════════════════════════════════
     PAGE HEADER — name + status badges
════════════════════════════════════════════════ --}}
<x-backend.page-header :title="$listing->name" :subtitle="'Listing #' . ($listing->sku ?? $listing->listing_number)">
    <x-slot:actions>
        <div class="flex items-center gap-2 flex-wrap">
            <span @class([
                'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold',
                'bg-amber-100 text-amber-800 border border-amber-200' => $listing->approval_status === 'pending',
                'bg-emerald-100 text-emerald-800 border border-emerald-200' => $listing->approval_status === 'approved',
                'bg-rose-100 text-rose-800 border border-rose-200' => $listing->approval_status === 'rejected',
                'bg-gray-100 text-gray-600 border border-gray-200' => !in_array($listing->approval_status, ['pending','approved','rejected']),
            ])>
                <i class="fa-solid fa-circle text-[6px]"></i>
                {{ ucfirst($listing->approval_status) }}
            </span>
            <span @class([
                'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border',
                'bg-blue-50 text-blue-700 border-blue-200' => $listing->is_active,
                'bg-gray-50 text-gray-500 border-gray-200' => !$listing->is_active,
            ])>
                {{ $listing->is_active ? 'Active' : 'Inactive' }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 uppercase">
                {{ $listing->listing_type }}
            </span>
            @if($listing->is_featured)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <i class="fa-solid fa-star text-amber-500 text-[10px]"></i> Featured
                </span>
            @endif
        </div>
    </x-slot:actions>
</x-backend.page-header>

{{-- ════════════════════════════════════════════════
     MODERATION ACTION PANEL (context-colored)
════════════════════════════════════════════════ --}}
@if($listing->approval_status === 'pending')
    <div class="mb-5 rounded-xl border-l-4 border-amber-400 bg-amber-50 border border-amber-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-amber-200/60 flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-clock text-amber-600 text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-900">Awaiting Your Decision</p>
                <p class="text-xs text-amber-700">This listing has been submitted by the supplier and is pending your review and moderation.</p>
            </div>
        </div>
        <div class="px-5 py-3.5 flex flex-wrap items-center gap-3">
            <div class="flex items-start gap-2">
                <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}" onsubmit="return confirmSwal(this, 'Approve & Publish Listing?', 'This will approve the listing and publish it immediately to the public marketplace.', 'question', 'Yes, Approve & Publish')">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm flex items-center gap-2 transition-colors">
                        <i class="fa-solid fa-check-circle text-emerald-200"></i>
                        Approve & Publish
                    </button>
                </form>
            </div>
            <div class="flex items-start gap-2">
                <button type="button" @click="$dispatch('open-modal-reject')"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-ban text-rose-200"></i>
                    Reject Listing
                </button>
            </div>
            <div class="flex items-center gap-2 ml-auto">
                <form method="POST" action="{{ route('admin.catalog.listings.feature', $listing) }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-lg border border-amber-300 bg-white text-amber-700 hover:bg-amber-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-star {{ $listing->is_featured ? 'text-amber-500' : 'text-gray-400' }}"></i>
                        {{ $listing->is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                    </button>
                </form>
                <a href="{{ route('frontend.listings.show', $listing) }}" target="_blank"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i> View on Marketplace
                </a>
                <a href="{{ route('admin.approvals.show', 'listings') }}"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-list-check text-gray-400"></i> Approval Queue
                </a>
            </div>
        </div>
    </div>

@elseif($listing->approval_status === 'approved')
    <div class="mb-5 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 border border-emerald-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-emerald-200/60 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-emerald-900">Approved & Published</p>
                    <p class="text-xs text-emerald-700">This listing is live on the marketplace.{{ $listing->approved_at ? ' Approved ' . $listing->approved_at->diffForHumans() . '.' : '' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form method="POST" action="{{ route('admin.catalog.listings.undo-approve', $listing) }}" onsubmit="return confirmSwal(this, 'Revert Approval to Pending?', 'This will revoke approval and return the listing to Pending Review.', 'warning', 'Yes, Revert to Pending')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i class="fa-solid fa-rotate-left"></i> Undo Approval
                    </button>
                </form>
                @if($listing->is_active)
                    <button type="button" @click="$dispatch('open-modal-deactivate')"
                            class="px-3.5 py-2 rounded-lg border border-rose-300 bg-white text-rose-600 hover:bg-rose-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-pause"></i> Suspend
                    </button>
                @else
                    <form method="POST" action="{{ route('admin.catalog.listings.reactivate', $listing) }}" onsubmit="return confirmSwal(this, 'Reactivate Listing?', 'This will reactivate the listing.', 'question', 'Yes, Reactivate')">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 rounded-lg border border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-play"></i> Reactivate
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.catalog.listings.feature', $listing) }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-star {{ $listing->is_featured ? 'text-amber-500' : 'text-gray-400' }}"></i>
                        {{ $listing->is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                    </button>
                </form>
                <a href="{{ route('frontend.listings.show', $listing) }}" target="_blank"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i> Marketplace
                </a>
                <a href="{{ route('admin.approvals.show', 'listings') }}"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-list-check text-gray-400"></i> Queue
                </a>
            </div>
        </div>
    </div>

@elseif($listing->approval_status === 'rejected')
    <div class="mb-5 rounded-xl border-l-4 border-rose-500 bg-rose-50 border border-rose-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-rose-200/60 flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-rose-900">Listing Was Rejected</p>
                @if($listing->rejection_reason)
                    <p class="text-xs text-rose-700 mt-0.5"><span class="font-semibold">Reason given to supplier:</span> {{ $listing->rejection_reason }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}" onsubmit="return confirmSwal(this, 'Approve Previously Rejected Listing?', 'This will approve and publish the listing to the marketplace.', 'question', 'Yes, Approve & Publish')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-check"></i> Re-Approve & Publish
                    </button>
                </form>
                <a href="{{ route('admin.approvals.show', 'listings') }}"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-list-check text-gray-400"></i> Queue
                </a>
            </div>
        </div>
    </div>

@else
    {{-- Fallback moderation bar for any other status --}}
    <div class="mb-5 bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold text-gray-500">Moderation Actions:</span>
            <form method="POST" action="{{ route('admin.catalog.listings.feature', $listing) }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-star {{ $listing->is_featured ? 'text-amber-500' : 'text-gray-400' }}"></i>
                    {{ $listing->is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                </button>
            </form>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('frontend.listings.show', $listing) }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i> View on Marketplace
            </a>
            <a href="{{ route('admin.approvals.show', 'listings') }}" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-list-check text-gray-400"></i> Approval Queue
            </a>
        </div>
    </div>
@endif

{{-- ════════════════════════════════════════════════
     MAIN CONTENT — 4 tabs (8 cols) + sidebar (4 cols)
════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ activeTab: 'overview' }">

    <div class="lg:col-span-8 space-y-0">
        {{-- Tab navigation bar —— wrapped in card to visually group with content --}}
        <div class="bg-white rounded-t-xl border border-b-0 border-gray-200 px-1">
            <nav class="flex gap-0 overflow-x-auto">
                <button type="button" @click="activeTab = 'overview'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'overview' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-images text-xs"></i> Overview & Media
                </button>
                <button type="button" @click="activeTab = 'specs'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'specs' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-sliders text-xs"></i> Specifications
                </button>
                <button type="button" @click="activeTab = 'pricing'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'pricing' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-tags text-xs"></i> Pricing & Variants
                    @if($listing->variants->isNotEmpty())
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'pricing' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'">{{ $listing->variants->count() }}</span>
                    @endif
                </button>
            </nav>
        </div>

        {{-- Tab content panels — rounded bottom only, flat top joins the tab nav --}}
        <div class="bg-white rounded-b-xl border border-gray-200">
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="space-y-5 p-5 lg:max-h-[62vh] lg:overflow-y-auto">
                @include('backend.admin.catalog.listings.partials.gallery')
                @include('backend.admin.catalog.listings.partials.basic-info')
            </div>

            <div x-show="activeTab === 'specs'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="space-y-5 p-5 lg:max-h-[62vh] lg:overflow-y-auto">
                @include('backend.admin.catalog.listings.partials.specifications')
            </div>

            <div x-show="activeTab === 'pricing'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="space-y-5 p-5 lg:max-h-[62vh] lg:overflow-y-auto">
                {{-- Commercial terms: base price, MOQ, logistics --}}
                @include('backend.admin.catalog.listings.partials.commercial-terms')

                {{-- Global (non-variant) tier pricing --}}
                @php($globalTiers = $listing->allTierPrices->whereNull('listing_variant_id')->values())
                @if($globalTiers->isNotEmpty())
                    <div class="rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2 bg-gray-50">
                            <i class="fa-solid fa-layer-group text-indigo-400 text-xs"></i>
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Global Tier / Volume Pricing</h3>
                            <span class="text-[10px] text-gray-400 ml-auto">Applies to the base product (all variants unless overridden)</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="px-5 py-2.5 font-semibold">Quantity Range</th>
                                        <th class="px-4 py-2.5 font-semibold text-right">Unit Price</th>
                                        <th class="px-4 py-2.5 font-semibold text-right">Discount vs. Base</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($globalTiers as $tp)
                                        @php($disc = $listing->base_price > 0 ? round((1 - $tp->unit_price / $listing->base_price) * 100) : null)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-5 py-2.5 font-medium text-gray-800">
                                                {{ (int)$tp->min_quantity }} &ndash; {{ $tp->max_quantity ? (int)$tp->max_quantity : '∞' }} units
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-bold text-indigo-700">
                                                {{ $tp->currency_code }} {{ number_format($tp->unit_price, 2) }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right">
                                                @if($disc && $disc > 0)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">−{{ $disc }}%</span>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Variants with their own inline tier pricing --}}
                @if($listing->variants->isNotEmpty())
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-cubes text-indigo-400 text-sm"></i>
                            <h3 class="text-sm font-bold text-gray-800">Product Variants ({{ $listing->variants->count() }})</h3>
                        </div>
                        @include('backend.admin.catalog.listings.partials.variants')
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Persistent supplier/trust summary — stays visible across every tab --}}
    <div class="lg:col-span-4 space-y-4">
        @include('backend.admin.catalog.listings.partials.sidebar')
    </div>

</div>

{{-- Reject Modal --}}
@if($listing->approval_status === 'pending')
    <x-backend.modal id="reject" title="Reject Listing">
        <form method="POST" action="{{ route('admin.catalog.listings.reject', $listing) }}">
            @csrf
            <div class="space-y-3">
                <p class="text-xs text-gray-500">
                    Please provide a clear reason for rejecting this listing. The supplier will see this reason and can make necessary revisions.
                </p>
                <x-backend.textarea name="reason" label="Rejection Note / Feedback to Supplier" placeholder="e.g. Incomplete specifications, invalid brand claim, low resolution images..." required />
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="open = false" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">Confirm Rejection</button>
            </div>
        </form>
    </x-backend.modal>
@elseif($listing->is_active)
    <x-backend.modal id="deactivate" title="Suspend / Deactivate Listing">
        <form method="POST" action="{{ route('admin.catalog.listings.deactivate', $listing) }}">
            @csrf
            <div class="space-y-3">
                <p class="text-xs text-gray-500">
                    Enter the reason for taking down this active listing from the marketplace.
                </p>
                <x-backend.textarea name="reason" label="Suspension Reason" placeholder="e.g. Policy violation, out of stock dispute..." required />
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="open = false" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">Suspend Listing</button>
            </div>
        </form>
    </x-backend.modal>
@endif
