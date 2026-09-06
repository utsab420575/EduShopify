
<?php
    $hasTiers   = $listing->allTierPrices?->whereNull('listing_variant_id')->isNotEmpty();
    $hasVariants = $listing->isProduct() && $listing->variants->isNotEmpty();
    $timelineSteps = [
        [
            'icon'  => 'fa-circle-plus',
            'label' => 'Listing Created',
            'sub'   => $listing->created_at?->format('d M Y, h:i A'),
            'done'  => true,
            'alert' => null,
        ],
        [
            'icon'  => 'fa-paper-plane',
            'label' => 'Submitted for Approval',
            'sub'   => $listing->setup_completed_at?->format('d M Y, h:i A'),
            'done'  => !in_array($listing->approval_status, ['draft']),
            'alert' => null,
        ],
        [
            'icon'  => 'fa-circle-check',
            'label' => 'Approved by Platform',
            'sub'   => $listing->approved_at?->format('d M Y, h:i A'),
            'done'  => $listing->approval_status === 'approved',
            'alert' => $listing->approval_status === 'rejected' ? 'Rejected' : null,
        ],
        [
            'icon'  => 'fa-globe',
            'label' => 'Published & Live',
            'sub'   => $listing->published_at?->format('d M Y, h:i A'),
            'done'  => (bool) $listing->published_at,
            'alert' => null,
        ],
    ];
    $timelineTotal = count($timelineSteps);
