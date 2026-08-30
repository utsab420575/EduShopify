{{-- Pricing, MOQ, and fulfillment terms — Tab 3 (Pricing & Inventory), shown with the tier-pricing table. See listings/_panel.blade.php for expected variables. --}}
<x-backend.form-card title="Commercial Terms">
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Pricing Type</dt>
            <dd class="font-bold text-gray-900">{{ $listing->pricingType?->name ?? ucfirst($listing->pricing_type ?? 'Fixed') }}</dd>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Base Price</dt>
            <dd class="font-bold text-indigo-700 text-sm">
                {{ $listing->base_price ? $listing->currency_code . ' ' . number_format($listing->base_price, 2) : 'Negotiable / Quote' }}
            </dd>
        </div>
        @if($listing->compare_at_price)
            <div class="p-3 bg-gray-50 rounded-lg">
                <dt class="text-gray-500 font-medium mb-0.5">Compare-At Price</dt>
                <dd class="font-bold text-gray-500 line-through">{{ $listing->currency_code }} {{ number_format($listing->compare_at_price, 2) }}</dd>
            </div>
        @endif
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Minimum Order Quantity (MOQ)</dt>
            <dd class="font-bold text-gray-900">{{ $listing->min_order_quantity ? (int)$listing->min_order_quantity . ' ' . ($listing->unit?->symbol ?? 'units') : '1 unit' }}</dd>
        </div>
    </dl>

    {{-- Product Detail Specifics --}}
    @if($listing->productDetail)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Product Logistics & Policies</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Product Type</dt>
                    <dd class="font-semibold text-gray-800">{{ ucfirst($listing->productDetail->product_type ?? 'Simple') }}</dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Stock Status</dt>
                    <dd class="font-semibold text-gray-800">{{ str_replace('_', ' ', ucfirst($listing->productDetail->stock_status ?? 'In Stock')) }}</dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Lead Time</dt>
                    <dd class="font-semibold text-gray-800">{{ $listing->productDetail->lead_time_days ? $listing->productDetail->lead_time_days . ' days' : 'Immediate' }}</dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Country of Origin</dt>
                    <dd class="font-semibold text-gray-800">{{ $listing->productDetail->originCountry?->name ?? '—' }}</dd>
                </div>
                @if($listing->productDetail->warranty_period_months)
                    <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                        <dt class="text-gray-400 font-medium text-[11px]">Warranty</dt>
                        <dd class="font-semibold text-gray-800">{{ $listing->productDetail->warranty_period_months }} Months</dd>
                    </div>
                @endif
                @if($listing->productDetail->packaging_type)
                    <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                        <dt class="text-gray-400 font-medium text-[11px]">Packaging</dt>
                        <dd class="font-semibold text-gray-800">{{ $listing->productDetail->packaging_type }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif

    {{-- Service Detail Specifics --}}
    @if($listing->serviceDetail)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Service Scope & Deliverables</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Service Type</dt>
                    <dd class="font-semibold text-gray-800">{{ ucfirst($listing->serviceDetail->service_type ?? 'Standard') }}</dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Delivery Mode</dt>
                    <dd class="font-semibold text-gray-800">{{ ucfirst($listing->serviceDetail->delivery_mode ?? 'Remote / On-site') }}</dd>
                </div>
            </dl>
        </div>
    @endif
</x-backend.form-card>
