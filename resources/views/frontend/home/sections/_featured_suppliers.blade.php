@if($featuredSuppliers->isNotEmpty())
    <section class="py-12 lg:py-16" style="background:var(--fe-canvas);">
        <div class="fe-container">
            <x-frontend::common.section-heading
                eyebrow="Suppliers"
                title="Featured verified suppliers"
                subtitle="Institutions trust these suppliers for reliable sourcing."
                :action="route('frontend.suppliers.index')"
                actionLabel="Browse suppliers"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($featuredSuppliers as $supplier)
                    <x-frontend::marketplace.supplier-card :supplier="$supplier" />
                @endforeach
            </div>
        </div>
    </section>
@endif
