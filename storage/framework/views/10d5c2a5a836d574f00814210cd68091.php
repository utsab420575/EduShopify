


<style>
    .listing-lightbox { display: none; }
    .listing-lightbox:target { display: flex; }
</style>


<?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => $listing->name,'subtitle' => 'Listing #' . ($listing->sku ?? $listing->listing_number)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($listing->name),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Listing #' . ($listing->sku ?? $listing->listing_number))]); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <div class="flex items-center gap-2 flex-wrap">
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold',
                'bg-amber-100 text-amber-800 border border-amber-200' => $listing->approval_status === 'pending',
                'bg-emerald-100 text-emerald-800 border border-emerald-200' => $listing->approval_status === 'approved',
                'bg-rose-100 text-rose-800 border border-rose-200' => $listing->approval_status === 'rejected',
                'bg-gray-100 text-gray-600 border border-gray-200' => !in_array($listing->approval_status, ['pending','approved','rejected']),
            ]); ?>">
                <i class="fa-solid fa-circle text-[6px]"></i>
                <?php echo e(ucfirst($listing->approval_status)); ?>

            </span>
            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border',
                'bg-blue-50 text-blue-700 border-blue-200' => $listing->is_active,
                'bg-gray-50 text-gray-500 border-gray-200' => !$listing->is_active,
            ]); ?>">
                <?php echo e($listing->is_active ? 'Active' : 'Inactive'); ?>

            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 uppercase">
                <?php echo e($listing->listing_type); ?>

            </span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->is_featured): ?>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <i class="fa-solid fa-star text-amber-500 text-[10px]"></i> Featured
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccefb989a1afce853acb3cdbc40307e)): ?>
<?php $attributes = $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e; ?>
<?php unset($__attributesOriginal6ccefb989a1afce853acb3cdbc40307e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccefb989a1afce853acb3cdbc40307e)): ?>
<?php $component = $__componentOriginal6ccefb989a1afce853acb3cdbc40307e; ?>
<?php unset($__componentOriginal6ccefb989a1afce853acb3cdbc40307e); ?>
<?php endif; ?>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->approval_status === 'pending'): ?>
    <div class="mb-5 rounded-xl border-l-4 border-amber-400 bg-amber-50 border border-amber-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-amber-200/60 flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-clock text-amber-600 text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-900">Awaiting Your Decision</p>
                <p class="text-xs text-amber-700">This listing has been submitted by the supplier and is pending your review and moderation.</p>
            </div>
        </div>
        <div class="px-5 py-3.5 flex flex-wrap items-center gap-3">
            <div class="flex items-start gap-2">
                <form method="POST" action="<?php echo e(route('admin.catalog.listings.approve', $listing)); ?>" onsubmit="return confirmSwal(this, 'Approve & Publish Listing?', 'This will approve the listing and publish it immediately to the public marketplace.', 'question', 'Yes, Approve & Publish')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm flex items-center gap-2 transition-colors">
                        <i class="fa-solid fa-check-circle text-emerald-200"></i>
                        Approve & Publish
                    </button>
                </form>
            </div>
            <div class="flex items-start gap-2">
                <button type="button" @click="$dispatch('open-modal-reject')"
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-ban text-rose-200"></i>
                    Reject Listing
                </button>
            </div>
            <div class="flex items-center gap-2 ml-auto">
                <form method="POST" action="<?php echo e(route('admin.catalog.listings.feature', $listing)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-3.5 py-2 rounded-lg border border-amber-300 bg-white text-amber-700 hover:bg-amber-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-star <?php echo e($listing->is_featured ? 'text-amber-500' : 'text-gray-400'); ?>"></i>
                        <?php echo e($listing->is_featured ? 'Remove Featured' : 'Mark as Featured'); ?>

                    </button>
                </form>
                <a href="<?php echo e(route('frontend.listings.show', $listing)); ?>" target="_blank"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i> View on Marketplace
                </a>
                <a href="<?php echo e(route('admin.approvals.show', 'listings')); ?>"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-list-check text-gray-400"></i> Approval Queue
                </a>
            </div>
        </div>
    </div>

