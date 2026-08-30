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

<x-backend.page-header :title="$listing->name" :subtitle="'Listing #' . ($listing->sku ?? $listing->id)">
    <x-slot:actions>
        <div class="flex items-center gap-2 flex-wrap">
            <span @class([
                'px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider',
                'bg-amber-100 text-amber-800' => $listing->approval_status === 'pending',
                'bg-emerald-100 text-emerald-800' => $listing->approval_status === 'approved',
                'bg-rose-100 text-rose-800' => $listing->approval_status === 'rejected',
            ])>
                <i class="fa-solid {{ $listing->approval_status === 'approved' ? 'fa-circle-check' : ($listing->approval_status === 'pending' ? 'fa-clock' : 'fa-circle-xmark') }} mr-1"></i>
                {{ ucfirst($listing->approval_status) }}
            </span>

            <span @class([
                'px-2.5 py-1 rounded-full text-xs font-semibold',
                'bg-blue-100 text-blue-800' => $listing->is_active,
                'bg-gray-100 text-gray-600' => !$listing->is_active,
            ])>
                {{ $listing->is_active ? 'Active' : 'Inactive' }}
            </span>

            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 uppercase">
                {{ $listing->listing_type }}
            </span>

            @if($listing->is_featured)
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                    <i class="fa-solid fa-star text-amber-500 mr-1"></i> Featured
                </span>
            @endif
        </div>
    </x-slot:actions>
</x-backend.page-header>

{{-- Top Moderation Action Toolbar --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-xs p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-semibold text-gray-500">Moderation Actions:</span>

        @if($listing->approval_status === 'pending')
            <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}" onsubmit="return confirmSwal(this, 'Approve & Publish Listing?', 'This will approve the listing and publish it immediately to the public marketplace.', 'question', 'Yes, Approve & Publish')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-check"></i> Approve &amp; Publish
                </button>
            </form>
            <button type="button" @click="$dispatch('open-modal-reject')" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-ban"></i> Reject Listing
            </button>
        @elseif($listing->approval_status === 'approved')
            {{-- Undo Approval (Revert back to Pending) --}}
            <form method="POST" action="{{ route('admin.catalog.listings.undo-approve', $listing) }}" onsubmit="return confirmSwal(this, 'Revert Approval to Pending?', 'This will revoke approval and return the listing to Pending Review. It will be hidden from the public marketplace until re-approved.', 'warning', 'Yes, Revert to Pending')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors" title="Revoke approval and return to pending review">
                    <i class="fa-solid fa-rotate-left"></i> Undo Approval
                </button>
            </form>

            @if($listing->is_active)
                <button type="button" @click="$dispatch('open-modal-deactivate')" class="px-4 py-2 rounded-lg border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-pause"></i> Suspend / Deactivate
                </button>
            @else
                <form method="POST" action="{{ route('admin.catalog.listings.reactivate', $listing) }}" onsubmit="return confirmSwal(this, 'Reactivate Listing?', 'This will reactivate the listing on the marketplace.', 'question', 'Yes, Reactivate')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-play"></i> Reactivate Listing
                    </button>
                </form>
            @endif
        @elseif($listing->approval_status === 'rejected')
            <form method="POST" action="{{ route('admin.catalog.listings.approve', $listing) }}" onsubmit="return confirmSwal(this, 'Approve Previously Rejected Listing?', 'This will approve the listing and publish it to the marketplace.', 'question', 'Yes, Approve & Publish')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-check"></i> Re-Approve &amp; Publish
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

{{-- Main Content: 4 tabs (left, 8 cols) + persistent supplier/moderation summary (right, 4 cols) --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ activeTab: 'overview' }">

    <div class="lg:col-span-8">
        {{-- Tab Nav --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-6 overflow-x-auto -mb-px">
                <button type="button" @click="activeTab = 'overview'"
                        class="py-3 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'overview' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'">
                    <i class="fa-solid fa-images text-xs"></i> Overview &amp; Media
                </button>
                <button type="button" @click="activeTab = 'specs'"
                        class="py-3 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'specs' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'">
                    <i class="fa-solid fa-sliders text-xs"></i> Specifications
                </button>
                <button type="button" @click="activeTab = 'pricing'"
                        class="py-3 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'pricing' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'">
                    <i class="fa-solid fa-tags text-xs"></i> Pricing &amp; Inventory
                </button>
                <button type="button" @click="activeTab = 'variants'"
                        class="py-3 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'variants' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'">
                    <i class="fa-solid fa-layer-group text-xs"></i> Variants &amp; Photos
                    @if($listing->variants->isNotEmpty())
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'variants' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'">{{ $listing->variants->count() }}</span>
                    @endif
                </button>
            </nav>
        </div>

        {{-- Each tab's own content scrolls independently, capped to stay
             above the footer instead of growing the page indefinitely. --}}
        <div x-show="activeTab === 'overview'" x-cloak class="space-y-6 lg:max-h-[65vh] lg:overflow-y-auto pr-1">
            @include('backend.admin.catalog.listings.partials.gallery')
            @include('backend.admin.catalog.listings.partials.basic-info')
        </div>

        <div x-show="activeTab === 'specs'" x-cloak class="space-y-6 lg:max-h-[65vh] lg:overflow-y-auto pr-1">
            @include('backend.admin.catalog.listings.partials.specifications')
        </div>

        <div x-show="activeTab === 'pricing'" x-cloak class="space-y-6 lg:max-h-[65vh] lg:overflow-y-auto pr-1">
            @include('backend.admin.catalog.listings.partials.commercial-terms')
            @include('backend.admin.catalog.listings.partials.pricing')
        </div>

        <div x-show="activeTab === 'variants'" x-cloak class="space-y-6 lg:max-h-[65vh] lg:overflow-y-auto pr-1">
            @include('backend.admin.catalog.listings.partials.variants')
        </div>
    </div>

    {{-- Persistent supplier/trust summary — stays visible across every tab --}}
    <div class="lg:col-span-4 space-y-6">
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
