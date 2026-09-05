@if($allSuppliers->isNotEmpty())
    <section class="py-12 lg:py-16 bg-gray-50">
        <div class="fe-container">
            <x-frontend::common.section-heading
                title="All Suppliers"
                :action="route('frontend.suppliers.index')"
                actionLabel="View all"
            />

            <div x-data="{ active: 'all' }" class="flex flex-wrap gap-2 mb-6">
                <button type="button" @click="active = 'all'"
                        class="tab-btn text-xs font-semibold px-3 py-1.5 rounded-md"
                        :class="active === 'all' ? 'tag-active' : 'tag-inactive'">
                    All Categories
                </button>
                @foreach($tabs as $tab)
                    <button type="button" @click="active = '{{ $tab->slug }}'"
                            class="tab-btn text-xs font-semibold px-3 py-1.5 rounded-md"
                            :class="active === '{{ $tab->slug }}' ? 'tag-active' : 'tag-inactive'">
                        {{ $tab->name }}
                    </button>
                @endforeach

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 w-full mt-4">
                    @foreach($allSuppliers as $supplier)
                        <div class="supplier-item" x-show="active === 'all' || active === '{{ $supplier->home_category?->slug }}'">
                            <x-frontend::marketplace.supplier-card :supplier="$supplier" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