<?php elseif($listing->approval_status === 'approved'): ?>
    <div class="mb-5 rounded-xl border-l-4 border-emerald-500 bg-emerald-50 border border-emerald-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-emerald-200/60 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-emerald-900">Approved & Published</p>
                    <p class="text-xs text-emerald-700">This listing is live on the marketplace.<?php echo e($listing->approved_at ? ' Approved ' . $listing->approved_at->diffForHumans() . '.' : ''); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form method="POST" action="<?php echo e(route('admin.catalog.listings.undo-approve', $listing)); ?>" onsubmit="return confirmSwal(this, 'Revert Approval to Pending?', 'This will revoke approval and return the listing to Pending Review.', 'warning', 'Yes, Revert to Pending')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                        <i class="fa-solid fa-rotate-left"></i> Undo Approval
                    </button>
                </form>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->is_active): ?>
                    <button type="button" @click="$dispatch('open-modal-deactivate')"
                            class="px-3.5 py-2 rounded-lg border border-rose-300 bg-white text-rose-600 hover:bg-rose-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-pause"></i> Suspend
                    </button>
                <?php else: ?>
                    <form method="POST" action="<?php echo e(route('admin.catalog.listings.reactivate', $listing)); ?>" onsubmit="return confirmSwal(this, 'Reactivate Listing?', 'This will reactivate the listing.', 'question', 'Yes, Reactivate')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="px-3.5 py-2 rounded-lg border border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                            <i class="fa-solid fa-play"></i> Reactivate
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <form method="POST" action="<?php echo e(route('admin.catalog.listings.feature', $listing)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-star <?php echo e($listing->is_featured ? 'text-amber-500' : 'text-gray-400'); ?>"></i>
                        <?php echo e($listing->is_featured ? 'Remove Featured' : 'Mark as Featured'); ?>

                    </button>
                </form>
                <a href="<?php echo e(route('frontend.listings.show', $listing)); ?>" target="_blank"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i> Marketplace
                </a>
                <a href="<?php echo e(route('admin.approvals.show', 'listings')); ?>"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-list-check text-gray-400"></i> Queue
                </a>
            </div>
        </div>
    </div>

<?php elseif($listing->approval_status === 'rejected'): ?>
    <div class="mb-5 rounded-xl border-l-4 border-rose-500 bg-rose-50 border border-rose-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-rose-200/60 flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-rose-900">Listing Was Rejected</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->rejection_reason): ?>
                    <p class="text-xs text-rose-700 mt-0.5"><span class="font-semibold">Reason given to supplier:</span> <?php echo e($listing->rejection_reason); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
                <form method="POST" action="<?php echo e(route('admin.catalog.listings.approve', $listing)); ?>" onsubmit="return confirmSwal(this, 'Approve Previously Rejected Listing?', 'This will approve and publish the listing to the marketplace.', 'question', 'Yes, Approve & Publish')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-check"></i> Re-Approve & Publish
                    </button>
                </form>
                <a href="<?php echo e(route('admin.approvals.show', 'listings')); ?>"
                   class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-list-check text-gray-400"></i> Queue
                </a>
            </div>
        </div>
    </div>

