@if(!$activeType)
    <div>
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Type</p>
        <div class="space-y-1.5">
            @foreach(['' => 'All', 'product' => 'Products', 'service' => 'Services'] as $value => $label)
                <label class="flex items-center gap-2 text-sm" style="color:var(--fe-text-muted);">
                    <input type="radio" name="listing_type" value="{{ $value }}" @checked(($filters['listing_type'] ?? '') === $value) style="accent-color:var(--fe-primary);">
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>
@endif

<div class="mt-5">
    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Category</p>
    <select name="category" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->slug }}" @selected(($filters['category'] ?? '') === $cat->slug)>{{ $cat->name }}</option>
        @endforeach
    </select>
</div>

@if($brands->isNotEmpty())
    <div class="mt-5">
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Brand</p>
        <select name="brand" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
            <option value="">All Brands</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>
@endif

<div class="mt-5">
    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Price range</p>
    <div class="flex items-center gap-2">
        <input type="number" name="min_price" min="0" value="{{ $filters['min_price'] ?? '' }}" placeholder="Min" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
        <span style="color:var(--fe-text-subtle);">&ndash;</span>
        <input type="number" name="max_price" min="0" value="{{ $filters['max_price'] ?? '' }}" placeholder="Max" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
    </div>
</div>

<div class="mt-5">
    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Minimum order quantity</p>
    <input type="number" name="min_moq" min="0" value="{{ $filters['min_moq'] ?? '' }}" placeholder="e.g. 10" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
</div>

@if($activeType !== 'service')
    <div class="mt-5">
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Stock status</p>
        <select name="stock_status" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
            <option value="">Any</option>
            <option value="in_stock" @selected(($filters['stock_status'] ?? '') === 'in_stock')>In Stock</option>
            <option value="limited" @selected(($filters['stock_status'] ?? '') === 'limited')>Limited</option>
            <option value="out_of_stock" @selected(($filters['stock_status'] ?? '') === 'out_of_stock')>Out of Stock</option>
        </select>
    </div>
@endif

@if($activeType !== 'product')
    <div class="mt-5">
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Service mode</p>
        <select name="service_mode" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
            <option value="">Any</option>
            <option value="onsite" @selected(($filters['service_mode'] ?? '') === 'onsite')>Onsite</option>
            <option value="remote" @selected(($filters['service_mode'] ?? '') === 'remote')>Remote</option>
            <option value="hybrid" @selected(($filters['service_mode'] ?? '') === 'hybrid')>Hybrid</option>
        </select>
    </div>
@endif

<div class="mt-5">
    <label class="flex items-center gap-2 text-sm" style="color:var(--fe-text-muted);">
        <input type="checkbox" name="verified" value="1" @checked($filters['verified'] ?? false) style="accent-color:var(--fe-primary);">
        Verified suppliers only
    </label>
</div>