?>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    
    <div class="px-5 pt-4 pb-3 border-b border-gray-100 flex items-center justify-between">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Listing Status</span>
        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $listing->approval_status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->approval_status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $attributes = $__attributesOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__attributesOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $component = $__componentOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__componentOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?>
    </div>

    <div class="px-5 py-4">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->base_price): ?>
            <div class="mb-1">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-gray-900 tracking-tight">
                        <?php echo e(number_format($listing->base_price, 2)); ?>

                    </span>
                    <span class="text-sm font-semibold text-gray-500"><?php echo e($listing->currency_code); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->compare_at_price && $listing->compare_at_price > $listing->base_price): ?>
                    <span class="text-xs text-gray-400 line-through"><?php echo e($listing->currency_code); ?> <?php echo e(number_format($listing->compare_at_price, 2)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex items-center gap-1.5 mt-1">
                <i class="fa-solid fa-tag text-indigo-400 text-[10px]"></i>
                <span class="text-xs text-gray-500">
                    <?php echo e($listing->pricingType?->name ?? ucfirst(str_replace('_', ' ', $listing->pricing_type ?? 'Fixed Price'))); ?>

                </span>
            </div>
        <?php else: ?>
            <div class="mb-1">
                <span class="text-xl font-extrabold text-gray-700">Request a Quote</span>
            </div>
            <div class="flex items-center gap-1.5 mt-1">
                <i class="fa-solid fa-envelope text-indigo-400 text-[10px]"></i>
                <span class="text-xs text-gray-500">Price provided on request (RFQ)</span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasTiers): ?>
            <div class="mt-3 flex items-center gap-1.5 py-2 px-3 rounded-lg bg-indigo-50 border border-indigo-100">
                <i class="fa-solid fa-layer-group text-indigo-500 text-[10px]"></i>
                <span class="text-xs text-indigo-700 font-medium">Volume pricing tiers available — see Overview tab</span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasVariants): ?>
            <div class="mt-2 flex items-center gap-1.5 py-2 px-3 rounded-lg bg-purple-50 border border-purple-100">
                <i class="fa-solid fa-cubes text-purple-500 text-[10px]"></i>
                <span class="text-xs text-purple-700 font-medium"><?php echo e($listing->variants->count()); ?> variant<?php echo e($listing->variants->count() !== 1 ? 's' : ''); ?> available — see Variants tab</span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Quick Info</h3>
    </div>
    <dl class="divide-y divide-gray-50 text-xs">
        
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500">
                <i class="fa-solid fa-tag w-3.5 text-gray-400 text-center"></i> Type
            </dt>
            <dd>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold <?php echo e($listing->isProduct() ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'); ?>">
                    <?php echo e($listing->listingType?->name ?? ucfirst($listing->listing_type ?? 'Product')); ?>

                </span>
            </dd>
        </div>

        
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500">
                <i class="fa-solid fa-folder w-3.5 text-gray-400 text-center"></i> Category
            </dt>
            <dd class="font-semibold text-gray-800 text-right max-w-[55%] truncate">
                <?php echo e($listing->mainCategory?->name ?? '—'); ?>

            </dd>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->brand): ?>
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-regular fa-registered w-3.5 text-gray-400 text-center"></i> Brand
                </dt>
                <dd class="font-semibold text-gray-800"><?php echo e($listing->brand->name); ?></dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->unit): ?>
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-solid fa-ruler w-3.5 text-gray-400 text-center"></i> Unit
                </dt>
                <dd class="font-semibold text-gray-800"><?php echo e($listing->unit->symbol ?? $listing->unit->name); ?></dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->min_order_quantity): ?>
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-solid fa-boxes-stacked w-3.5 text-gray-400 text-center"></i> Min. Order
                </dt>
                <dd class="font-semibold text-gray-800"><?php echo e(number_format($listing->min_order_quantity, 0)); ?> units</dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->sku): ?>
            <div class="flex items-center justify-between px-5 py-3">
                <dt class="flex items-center gap-2 text-gray-500">
                    <i class="fa-solid fa-barcode w-3.5 text-gray-400 text-center"></i> SKU
                </dt>
                <dd class="font-mono font-semibold text-gray-800"><?php echo e($listing->sku); ?></dd>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="flex items-center justify-between px-5 py-3">
            <dt class="flex items-center gap-2 text-gray-500">
                <i class="fa-solid fa-hashtag w-3.5 text-gray-400 text-center"></i> Listing ID
            </dt>
            <dd class="font-mono text-gray-600"><?php echo e($listing->listing_number); ?></dd>
        </div>
    </dl>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Listing Timeline</h3>
    </div>
    <div class="px-5 py-4">
        <ol class="relative space-y-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $timelineSteps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stepIdx => $tStep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex gap-3 <?php echo e($stepIdx < $timelineTotal - 1 ? 'pb-4' : ''); ?> relative">
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stepIdx < $timelineTotal - 1): ?>
                        <div class="absolute left-[13px] top-7 bottom-0 w-0.5 <?php echo e($tStep['done'] ? 'bg-indigo-200' : 'bg-gray-200'); ?>"></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="flex-shrink-0 z-10 w-7 h-7 rounded-full flex items-center justify-center mt-0.5
                        <?php echo e($tStep['alert'] ? 'bg-red-100' : ($tStep['done'] ? 'bg-indigo-100' : 'bg-gray-100')); ?>">
                        <i class="fa-solid <?php echo e($tStep['icon']); ?> text-[11px]
                            <?php echo e($tStep['alert'] ? 'text-red-500' : ($tStep['done'] ? 'text-indigo-600' : 'text-gray-400')); ?>"></i>
                    </div>

                    
                    <div class="flex-1 min-w-0 pt-1">
                        <p class="text-xs font-semibold
                            <?php echo e($tStep['alert'] ? 'text-red-600' : ($tStep['done'] ? 'text-gray-900' : 'text-gray-400')); ?>">
                            <?php echo e($tStep['alert'] ?? $tStep['label']); ?>

                        </p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tStep['sub']): ?>
                            <p class="text-[11px] text-gray-400 mt-0.5"><?php echo e($tStep['sub']); ?></p>
                        <?php elseif(!$tStep['done']): ?>
                            <p class="text-[11px] text-gray-300 mt-0.5">Pending</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ol>
    </div>
</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\catalog\listings\partials\preview-sidebar.blade.php ENDPATH**/ ?>