<?php else: ?>
    
    <div class="mb-5 bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-semibold text-gray-500">Moderation Actions:</span>
            <form method="POST" action="<?php echo e(route('admin.catalog.listings.feature', $listing)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-star <?php echo e($listing->is_featured ? 'text-amber-500' : 'text-gray-400'); ?>"></i>
                    <?php echo e($listing->is_featured ? 'Remove Featured' : 'Mark as Featured'); ?>

                </button>
            </form>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('frontend.listings.show', $listing)); ?>" target="_blank" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i> View on Marketplace
            </a>
            <a href="<?php echo e(route('admin.approvals.show', 'listings')); ?>" class="px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-list-check text-gray-400"></i> Approval Queue
            </a>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ activeTab: 'overview' }">

    <div class="lg:col-span-8 space-y-0">
        
        <div class="bg-white rounded-t-xl border border-b-0 border-gray-200 px-1">
            <nav class="flex gap-0 overflow-x-auto">
                <button type="button" @click="activeTab = 'overview'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'overview' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-images text-xs"></i> Overview & Media
                </button>
                <button type="button" @click="activeTab = 'specs'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'specs' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-sliders text-xs"></i> Specifications
                </button>
                <button type="button" @click="activeTab = 'pricing'"
                        class="px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition-colors"
                        :class="activeTab === 'pricing' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'">
                    <i class="fa-solid fa-tags text-xs"></i> Pricing & Variants
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->variants->isNotEmpty()): ?>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                              :class="activeTab === 'pricing' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'"><?php echo e($listing->variants->count()); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
            </nav>
        </div>

        
        <div class="bg-white rounded-b-xl border border-gray-200">
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="space-y-5 p-5 lg:max-h-[62vh] lg:overflow-y-auto">
                <?php echo $__env->make('backend.admin.catalog.listings.partials.gallery', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('backend.admin.catalog.listings.partials.basic-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div x-show="activeTab === 'specs'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="space-y-5 p-5 lg:max-h-[62vh] lg:overflow-y-auto">
                <?php echo $__env->make('backend.admin.catalog.listings.partials.specifications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div x-show="activeTab === 'pricing'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="space-y-5 p-5 lg:max-h-[62vh] lg:overflow-y-auto">
                
                <?php echo $__env->make('backend.admin.catalog.listings.partials.commercial-terms', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                
                <?php ($globalTiers = $listing->allTierPrices->whereNull('listing_variant_id')->values()); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($globalTiers->isNotEmpty()): ?>
                    <div class="rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2 bg-gray-50">
                            <i class="fa-solid fa-layer-group text-indigo-400 text-xs"></i>
                            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Global Tier / Volume Pricing</h3>
                            <span class="text-[10px] text-gray-400 ml-auto">Applies to the base product (all variants unless overridden)</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="px-5 py-2.5 font-semibold">Quantity Range</th>
                                        <th class="px-4 py-2.5 font-semibold text-right">Unit Price</th>
                                        <th class="px-4 py-2.5 font-semibold text-right">Discount vs. Base</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $globalTiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php ($disc = $listing->base_price > 0 ? round((1 - $tp->unit_price / $listing->base_price) * 100) : null); ?>
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-5 py-2.5 font-medium text-gray-800">
                                                <?php echo e((int)$tp->min_quantity); ?> &ndash; <?php echo e($tp->max_quantity ? (int)$tp->max_quantity : '∞'); ?> units
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-bold text-indigo-700">
                                                <?php echo e($tp->currency_code); ?> <?php echo e(number_format($tp->unit_price, 2)); ?>

                                            </td>
                                            <td class="px-4 py-2.5 text-right">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($disc && $disc > 0): ?>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">−<?php echo e($disc); ?>%</span>
                                                <?php else: ?>
                                                    <span class="text-gray-300">—</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->variants->isNotEmpty()): ?>
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-cubes text-indigo-400 text-sm"></i>
                            <h3 class="text-sm font-bold text-gray-800">Product Variants (<?php echo e($listing->variants->count()); ?>)</h3>
                        </div>
                        <?php echo $__env->make('backend.admin.catalog.listings.partials.variants', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="lg:col-span-4 space-y-4">
        <?php echo $__env->make('backend.admin.catalog.listings.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($listing->approval_status === 'pending'): ?>
    <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'reject','title' => 'Reject Listing']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'reject','title' => 'Reject Listing']); ?>
        <form method="POST" action="<?php echo e(route('admin.catalog.listings.reject', $listing)); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-3">
                <p class="text-xs text-gray-500">
                    Please provide a clear reason for rejecting this listing. The supplier will see this reason and can make necessary revisions.
                </p>
                <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'reason','label' => 'Rejection Note / Feedback to Supplier','placeholder' => 'e.g. Incomplete specifications, invalid brand claim, low resolution images...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'reason','label' => 'Rejection Note / Feedback to Supplier','placeholder' => 'e.g. Incomplete specifications, invalid brand claim, low resolution images...','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e)): ?>
<?php $attributes = $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e; ?>
<?php unset($__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e)): ?>
<?php $component = $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e; ?>
<?php unset($__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e); ?>
<?php endif; ?>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="open = false" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">Confirm Rejection</button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5845bcee7aa8bdff54827061cf154d18)): ?>
<?php $attributes = $__attributesOriginal5845bcee7aa8bdff54827061cf154d18; ?>
<?php unset($__attributesOriginal5845bcee7aa8bdff54827061cf154d18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5845bcee7aa8bdff54827061cf154d18)): ?>
<?php $component = $__componentOriginal5845bcee7aa8bdff54827061cf154d18; ?>
<?php unset($__componentOriginal5845bcee7aa8bdff54827061cf154d18); ?>
<?php endif; ?>
<?php elseif($listing->is_active): ?>
    <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'deactivate','title' => 'Suspend / Deactivate Listing']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'deactivate','title' => 'Suspend / Deactivate Listing']); ?>
        <form method="POST" action="<?php echo e(route('admin.catalog.listings.deactivate', $listing)); ?>">
            <?php echo csrf_field(); ?>
            <div class="space-y-3">
                <p class="text-xs text-gray-500">
                    Enter the reason for taking down this active listing from the marketplace.
                </p>
                <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'reason','label' => 'Suspension Reason','placeholder' => 'e.g. Policy violation, out of stock dispute...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'reason','label' => 'Suspension Reason','placeholder' => 'e.g. Policy violation, out of stock dispute...','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e)): ?>
<?php $attributes = $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e; ?>
<?php unset($__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e)): ?>
<?php $component = $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e; ?>
<?php unset($__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e); ?>
<?php endif; ?>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="open = false" class="text-xs font-semibold px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">Suspend Listing</button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5845bcee7aa8bdff54827061cf154d18)): ?>
<?php $attributes = $__attributesOriginal5845bcee7aa8bdff54827061cf154d18; ?>
<?php unset($__attributesOriginal5845bcee7aa8bdff54827061cf154d18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5845bcee7aa8bdff54827061cf154d18)): ?>
<?php $component = $__componentOriginal5845bcee7aa8bdff54827061cf154d18; ?>
<?php unset($__componentOriginal5845bcee7aa8bdff54827061cf154d18); ?>
<?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\listings\_panel.blade.php ENDPATH**/ ?>