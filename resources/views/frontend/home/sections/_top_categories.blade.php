@if($topCategories->isNotEmpty())
    <section class="py-12 lg:py-16 bg-white">
        <div class="fe-container">
            <x-frontend::common.section-heading
                eyebrow="Browse"
                title="Shop by category"
                subtitle="Explore the categories institutions source most."
                :action="route('frontend.categories.index')"
                actionLabel="View all categories"
            />

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($topCategories as $category)
                    <x-frontend::marketplace.category-card :category="$category" />
                @endforeach
            </div>
        </div>
    </section>
@endif
