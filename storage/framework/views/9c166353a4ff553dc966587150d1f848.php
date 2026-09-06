<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$activeType): ?>
    <div>
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Type</p>
        <div class="space-y-1.5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['' => 'All', 'product' => 'Products', 'service' => 'Services']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-2 text-sm" style="color:var(--fe-text-muted);">
                    <input type="radio" name="listing_type" value="<?php echo e($value); ?>" <?php if(($filters['listing_type'] ?? '') === $value): echo 'checked'; endif; ?> style="accent-color:var(--fe-primary);">
                    <?php echo e($label); ?>

                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="mt-5">
    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Category</p>
    <select name="category" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
        <option value="">All Categories</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cat->slug); ?>" <?php if(($filters['category'] ?? '') === $cat->slug): echo 'selected'; endif; ?>><?php echo e($cat->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </select>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($brands->isNotEmpty()): ?>
    <div class="mt-5">
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Brand</p>
        <select name="brand" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
            <option value="">All Brands</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($brand->slug); ?>" <?php if(($filters['brand'] ?? '') === $brand->slug): echo 'selected'; endif; ?>><?php echo e($brand->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="mt-5">
    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Price range</p>
    <div class="flex items-center gap-2">
        <input type="number" name="min_price" min="0" value="<?php echo e($filters['min_price'] ?? ''); ?>" placeholder="Min" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
        <span style="color:var(--fe-text-subtle);">&ndash;</span>
        <input type="number" name="max_price" min="0" value="<?php echo e($filters['max_price'] ?? ''); ?>" placeholder="Max" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
    </div>
</div>

<div class="mt-5">
    <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Minimum order quantity</p>
    <input type="number" name="min_moq" min="0" value="<?php echo e($filters['min_moq'] ?? ''); ?>" placeholder="e.g. 10" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm" style="border-color:var(--fe-border);">
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeType !== 'service'): ?>
    <div class="mt-5">
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Stock status</p>
        <select name="stock_status" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
            <option value="">Any</option>
            <option value="in_stock" <?php if(($filters['stock_status'] ?? '') === 'in_stock'): echo 'selected'; endif; ?>>In Stock</option>
            <option value="limited" <?php if(($filters['stock_status'] ?? '') === 'limited'): echo 'selected'; endif; ?>>Limited</option>
            <option value="out_of_stock" <?php if(($filters['stock_status'] ?? '') === 'out_of_stock'): echo 'selected'; endif; ?>>Out of Stock</option>
        </select>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeType !== 'product'): ?>
    <div class="mt-5">
        <p class="text-sm font-semibold mb-2" style="color:var(--fe-text);">Service mode</p>
        <select name="service_mode" class="fe-focus-ring w-full h-11 px-3 rounded-xl border text-sm bg-white" style="border-color:var(--fe-border);">
            <option value="">Any</option>
            <option value="onsite" <?php if(($filters['service_mode'] ?? '') === 'onsite'): echo 'selected'; endif; ?>>Onsite</option>
            <option value="remote" <?php if(($filters['service_mode'] ?? '') === 'remote'): echo 'selected'; endif; ?>>Remote</option>
            <option value="hybrid" <?php if(($filters['service_mode'] ?? '') === 'hybrid'): echo 'selected'; endif; ?>>Hybrid</option>
        </select>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="mt-5">
    <label class="flex items-center gap-2 text-sm" style="color:var(--fe-text-muted);">
        <input type="checkbox" name="verified" value="1" <?php if($filters['verified'] ?? false): echo 'checked'; endif; ?> style="accent-color:var(--fe-primary);">
        Verified suppliers only
    </label>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\frontend\catalog\_filters.blade.php ENDPATH**/ ?>