<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('breadcrumb', 'Overview'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Platform Overview','subtitle' => ''.e(now()->format('l, d M Y')).' — a snapshot of everything that needs your attention.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Platform Overview','subtitle' => ''.e(now()->format('l, d M Y')).' — a snapshot of everything that needs your attention.']); ?>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($userMetrics)): ?>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Users &amp; Accounts</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Total Users','value' => $userMetrics['total'],'icon' => 'fa-users','href' => route('admin.users.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Users','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userMetrics['total']),'icon' => 'fa-users','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.users.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Active Users','value' => $userMetrics['active'],'tone' => 'success','icon' => 'fa-user-check','href' => route('admin.users.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Active Users','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userMetrics['active']),'tone' => 'success','icon' => 'fa-user-check','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.users.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Total Accounts','value' => $accountMetrics['total'],'icon' => 'fa-building','href' => route('admin.accounts.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Accounts','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accountMetrics['total']),'icon' => 'fa-building','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.accounts.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Pending Approval','value' => $accountMetrics['pending_approval'],'tone' => 'warning','icon' => 'fa-hourglass-half','href' => route('admin.accounts.index', ['status' => 'pending_approval'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pending Approval','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accountMetrics['pending_approval']),'tone' => 'warning','icon' => 'fa-hourglass-half','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.accounts.index', ['status' => 'pending_approval']))]); ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($capabilityMetrics)): ?>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Capability Applications</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php if (isset($component)) { $__componentOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b9feb6be9ec2c1d7f2f1786ee94023f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Buyer Applications Pending','value' => $capabilityMetrics['buyer_pending'],'tone' => 'warning','icon' => 'fa-cart-shopping','href' => route('admin.capabilities.index', ['type' => 'buyer', 'status' => 'pending'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Buyer Applications Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($capabilityMetrics['buyer_pending']),'tone' => 'warning','icon' => 'fa-cart-shopping','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.capabilities.index', ['type' => 'buyer', 'status' => 'pending']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Active Buyers','value' => $capabilityMetrics['buyer_active'],'tone' => 'success','icon' => 'fa-cart-shopping','href' => route('admin.buyers.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Active Buyers','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($capabilityMetrics['buyer_active']),'tone' => 'success','icon' => 'fa-cart-shopping','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.buyers.index'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Supplier Applications Pending','value' => $capabilityMetrics['supplier_pending'],'tone' => 'warning','icon' => 'fa-store','href' => route('admin.capabilities.index', ['type' => 'supplier', 'status' => 'pending'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Supplier Applications Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($capabilityMetrics['supplier_pending']),'tone' => 'warning','icon' => 'fa-store','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.capabilities.index', ['type' => 'supplier', 'status' => 'pending']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.stat-card','data' => ['label' => 'Active Suppliers','value' => $capabilityMetrics['supplier_active'],'tone' => 'success','icon' => 'fa-store','href' => route('admin.suppliers.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Active Suppliers','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($capabilityMetrics['supplier_active']),'tone' => 'success','icon' => 'fa-store','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.suppliers.index'))]); ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <?php
                $actionItems = collect();
                if (isset($capabilityMetrics)) {
                    if ($capabilityMetrics['buyer_pending'] > 0) $actionItems->push(['icon' => 'fa-cart-shopping', 'label' => $capabilityMetrics['buyer_pending'].' Buyer application(s) require review', 'url' => route('admin.capabilities.index', ['type' => 'buyer', 'status' => 'pending'])]);
                    if ($capabilityMetrics['supplier_pending'] > 0) $actionItems->push(['icon' => 'fa-store', 'label' => $capabilityMetrics['supplier_pending'].' Supplier application(s) require review', 'url' => route('admin.capabilities.index', ['type' => 'supplier', 'status' => 'pending'])]);
                }
                if (isset($documentMetrics) && $documentMetrics['pending'] > 0) $actionItems->push(['icon' => 'fa-file-lines', 'label' => $documentMetrics['pending'].' Supplier document(s) await verification', 'url' => route('admin.suppliers.index', ['document_status' => 'pending'])]);
                if (isset($catalogMetrics)) {
                    if ($catalogMetrics['listings_pending'] > 0) $actionItems->push(['icon' => 'fa-box', 'label' => $catalogMetrics['listings_pending'].' Listing(s) await approval', 'url' => route('admin.catalog.listings.index', ['status' => 'pending'])]);
                    if ($catalogMetrics['category_suggestions'] > 0) $actionItems->push(['icon' => 'fa-layer-group', 'label' => $catalogMetrics['category_suggestions'].' Category suggestion(s) pending', 'url' => route('admin.catalog.categories.index', ['tab' => 'suggestions'])]);
                }
                if (($roleRequestsPending ?? 0) > 0) $actionItems->push(['icon' => 'fa-shield-halved', 'label' => $roleRequestsPending.' Custom role request(s) await review', 'url' => route('admin.access-control.role-requests.index')]);
                if (($conversionsPending ?? 0) > 0) $actionItems->push(['icon' => 'fa-right-left', 'label' => $conversionsPending.' Account conversion request(s) await review', 'url' => route('admin.conversions.index')]);
                if (isset($supportMetrics)) {
                    if ($supportMetrics['reports_pending'] > 0) $actionItems->push(['icon' => 'fa-flag', 'label' => $supportMetrics['reports_pending'].' Review report(s) await moderation', 'url' => route('admin.reviews.reports.index')]);
                    if ($supportMetrics['unassigned_tickets'] > 0) $actionItems->push(['icon' => 'fa-life-ring', 'label' => $supportMetrics['unassigned_tickets'].' Unassigned support ticket(s)', 'url' => route('admin.tickets.index', ['assigned' => 'unassigned'])]);
                }
                if (isset($billingMetrics) && $billingMetrics['failed'] > 0) $actionItems->push(['icon' => 'fa-triangle-exclamation', 'label' => $billingMetrics['failed'].' Failed subscription payment(s)', 'url' => route('admin.billing.payments.index', ['status' => 'failed'])]);
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($actionItems->isNotEmpty()): ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $actionItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <a href="<?php echo e($item['url']); ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                    <span class="text-sm text-gray-700"><i class="fa-solid <?php echo e($item['icon']); ?> text-amber-500 mr-2"></i><?php echo e($item['label']); ?></span>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                </a>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($procurementMetrics)): ?>
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Procurement Activity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Procurement Activity']); ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div><p class="text-gray-500">Open RFQs</p><p class="text-lg font-bold text-gray-900"><?php echo e($procurementMetrics['open_rfqs']); ?></p></div>
                        <div><p class="text-gray-500">Quotations Submitted</p><p class="text-lg font-bold text-gray-900"><?php echo e($procurementMetrics['quotations_submitted']); ?></p></div>
                        <div><p class="text-gray-500">Awards Awaiting Response</p><p class="text-lg font-bold text-gray-900"><?php echo e($procurementMetrics['awards_awaiting_response']); ?></p></div>
                        <div><p class="text-gray-500">Purchase Orders Issued</p><p class="text-lg font-bold text-gray-900"><?php echo e($procurementMetrics['po_issued']); ?></p></div>
                        <div><p class="text-gray-500">Purchase Orders Completed</p><p class="text-lg font-bold text-gray-900"><?php echo e($procurementMetrics['po_completed']); ?></p></div>
                        <div><p class="text-gray-500">Cancelled / Disputed POs</p><p class="text-lg font-bold text-gray-900"><?php echo e($procurementMetrics['po_cancelled_disputed']); ?></p></div>
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

        <div class="space-y-6">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($documentMetrics)): ?>
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Supplier Verification']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Supplier Verification']); ?>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Pending Documents</dt><dd class="font-medium text-gray-900"><?php echo e($documentMetrics['pending']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Rejected (awaiting replacement)</dt><dd class="font-medium text-gray-900"><?php echo e($documentMetrics['rejected']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Expiring within 30 days</dt><dd class="font-medium text-gray-900"><?php echo e($documentMetrics['expiring_soon']); ?></dd></div>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($billingMetrics)): ?>
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Subscription Health']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Subscription Health']); ?>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Active</dt><dd class="font-medium text-green-700"><?php echo e($billingMetrics['active']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Trialing</dt><dd class="font-medium text-gray-900"><?php echo e($billingMetrics['trialing']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Past Due</dt><dd class="font-medium text-amber-700"><?php echo e($billingMetrics['past_due']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Expired</dt><dd class="font-medium text-gray-500"><?php echo e($billingMetrics['expired']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Failed Payments</dt><dd class="font-medium text-red-600"><?php echo e($billingMetrics['failed']); ?></dd></div>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($recentActivity ?? collect())->isNotEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Recent Activity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recent Activity']); ?>
                    <ul class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="text-sm">
                                <p class="text-gray-700"><?php echo e($activity->description); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($activity->causer?->name ?? 'System'); ?> &middot; <?php echo e($activity->created_at->diffForHumans()); ?></p>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\dashboard\index.blade.php ENDPATH**/ ?>