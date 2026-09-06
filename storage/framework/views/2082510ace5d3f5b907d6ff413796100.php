
<?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Commercial Terms']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Commercial Terms']); ?>
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Pricing Type</dt>
            <dd class="font-bold text-gray-900"><?php echo e($listing->pricingType?->name ?? ucfirst($listing->pricing_type ?? 'Fixed')); ?></dd>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Base Price</dt>
            <dd class="font-bold text-indigo-700 text-sm">
                <?php echo e($listing->base_price ? $listing->currency_code . ' ' . number_format($listing->base_price, 2) : 'Negotiable / Quote'); ?>

            </dd>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->compare_at_price): ?>
            <div class="p-3 bg-gray-50 rounded-lg">
                <dt class="text-gray-500 font-medium mb-0.5">Compare-At Price</dt>
                <dd class="font-bold text-gray-500 line-through"><?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->compare_at_price, 2)); ?></dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-gray-500 font-medium mb-0.5">Minimum Order Quantity (MOQ)</dt>
            <dd class="font-bold text-gray-900"><?php echo e($listing->min_order_quantity ? (int)$listing->min_order_quantity . ' ' . ($listing->unit?->symbol ?? 'units') : '1 unit'); ?></dd>
        </div>
    </dl>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->productDetail): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Product Logistics & Policies</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Product Type</dt>
                    <dd class="font-semibold text-gray-800"><?php echo e(ucfirst($listing->productDetail->product_type ?? 'Simple')); ?></dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Stock Status</dt>
                    <dd class="font-semibold text-gray-800"><?php echo e(str_replace('_', ' ', ucfirst($listing->productDetail->stock_status ?? 'In Stock'))); ?></dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Lead Time</dt>
                    <dd class="font-semibold text-gray-800"><?php echo e($listing->productDetail->lead_time_days ? $listing->productDetail->lead_time_days . ' days' : 'Immediate'); ?></dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Country of Origin</dt>
                    <dd class="font-semibold text-gray-800"><?php echo e($listing->productDetail->originCountry?->name ?? '—'); ?></dd>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->productDetail->warranty_period_months): ?>
                    <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                        <dt class="text-gray-400 font-medium text-[11px]">Warranty</dt>
                        <dd class="font-semibold text-gray-800"><?php echo e($listing->productDetail->warranty_period_months); ?> Months</dd>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->productDetail->packaging_type): ?>
                    <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                        <dt class="text-gray-400 font-medium text-[11px]">Packaging</dt>
                        <dd class="font-semibold text-gray-800"><?php echo e($listing->productDetail->packaging_type); ?></dd>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </dl>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->serviceDetail): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-3">Service Scope & Deliverables</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Service Type</dt>
                    <dd class="font-semibold text-gray-800"><?php echo e(ucfirst($listing->serviceDetail->service_type ?? 'Standard')); ?></dd>
                </div>
                <div class="p-2.5 bg-gray-50/70 rounded-lg border border-gray-100">
                    <dt class="text-gray-400 font-medium text-[11px]">Delivery Mode</dt>
                    <dd class="font-semibold text-gray-800"><?php echo e(ucfirst($listing->serviceDetail->delivery_mode ?? 'Remote / On-site')); ?></dd>
                </div>
            </dl>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $attributes = $__attributesOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__attributesOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c6ebeac636fe1a833069360516dddbb)): ?>
<?php $component = $__componentOriginal3c6ebeac636fe1a833069360516dddbb; ?>
<?php unset($__componentOriginal3c6ebeac636fe1a833069360516dddbb); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\listings\partials\commercial-terms.blade.php ENDPATH**/ ?>