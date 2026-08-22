@if($featuredProducts->isNotEmpty())
    <section class="py-12 lg:py-16" style="background:var(--fe-canvas);">
        <div class="fe-container">
            <x-frontend::common.section-heading
                eyebrow="Products"
                title="Featured products"
                subtitle="In-demand supplies and equipment from verified suppliers."
                :action="route('frontend.products.index')"
                actionLabel="Browse products"
            />

            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($featuredProducts as $listing)
                    <x-frontend::marketplace.listing-card :listing="$listing" />
                @endforeach
            </div>
        </div>
    </section>
@endif
