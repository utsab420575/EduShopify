@if($featuredSuppliers->isNotEmpty())
    <section class="py-12 lg:py-16 bg-white">
        <div class="fe-container">
            <x-frontend::common.section-heading
                eyebrow="Suppliers"
                title="Featured Suppliers"
                subtitle="Institutions trust these suppliers for reliable sourcing."
                :action="route('frontend.suppliers.index')"
                actionLabel="See all"
            />

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($featuredSuppliers as $supplier)
                    @php
                        $productCount = \App\Services\Catalog\PublicListingQuery::forSupplierAccount($supplier->account_id)->count();
                    @endphp
                    <div class="relative rounded-lg overflow-hidden border border-gray-200 hover:shadow-md transition-shadow group"
                         x-data="{
                            isSaved: false,
                            loading: false,
                            async saveSupplier() {
                                @guest
                                    window.location.href = '{{ route('frontend.handoff.save-supplier', $supplier->slug) }}';
                                    return;
                                @endguest
                                if (this.loading) return;
                                this.loading = true;
                                try {
                                    const res = await fetch('{{ route('buyer.saved-items.toggle') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                                        },
                                        body: JSON.stringify({ type: 'supplier', id: {{ $supplier->account_id }}, action: 'save' })
                                    });
                                    const data = await res.json();
                                    if (res.ok) {
                                        this.isSaved = true;
                                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Supplier is saved', type: 'success', actionUrl: '{{ route('buyer.saved-items.index', ['type' => 'supplier']) }}', actionLabel: 'saved suppliers' } }));
                                    } else {
                                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Could not save supplier.', type: 'danger' } }));
                                    }
                                } catch (e) {
                                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'An error occurred while saving.', type: 'danger' } }));
                                } finally {
                                    this.loading = false;
                                }
                            }
                         }">
                        @if($supplier->banner)
                            <img src="{{ Illuminate\Support\Facades\Storage::url($supplier->banner) }}" alt="{{ $supplier->display_name }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" />
                        @else
                            <div class="w-full h-48 flex items-center justify-center bg-emerald-50 text-emerald-600 text-3xl font-bold group-hover:scale-105 transition-transform duration-300 font-display">
                                {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
                            </div>
                        @endif

                        <div class="absolute bottom-3 left-3">
                            <span class="badge-verified text-[10px] font-semibold px-1.5 py-0.5 rounded inline-flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Verified</span>
                        </div>

                        <button type="button" @click="saveSupplier()" :disabled="loading"
                                class="absolute top-3 right-3 w-7 h-7 bg-white/90 rounded flex items-center justify-center hover:bg-white shadow-sm"
                                title="Save supplier to dashboard">
                            <i :class="isSaved ? 'fa-solid fa-heart text-emerald-600' : 'fa-regular fa-heart text-gray-500'" class="text-xs"></i>
                        </button>

                        <div class="p-3">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-7 h-7 rounded bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ route('frontend.suppliers.show', $supplier->slug) }}" class="text-sm font-semibold text-gray-900 fe-line-clamp-2 hover:text-emerald-600">{{ $supplier->display_name }}</a>
                                    <p class="text-xs text-gray-500">{{ $supplier->account?->supplierTypes?->first()?->name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span><span class="star">★</span> {{ number_format((float) $supplier->rating, 1) }} ({{ $supplier->reviews_count ?? 0 }})</span>
                                <span>{{ $supplier->country?->name }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-1">
                                <span class="text-gray-400">{{ $productCount }}+ Products</span>
                                <a href="{{ route('frontend.suppliers.show', $supplier->slug) }}" class="text-emerald-600 font-medium hover:underline">View Profile →</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
