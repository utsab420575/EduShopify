<?php $__env->startSection('title', 'Supplier Dashboard'); ?>
<?php $__env->startSection('breadcrumb', 'Overview'); ?>

<?php $__env->startSection('body'); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($capabilityStatus !== 'active'): ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($capabilityStatus === 'pending'): ?>
            <div class="bg-white rounded-xl border border-amber-200 p-6 text-center max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-clock text-amber-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your Supplier Application is Under Review</h1>
                <p class="text-sm text-gray-500 mt-2">We'll notify you as soon as an administrator reviews your application. This usually takes 1–2 business days.</p>
            </div>
        <?php elseif($capabilityStatus === 'revision_required'): ?>
            <div class="bg-white rounded-xl border border-amber-200 p-6 max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Revision Required</h1>
                <p class="text-sm text-gray-600 mt-2"><?php echo e($revisionReason ?? 'Please review and update your Supplier application.'); ?></p>
                <a href="<?php echo e(route('supplier.onboarding.revision')); ?>" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Update Application</a>
            </div>
        <?php elseif($capabilityStatus === 'rejected'): ?>
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-xmark text-red-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your application was not approved</h1>
                <p class="text-sm text-gray-600 mt-2"><?php echo e($rejectionReason ?? 'Please contact support for more information.'); ?></p>
            </div>
        <?php elseif($capabilityStatus === 'suspended'): ?>
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-ban text-red-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your Supplier capability is suspended</h1>
                <p class="text-sm text-gray-600 mt-2"><?php echo e($suspensionReason ?? 'Please contact support for more information.'); ?></p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Complete your Supplier application</h1>
                <a href="<?php echo e(route('supplier.onboarding.profile')); ?>" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Continue Setup</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php else: ?>

        <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Welcome back, '.e($user->name).'','subtitle' => 'Here\'s your supplier activity overview.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Welcome back, '.e($user->name).'','subtitle' => 'Here\'s your supplier activity overview.']); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <a href="<?php echo e(route('supplier.catalog.listings.create')); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Listing
                </a>
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

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscription && $subscription->status === 'past_due'): ?>
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <p class="text-sm text-red-700">Your subscription payment is past due. <a href="<?php echo e(route('supplier.subscription.current')); ?>" class="font-semibold underline">Renew now</a> to avoid service interruption.</p>
            </div>
        <?php elseif(!$subscription): ?>
            <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                <p class="text-sm text-amber-700">You don't have an active subscription. <a href="<?php echo e(route('supplier.pricing')); ?>" class="font-semibold underline">Choose a plan</a> to unlock all features.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Total Listings','value' => $totalListings,'icon' => 'fa-box-open','href' => route('supplier.catalog.listings.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Listings','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalListings),'icon' => 'fa-box-open','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.catalog.listings.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Published','value' => $publishedListings,'tone' => 'success','icon' => 'fa-circle-check','href' => route('supplier.catalog.listings.index', ['status' => 'approved'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Published','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($publishedListings),'tone' => 'success','icon' => 'fa-circle-check','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.catalog.listings.index', ['status' => 'approved']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Pending Approval','value' => $pendingListings,'tone' => 'warning','icon' => 'fa-hourglass-half','href' => route('supplier.catalog.listings.index', ['status' => 'pending'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pending Approval','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingListings),'tone' => 'warning','icon' => 'fa-hourglass-half','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.catalog.listings.index', ['status' => 'pending']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Available RFQs','value' => $availableOpportunities,'tone' => 'info','icon' => 'fa-magnifying-glass-chart','href' => route('supplier.opportunities.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Available RFQs','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($availableOpportunities),'tone' => 'info','icon' => 'fa-magnifying-glass-chart','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.opportunities.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Draft Quotations','value' => $draftQuotations,'icon' => 'fa-file-pen','href' => route('supplier.quotations.index', ['status' => 'draft'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Draft Quotations','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($draftQuotations),'icon' => 'fa-file-pen','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.quotations.index', ['status' => 'draft']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Submitted Quotations','value' => $submittedQuotations,'tone' => 'info','icon' => 'fa-paper-plane','href' => route('supplier.quotations.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Submitted Quotations','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($submittedQuotations),'tone' => 'info','icon' => 'fa-paper-plane','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.quotations.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Awards Awaiting Response','value' => $pendingAwards,'tone' => 'warning','icon' => 'fa-trophy','href' => route('supplier.awards.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Awards Awaiting Response','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pendingAwards),'tone' => 'warning','icon' => 'fa-trophy','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.awards.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Issued Purchase Orders','value' => $issuedPoCount,'tone' => 'info','icon' => 'fa-clipboard-list','href' => route('supplier.purchase-orders.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Issued Purchase Orders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($issuedPoCount),'tone' => 'info','icon' => 'fa-clipboard-list','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supplier.purchase-orders.index'))]); ?>
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

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingAwards > 0 || $revisionRequests > 0 || $rejectedListings > 0): ?>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingAwards > 0): ?>
                                <li>
                                    <a href="<?php echo e(route('supplier.awards.index')); ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-trophy text-amber-500 mr-2"></i><?php echo e($pendingAwards); ?> award(s) awaiting your response</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($revisionRequests > 0): ?>
                                <li>
                                    <a href="<?php echo e(route('supplier.quotations.index', ['status' => 'revision_requested'])); ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-rotate text-amber-500 mr-2"></i><?php echo e($revisionRequests); ?> quotation revision(s) requested by buyers</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rejectedListings > 0): ?>
                                <li>
                                    <a href="<?php echo e(route('supplier.catalog.listings.index', ['status' => 'rejected'])); ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-circle-xmark text-red-500 mr-2"></i><?php echo e($rejectedListings); ?> listing(s) rejected — please review and resubmit</span>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Recent Quotations','description' => 'Your most recently updated quotations.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Quotations','description' => 'Your most recently updated quotations.']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentQuotations->isEmpty()): ?>
                        <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-file-invoice','title' => 'No quotations yet','description' => 'Browse available RFQs and submit your first quotation.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-file-invoice','title' => 'No quotations yet','description' => 'Browse available RFQs and submit your first quotation.']); ?>
                             <?php $__env->slot('actions', null, []); ?> 
                                <a href="<?php echo e(route('supplier.opportunities.index')); ?>" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Browse RFQs</a>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentQuotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('supplier.quotations.show', $quotation)); ?>" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($quotation->rfq?->title ?? $quotation->quotation_number); ?></p>
                                            <p class="text-xs text-gray-400"><?php echo e($quotation->quotation_number); ?> &middot; <?php echo e($quotation->submitted_at?->format('d M Y') ?? $quotation->created_at->format('d M Y')); ?></p>
                                        </div>
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

                
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Recent Purchase Orders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Purchase Orders']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentPurchaseOrders->isEmpty()): ?>
                        <p class="text-sm text-gray-400">No purchase orders yet.</p>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentPurchaseOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('supplier.purchase-orders.show', $po)); ?>" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($po->po_number); ?></p>
                                            <p class="text-xs text-gray-400"><?php echo e($po->rfq?->title); ?> &middot; <?php echo e($po->issued_at?->format('d M Y')); ?></p>
                                        </div>
                                        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $po->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($po->status)]); ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quotationDeadlines->isEmpty() && $awardDeadlines->isEmpty()): ?>
                        <p class="text-sm text-gray-400">No upcoming deadlines in the next 7 days.</p>
                    <?php else: ?>
                        <ul class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quotationDeadlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($entry->rfq): ?>
                                    <li>
                                        <a href="<?php echo e(route('supplier.opportunities.show', $entry->rfq)); ?>" class="block">
                                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($entry->rfq->title); ?></p>
                                            <p class="text-xs text-amber-600 mt-0.5"><i class="fa-regular fa-clock mr-1"></i>Quotation due <?php echo e($entry->rfq->quotation_deadline->format('d M Y, h:i A')); ?></p>
                                        </a>
                                    </li>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $awardDeadlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('supplier.awards.show', $award)); ?>" class="block">
                                        <p class="text-sm font-medium text-gray-900 truncate">Award <?php echo e($award->award_number); ?></p>
                                        <p class="text-xs text-red-600 mt-0.5"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Response due <?php echo e($award->response_deadline->format('d M Y, h:i A')); ?></p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Recent Awards']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Awards']); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentAwards->isEmpty()): ?>
                        <p class="text-sm text-gray-400">No awards yet.</p>
                    <?php else: ?>
                        <ul class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentAwards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('supplier.awards.show', $award)); ?>" class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-gray-700 truncate"><?php echo e($award->rfq?->title ?? $award->award_number); ?></span>
                                        <?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $award->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($award->status)]); ?>
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

                
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Quick Stats']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quick Stats']); ?>
                    <ul class="space-y-2">
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fa-solid fa-star text-amber-400 mr-2"></i>Reviews Received</span>
                            <span class="font-semibold text-gray-900"><?php echo e($reviewCount); ?></span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fa-solid fa-circle-check text-green-500 mr-2"></i>Completed POs</span>
                            <span class="font-semibold text-gray-900"><?php echo e($completedPoCount); ?></span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fa-solid fa-life-ring text-gray-400 mr-2"></i>Open Tickets</span>
                            <span class="font-semibold text-gray-900"><?php echo e($openTicketsCount); ?></span>
                        </li>
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

            </div>

        </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.supplier', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\dashboard\index.blade.php ENDPATH**/ ?>