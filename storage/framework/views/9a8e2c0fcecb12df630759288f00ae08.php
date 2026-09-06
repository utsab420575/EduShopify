
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->isProduct()): ?>
    <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Product Variants','description' => ''.e($listing->variants->isNotEmpty() ? $listing->variants->count() . ' variant(s). To add, edit, or remove variants, use Edit Listing above.' : '').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Product Variants','description' => ''.e($listing->variants->isNotEmpty() ? $listing->variants->count() . ' variant(s). To add, edit, or remove variants, use Edit Listing above.' : '').'']); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->variants->isEmpty()): ?>
            <p class="text-xs text-gray-400">
                No variants configured. If this item has multiple options (sizes, colors, models), add them from
                <a href="<?php echo e(route('supplier.catalog.listings.edit', $listing)); ?>" class="text-indigo-600 font-semibold hover:underline">Edit Listing</a>.
            </p>
        <?php else: ?>
            
            <style>
                .variant-photos-modal { display: none; }
                .variant-photos-modal:target { display: flex; }
            </style>

            <div class="overflow-x-auto -mx-5 -mb-5">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50/80 border-y border-gray-100 text-gray-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-5 py-3 font-semibold w-16">Photo</th>
                            <th class="px-3 py-3 font-semibold">Variant Details</th>
                            <th class="px-3 py-3 font-semibold">SKU</th>
                            <th class="px-3 py-3 font-semibold">Price</th>
                            <th class="px-3 py-3 font-semibold">Stock</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $listing->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($variantCover = $variant->images->firstWhere('pivot.is_primary', true) ?? $variant->images->first()); ?>
                            <tr>
                                <td class="px-5 py-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variantCover): ?>
                                        <a href="#variant-photos-<?php echo e($variant->id); ?>" class="relative block w-10 h-10" title="View all <?php echo e($variant->images->count()); ?> photo(s)">
                                            <img src="<?php echo e($variantCover->getUrl()); ?>" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-200 hover:opacity-80 transition">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant->images->count() > 1): ?>
                                                <span class="absolute -bottom-1 -right-1 text-[9px] font-bold bg-gray-900/80 text-white rounded-full w-4 h-4 flex items-center justify-center">
                                                    <?php echo e($variant->images->count()); ?>

                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </a>

                                        
                                        <div id="variant-photos-<?php echo e($variant->id); ?>" class="variant-photos-modal fixed inset-0 z-50 items-center justify-center p-4">
                                            <a href="#" class="absolute inset-0 bg-gray-900/70" aria-label="Close"></a>
                                            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[85vh] overflow-y-auto p-5">
                                                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                                                    <h4 class="text-sm font-bold text-gray-900"><?php echo e($variant->name); ?> — <?php echo e($variant->images->count()); ?> photo(s)</h4>
                                                    <a href="#" class="text-gray-400 hover:text-gray-600 text-sm" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
                                                </div>
                                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variant->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="relative">
                                                            <img src="<?php echo e($img->getUrl()); ?>" alt="" class="w-full h-28 object-cover rounded-lg border <?php echo e($img->pivot->is_primary ? 'border-amber-400 ring-2 ring-amber-300' : 'border-gray-200'); ?>">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($img->pivot->is_primary): ?>
                                                                <span class="absolute top-1.5 left-1.5 text-[9px] font-bold uppercase tracking-wide bg-amber-500 text-white px-1.5 py-0.5 rounded">Cover</span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center text-gray-300">
                                            <i class="fa-solid fa-image text-xs"></i>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-3 py-3">
                                    <p class="font-bold text-gray-900"><?php echo e($variant->name); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant->variantAttributes->isNotEmpty()): ?>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variant->variantAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $va): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">
                                                    <span class="text-gray-400 mr-1"><?php echo e($va->attribute?->name); ?>:</span>
                                                    <strong><?php echo e($va->resolvedValue()); ?></strong>
                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-3 py-3 font-mono text-gray-600"><?php echo e($variant->sku ?: '—'); ?></td>
                                <td class="px-3 py-3 font-bold text-indigo-700 text-sm"><?php echo e($variant->currency_code); ?> <?php echo e(number_format($variant->price, 2)); ?></td>
                                <td class="px-3 py-3 font-semibold text-gray-800"><?php echo e((int) $variant->stock_quantity); ?></td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold <?php echo e(($variant->stock_status ?? 'in_stock') === 'in_stock' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'); ?>">
                                        <?php echo e(str_replace('_', ' ', $variant->stock_status ?? 'in_stock')); ?>

                                    </span>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($variant->tierPrices->isNotEmpty()): ?>
                                <tr>
                                    <td colspan="6" class="px-5 pb-3 pt-0 bg-gray-50/40">
                                        <details class="text-xs">
                                            <summary class="cursor-pointer font-semibold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1.5 py-1">
                                                <i class="fa-solid fa-layer-group"></i>
                                                <?php echo e($variant->tierPrices->count()); ?> quantity-break tier<?php echo e($variant->tierPrices->count() === 1 ? '' : 's'); ?> for this variant
                                            </summary>
                                            <div class="overflow-x-auto rounded-lg border border-gray-200 mt-2">
                                                <table class="w-full text-xs text-left">
                                                    <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                                                        <tr>
                                                            <th class="px-4 py-2 font-semibold">Quantity Range</th>
                                                            <th class="px-4 py-2 font-semibold">Unit Price</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100 bg-white">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $variant->tierPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td class="px-4 py-2 font-medium text-gray-800"><?php echo e(number_format($tp->min_quantity, 0)); ?> &ndash; <?php echo e($tp->max_quantity ? number_format($tp->max_quantity, 0) : '∞'); ?> units</td>
                                                                <td class="px-4 py-2 font-bold text-indigo-700"><?php echo e($tp->currency_code); ?> <?php echo e(number_format($tp->unit_price, 2)); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
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
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\catalog\listings\partials\preview-variants.blade.php ENDPATH**/ ?>