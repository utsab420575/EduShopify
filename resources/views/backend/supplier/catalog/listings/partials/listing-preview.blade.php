{{--
    Read-only listing detail content, shared by the full show.blade.php page
    and the Step 4 "Preview Listing" modal (fetched as an HTML fragment via
    ListingController::previewFragment and injected with x-html). Expects
    $listing (with variants.images, variants.tierPrices, allTierPrices,
    attributeValues... eager loaded) and $groupedSpecifications.

    Split into several @include'd sub-partials rather than one large file:
    a single template this size hit a Blade compiler bug (repeatable —
    directives past a certain cumulative point in one compileString() call
    were silently dropped, producing a truncated, unparseable compiled
    view) that only went away once compiled in smaller pieces.
--}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

    <div class="xl:col-span-8 space-y-6">
        @include('backend.supplier.catalog.listings.partials.preview-overview')
        @include('backend.supplier.catalog.listings.partials.preview-variants')
        @include('backend.supplier.catalog.listings.partials.preview-pricing')
    </div>

    <div class="xl:col-span-4 space-y-6">
        @include('backend.supplier.catalog.listings.partials.preview-sidebar')
    </div>

</div>
