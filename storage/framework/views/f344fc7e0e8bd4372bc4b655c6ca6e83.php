<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('breadcrumb', 'Overview'); ?>

<?php $__env->startSection('body'); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($capabilityStatus !== 'active'): ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($capabilityStatus === 'pending'): ?>
            <div class="bg-white rounded-xl border border-amber-200 p-6 text-center max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-clock text-amber-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your Buyer Application is Under Review</h1>
                <p class="text-sm text-gray-500 mt-2">We'll notify you as soon as an administrator reviews your application. This usually takes 1–2 business days.</p>
            </div>
        <?php elseif($capabilityStatus === 'revision_required'): ?>
            <div class="bg-white rounded-xl border border-amber-200 p-6 max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Revision required</h1>
                <p class="text-sm text-gray-600 mt-2"><?php echo e($revisionReason ?? 'Please review and update your Buyer application.'); ?></p>
                <a href="<?php echo e(route('buyer.onboarding.profile')); ?>" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Update Application</a>
            </div>
        <?php elseif($capabilityStatus === 'rejected'): ?>
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Your application was not approved</h1>
                <p class="text-sm text-gray-600 mt-2"><?php echo e($rejectionReason ?? 'Please contact support for more information.'); ?></p>
            </div>
        <?php elseif($capabilityStatus === 'suspended'): ?>
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Your Buyer capability is suspended</h1>
                <p class="text-sm text-gray-600 mt-2"><?php echo e($suspensionReason ?? 'Please contact support for more information.'); ?></p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Complete your Buyer application</h1>
                <a href="<?php echo e(route('buyer.onboarding.profile')); ?>" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Continue Setup</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php else: ?>

        <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Welcome back, '.e($user->name).'','subtitle' => 'Here\'s what\'s happening with your procurement activity.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Welcome back, '.e($user->name).'','subtitle' => 'Here\'s what\'s happening with your procurement activity.']); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', \App\Models\Rfq::class)): ?>
                    <a href="<?php echo e(route('buyer.rfqs.create')); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Create RFQ
                    </a>
                <?php endif; ?>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Open RFQs','value' => $openRfqCount,'hint' => 'Currently accepting quotations','icon' => 'fa-file-signature','href' => route('buyer.rfqs.index', ['status' => 'open'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Open RFQs','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($openRfqCount),'hint' => 'Currently accepting quotations','icon' => 'fa-file-signature','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.rfqs.index', ['status' => 'open']))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Draft RFQs','value' => $draftRfqCount,'hint' => 'Not yet published','icon' => 'fa-file-pen','href' => route('buyer.rfqs.index', ['status' => 'draft'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Draft RFQs','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($draftRfqCount),'hint' => 'Not yet published','icon' => 'fa-file-pen','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.rfqs.index', ['status' => 'draft']))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Quotations to Review','value' => $quotationsAwaitingReview,'tone' => 'info','hint' => 'Awaiting your decision','icon' => 'fa-inbox','href' => route('buyer.quotations.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Quotations to Review','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quotationsAwaitingReview),'tone' => 'info','hint' => 'Awaiting your decision','icon' => 'fa-inbox','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.quotations.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Awards Awaiting Response','value' => $awardsAwaitingResponse,'tone' => 'warning','hint' => 'Waiting on supplier','icon' => 'fa-trophy','href' => route('buyer.awards.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Awards Awaiting Response','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($awardsAwaitingResponse),'tone' => 'warning','hint' => 'Waiting on supplier','icon' => 'fa-trophy','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.awards.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Shortlisted Quotes','value' => $shortlistedCount,'icon' => 'fa-star','href' => route('buyer.quotations.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Shortlisted Quotes','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($shortlistedCount),'icon' => 'fa-star','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.quotations.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Active Purchase Orders','value' => $activePoCount,'tone' => 'info','icon' => 'fa-clipboard-list','href' => route('buyer.purchase-orders.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Active Purchase Orders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activePoCount),'tone' => 'info','icon' => 'fa-clipboard-list','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.purchase-orders.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Completed Purchase Orders','value' => $completedPoCount,'tone' => 'success','icon' => 'fa-circle-check','href' => route('buyer.purchase-orders.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Completed Purchase Orders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($completedPoCount),'tone' => 'success','icon' => 'fa-circle-check','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.purchase-orders.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Open Support Tickets','value' => $openTicketsCount,'icon' => 'fa-life-ring','href' => route('buyer.tickets.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Open Support Tickets','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($openTicketsCount),'icon' => 'fa-life-ring','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('buyer.tickets.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $attributes = $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f)): ?>
<?php $component = $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f; ?>
<?php unset($__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f); ?>
<?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingQuestionsCount > 0 || $poAwaitingCompletion > 0 || $awardsAwaitingResponse > 0): ?>
                    <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Action Required']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Action Required']); ?>
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingQuestionsCount > 0): ?>
                                <li>
                                    <a href="<?php echo e(route('buyer.rfqs.index')); ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-circle-question text-amber-500 mr-2"></i><?php echo e($pendingQuestionsCount); ?> supplier question(s) waiting for your answer</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($awardsAwaitingResponse > 0): ?>
                                <li>
                                    <a href="<?php echo e(route('buyer.awards.index')); ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-trophy text-amber-500 mr-2"></i><?php echo e($awardsAwaitingResponse); ?> award(s) awaiting supplier response</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($poAwaitingCompletion > 0): ?>
                                <li>
                                    <a href="<?php echo e(route('buyer.purchase-orders.index', ['status' => 'delivered'])); ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-box-open text-amber-500 mr-2"></i><?php echo e($poAwaitingCompletion); ?> purchase order(s) delivered — confirm to complete</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'My RFQs','description' => 'Your most recently updated RFQs.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My RFQs','description' => 'Your most recently updated RFQs.']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentRfqs->isEmpty()): ?>
                        <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-file-signature','title' => 'No RFQs yet','description' => 'Create your first RFQ to start receiving supplier quotations.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-file-signature','title' => 'No RFQs yet','description' => 'Create your first RFQ to start receiving supplier quotations.']); ?>
                             <?php $__env->slot('actions', null, []); ?> 
                                <a href="<?php echo e(route('buyer.rfqs.create')); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Create RFQ</a>
                             <?php $__env->endSlot(); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $attributes = $__attributesOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $component = $__componentOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__componentOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentRfqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rfq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('buyer.rfqs.show', $rfq)); ?>" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($rfq->title); ?></p>
                                            <p class="text-xs text-gray-400"><?php echo e($rfq->rfq_number); ?> &middot; <?php echo e($rfq->created_at->format('d M Y')); ?></p>
                                        </div>
                                        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $rfq->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rfq->status)]); ?>
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
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
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

            <div class="space-y-6">
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Upcoming Deadlines']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Upcoming Deadlines']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($upcomingDeadlines->isEmpty()): ?>
                        <p class="text-sm text-gray-400">No quotation deadlines in the next 3 days.</p>
                    <?php else: ?>
                        <ul class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $upcomingDeadlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rfq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('buyer.rfqs.show', $rfq)); ?>" class="block">
                                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($rfq->title); ?></p>
                                        <p class="text-xs text-amber-600 mt-0.5"><i class="fa-regular fa-clock mr-1"></i><?php echo e($rfq->quotation_deadline->format('d M Y, h:i A')); ?></p>
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
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

                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Recent Quotations']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Quotations']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentQuotations->isEmpty()): ?>
                        <p class="text-sm text-gray-400">No quotations received yet.</p>
                    <?php else: ?>
                        <ul class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentQuotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('buyer.quotations.show', $quotation)); ?>" class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-gray-700 truncate"><?php echo e($quotation->supplierAccount?->supplierProfile?->display_name ?? $quotation->supplierAccount?->display_name); ?></span>
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
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
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

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.buyer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\buyer\dashboard\index.blade.php ENDPATH**/ ?>