<?php $__env->startSection('title', 'Quotation — ' . $quotation->quotation_number); ?>
<?php $__env->startSection('breadcrumb', 'Quotations / ' . $quotation->quotation_number); ?>

<?php $__env->startSection('body'); ?>

    <?php
        $requestedItems = $quotation->items->where('is_optional_addon', false);
        $addonItems = $quotation->items->where('is_optional_addon', true);
        $sourceLabel = fn ($item) => $item->is_alternative
            ? 'Alternative Offer'
            : ($item->offered_listing_id ? 'From Your Listing' : 'Custom Offer');
        $sourceClass = fn ($item) => $item->is_alternative
            ? 'bg-amber-50 text-amber-700 border-amber-200'
            : ($item->offered_listing_id ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200');
    ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Quotation '.e($quotation->quotation_number).'','subtitle' => 'For RFQ: '.e($quotation->rfq?->title ?? 'RFQ #' . $quotation->rfq_id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quotation '.e($quotation->quotation_number).'','subtitle' => 'For RFQ: '.e($quotation->rfq?->title ?? 'RFQ #' . $quotation->rfq_id).'']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <div class="flex items-center gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotation->status === 'draft'): ?>
                    <a href="<?php echo e(route('supplier.quotations.edit', $quotation)); ?>" class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <form method="POST" action="<?php echo e(route('supplier.quotations.submit', $quotation)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn-primary text-xs font-bold px-3 py-2 rounded-lg flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Submit Quotation
                        </button>
                    </form>
                <?php elseif($quotation->status === 'revision_requested'): ?>
                    <a href="<?php echo e(route('supplier.quotations.revision.create', $quotation)); ?>" class="btn-primary text-xs font-bold px-3 py-2 rounded-lg flex items-center gap-1.5 animate-pulse">
                        <i class="fa-solid fa-rotate"></i> Submit Revision
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($quotation->status, ['submitted', 'under_review', 'revision_requested', 'revised', 'shortlisted'])): ?>
                    <form method="POST" action="<?php echo e(route('supplier.quotations.withdraw', $quotation)); ?>" onsubmit="return confirm('Withdraw this quotation? You will no longer be considered for award.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">
                            Withdraw Quote
                        </button>
                    </form>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->has('rfq_version')): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-bold text-red-900 flex items-center gap-1.5"><i class="fa-solid fa-triangle-exclamation"></i> This RFQ Has Changed</h4>
            <p class="text-xs text-red-800 mt-1"><?php echo e($errors->first('rfq_version')); ?></p>
            <div class="mt-3 space-y-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $changeLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p class="text-xs text-red-700">
                        v<?php echo e($log->from_version_no); ?> &rarr; v<?php echo e($log->to_version_no); ?>:
                        <?php echo e(collect($log->changed_fields)->map(fn ($f) => ucwords(str_replace('_', ' ', $f)))->implode(', ')); ?>

                    </p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <form method="POST" action="<?php echo e(route('supplier.quotations.submit', $quotation)); ?>" class="mt-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="acknowledge_version_change" value="1">
                <button type="submit" class="text-xs font-semibold px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    I've Reviewed the Changes — Submit Anyway
                </button>
            </form>
        </div>
    <?php elseif($versionChanged && $quotation->status === 'draft'): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5"><i class="fa-solid fa-circle-info"></i> RFQ Updated Since You Started</h4>
            <p class="text-xs text-amber-800 mt-1">The RFQ moved from version <?php echo e($quotation->rfq_version_no); ?> to version <?php echo e($quotation->rfq->current_version_no); ?>. Review the changes before submitting.</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotation->status === 'revision_requested'): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate"></i> Buyer Requested a Quotation Revision
                </h4>
                <p class="text-xs text-amber-800 mt-1"><?php echo e($quotation->revisionRequests->first()?->requested_changes ?? 'Please review your pricing or terms and submit a revised quote.'); ?></p>
            </div>
            <a href="<?php echo e(route('supplier.quotations.revision.create', $quotation)); ?>" class="btn-primary text-xs font-bold px-4 py-2 rounded-lg shrink-0">
                Revise Quotation
            </a>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        
        <div class="xl:col-span-8 space-y-6">

            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Quotation Overview']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quotation Overview']); ?>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4 pb-4 border-b border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block">Status</span>
                        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $quotation->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotation->status)]); ?>
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
                    <div>
                        <span class="text-gray-400 block">Revision No.</span>
                        <span class="font-bold text-gray-800"><?php echo e($quotation->current_revision_no > 0 ? '#'.$quotation->current_revision_no : 'Not yet submitted'); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Total Quoted</span>
                        <span class="font-bold text-indigo-700 text-sm"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($quotation->grand_total, 2)); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Delivery Lead Time</span>
                        <span class="font-semibold text-gray-800"><?php echo e($quotation->lead_time_days ? $quotation->lead_time_days . ' days' : 'As requested'); ?></span>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotation->proposal): ?>
                    <div class="text-xs text-gray-700 whitespace-pre-line bg-gray-50 p-3 rounded-lg border border-gray-100 mb-4">
                        <span class="font-bold block mb-1 text-gray-900">Proposal Summary:</span>
                        <?php echo e($quotation->proposal); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-gray-400 block">Warranty:</span>
                        <span class="font-semibold text-gray-800"><?php echo e($quotation->warranty_terms ?? 'None'); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Support:</span>
                        <span class="font-semibold text-gray-800"><?php echo e($quotation->support_terms ?? 'None'); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Payment:</span>
                        <span class="font-semibold text-gray-800"><?php echo e($quotation->payment_terms ?? 'Standard'); ?></span>
                    </div>
                </div>
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

            
            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Quoted Items']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quoted Items']); ?>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $requestedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php ($rfqItem = $item->rfqItem); ?>
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-gray-900"><?php echo e($item->item_name); ?></p>
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full border <?php echo e($sourceClass($item)); ?>"><?php echo e($sourceLabel($item)); ?></span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rfqItem): ?>
                                        <p class="text-xs text-gray-400 mt-0.5">Responding to: <?php echo e($rfqItem->item_name); ?> (<?php echo e($rfqItem->category?->name ?? 'No category'); ?>)</p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->description): ?>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo e($item->description); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="text-right shrink-0 text-xs">
                                    <p class="text-gray-800 font-semibold"><?php echo e((float) $item->quantity); ?> <?php echo e($item->unit?->symbol ?? $item->custom_unit); ?></p>
                                    <p class="text-gray-500 mt-0.5"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($item->unit_price, 2)); ?> / unit</p>
                                    <p class="text-indigo-700 font-bold mt-0.5"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($item->line_total, 2)); ?></p>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->attributeValues->isNotEmpty()): ?>
                                <div class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $item->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php ($requestedValue = $rfqItem?->attributeValues->firstWhere('attribute_id', $value->attribute_id)); ?>
                                        <?php ($differs = $requestedValue && $requestedValue->formattedValue() !== $value->formattedValue()); ?>
                                        <div class="flex items-center justify-between text-[11px] gap-2">
                                            <span class="text-gray-500"><?php echo e($value->attribute?->name); ?></span>
                                            <span class="text-right">
                                                <span class="text-gray-400"><?php echo e($requestedValue?->formattedValue() ?? '—'); ?></span>
                                                <i class="fa-solid fa-arrow-right text-gray-300 mx-1"></i>
                                                <span class="<?php echo e($differs ? 'text-amber-700 font-semibold' : 'text-gray-700 font-semibold'); ?>"><?php echo e($value->formattedValue()); ?></span>
                                            </span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-400">No items quoted yet.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($addonItems->isNotEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Optional Add-Ons','description' => 'Offered in addition to the buyer\'s requested items.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Optional Add-Ons','description' => 'Offered in addition to the buyer\'s requested items.']); ?>
                    <div class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $addonItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between p-3 bg-amber-50/40 border border-amber-200 rounded-lg text-xs">
                                <div>
                                    <p class="font-semibold text-gray-900"><?php echo e($addon->item_name); ?></p>
                                    <p class="text-gray-500"><?php echo e((float) $addon->quantity); ?> <?php echo e($addon->unit?->symbol); ?> &times; <?php echo e($quotation->currency_code); ?> <?php echo e(number_format($addon->unit_price, 2)); ?></p>
                                </div>
                                <span class="font-bold text-amber-700"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($addon->line_total, 2)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
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

            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Commercial Summary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Commercial Summary']); ?>
                <dl class="space-y-2 text-sm max-w-xs ml-auto">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="text-gray-800"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($quotation->subtotal, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd class="text-gray-800"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($quotation->tax_amount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="text-gray-800">-<?php echo e($quotation->currency_code); ?> <?php echo e(number_format($quotation->discount_amount, 2)); ?></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Shipping</dt><dd class="text-gray-800"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($quotation->shipping_charge, 2)); ?></dd></div>
                    <div class="flex justify-between pt-2 border-t border-gray-100"><dt class="font-semibold text-gray-700">Grand Total</dt><dd class="font-bold text-indigo-700"><?php echo e($quotation->currency_code); ?> <?php echo e(number_format($quotation->grand_total, 2)); ?></dd></div>
                </dl>
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

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotation->revisions->isNotEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Revision History']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Revision History']); ?>
                    <div class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quotation->revisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-3 bg-gray-50 rounded-lg text-xs flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-gray-900">Revision #<?php echo e($rev->revision_no); ?> &middot; <?php echo e($rev->created_at->format('d M Y, h:i A')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rev->change_summary): ?>
                                        <p class="text-gray-600 mt-0.5"><?php echo e($rev->change_summary); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <span class="font-bold text-gray-800"><?php echo e($rev->currency_code); ?> <?php echo e(number_format($rev->grand_total, 2)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
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

        </div>

        
        <div class="xl:col-span-4 space-y-6">
            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Buyer Details']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Buyer Details']); ?>
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-gray-900 text-sm"><?php echo e($quotation->rfq?->buyerAccount?->buyerProfile?->organization_name ?? $quotation->rfq?->buyerAccount?->display_name); ?></p>
                    <p class="text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i><?php echo e($quotation->rfq?->buyerAccount?->buyerProfile?->country?->name ?? 'Location specified in RFQ'); ?></p>
                    <div class="pt-3 border-t border-gray-100">
                        <a href="<?php echo e(route('supplier.opportunities.show', $quotation->rfq)); ?>" class="text-indigo-600 font-semibold hover:underline">
                            View Original RFQ &rarr;
                        </a>
                    </div>
                </div>
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

            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'RFQ Version']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'RFQ Version']); ?>
                <p class="text-xs text-gray-500">This quotation responds to <span class="font-semibold text-gray-800">version <?php echo e($quotation->rfq_version_no); ?></span>.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($versionChanged): ?>
                    <p class="text-xs text-amber-700 mt-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i>The RFQ is now at version <?php echo e($quotation->rfq->current_version_no); ?>.</p>
                <?php else: ?>
                    <p class="text-xs text-emerald-700 mt-1"><i class="fa-solid fa-circle-check mr-1"></i>Up to date with the current RFQ version.</p>
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
        </div>

    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.supplier', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\procurement\quotations\show.blade.php ENDPATH**/ ?>