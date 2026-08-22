@if($featuredServices->isNotEmpty())
    <section class="py-12 lg:py-16 bg-white">
        <div class="fe-container">
            <x-frontend::common.section-heading
                eyebrow="Services"
                title="Featured services"
                subtitle="Professional and support services for your institution."
                :action="route('frontend.services.index')"
                actionLabel="Browse services"
            />

            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($featuredServices as $listing)
                    <x-frontend::marketplace.listing-card :listing="$listing" />
                @endforeach
            </div>
        </div>
    </section>
@endif
