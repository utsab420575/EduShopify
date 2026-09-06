<?php $__env->startSection('title', 'Blog Approval'); ?>
<?php $__env->startSection('breadcrumb', 'Blog / Blog Approval'); ?>

<?php $__env->startSection('body'); ?>

    <?php if (isset($component)) { $__componentOriginal6ccefb989a1afce853acb3cdbc40307e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccefb989a1afce853acb3cdbc40307e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.page-header','data' => ['title' => 'Blog Approval','subtitle' => 'Review blog posts submitted for publication.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Blog Approval','subtitle' => 'Review blog posts submitted for publication.']); ?>
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

    <?php if (isset($component)) { $__componentOriginalb5402a608f4ff404f07800485e35a084 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5402a608f4ff404f07800485e35a084 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.tabs','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <?php if (isset($component)) { $__componentOriginalfda544832f91f39c42466108e87ef294 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfda544832f91f39c42466108e87ef294 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.tab','data' => ['href' => route('admin.blog.approval.index', array_filter(['search' => $search])),'active' => $status === '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.blog.approval.index', array_filter(['search' => $search]))),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status === '')]); ?>
            All <span class="ml-1 text-xs text-gray-400">(<?php echo e($counts['all']); ?>)</span>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $attributes = $__attributesOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__attributesOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $component = $__componentOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__componentOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginalfda544832f91f39c42466108e87ef294 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfda544832f91f39c42466108e87ef294 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.tab','data' => ['href' => route('admin.blog.approval.index', array_filter(['status' => $value, 'search' => $search])),'active' => $status === $value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.tab'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.blog.approval.index', array_filter(['status' => $value, 'search' => $search]))),'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status === $value)]); ?>
                <?php echo e($label); ?> <span class="ml-1 text-xs text-gray-400">(<?php echo e($counts[$value]); ?>)</span>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $attributes = $__attributesOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__attributesOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfda544832f91f39c42466108e87ef294)): ?>
<?php $component = $__componentOriginalfda544832f91f39c42466108e87ef294; ?>
<?php unset($__componentOriginalfda544832f91f39c42466108e87ef294); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb5402a608f4ff404f07800485e35a084)): ?>
<?php $attributes = $__attributesOriginalb5402a608f4ff404f07800485e35a084; ?>
<?php unset($__attributesOriginalb5402a608f4ff404f07800485e35a084); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb5402a608f4ff404f07800485e35a084)): ?>
<?php $component = $__componentOriginalb5402a608f4ff404f07800485e35a084; ?>
<?php unset($__componentOriginalb5402a608f4ff404f07800485e35a084); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal72603426510669aaf343a2492f1d2357 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72603426510669aaf343a2492f1d2357 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
         <?php $__env->slot('toolbar', null, []); ?> 
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <input type="hidden" name="status" value="<?php echo e($status); ?>">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search posts..." class="focus-accent w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300">
                </div>
                <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Search</button>
            </form>
         <?php $__env->endSlot(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($posts->isEmpty()): ?>
             <?php $__env->slot('empty', null, []); ?> <?php if (isset($component)) { $__componentOriginal899b2e3433d50ad8f550a578a5de8708 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal899b2e3433d50ad8f550a578a5de8708 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.empty-state','data' => ['icon' => 'fa-newspaper','title' => 'No blog posts found']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'fa-newspaper','title' => 'No blog posts found']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $attributes = $__attributesOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__attributesOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal899b2e3433d50ad8f550a578a5de8708)): ?>
<?php $component = $__componentOriginal899b2e3433d50ad8f550a578a5de8708; ?>
<?php unset($__componentOriginal899b2e3433d50ad8f550a578a5de8708); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
        <?php else: ?>
             <?php $__env->slot('head', null, []); ?> 
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Post</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Author</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reviewed By</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-900 max-w-xs truncate"><?php echo e($post->title); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($post->account?->display_name ?? $post->authorUser?->name ?? '—'); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($post->created_at->format('d M Y')); ?></td>
                    <td class="px-5 py-3.5 text-sm text-gray-600"><?php echo e($post->approvedBy?->name ?? '—'); ?></td>
                    <td class="px-5 py-3.5"><?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $post->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $attributes = $__attributesOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__attributesOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $component = $__componentOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__componentOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?></td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button"
                                    @click="$dispatch('open-modal-view-blog-post-<?php echo e($post->id); ?>')"
                                    title="View"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($post->status === 'pending'): ?>
                                <form method="POST" action="<?php echo e(route('admin.blog.approval.approve', $post)); ?>" onsubmit="return confirmSwal(this, 'Approve Post?', 'Publish <?php echo e(addslashes($post->title)); ?>?', 'question', 'Yes, Approve')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" title="Approve" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-emerald-50 transition-colors">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <button type="button"
                                        title="Reject"
                                        @click="$dispatch('open-modal-reject-blog-post-<?php echo e($post->id); ?>')"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            <?php else: ?>
                                <form method="POST" action="<?php echo e(route('admin.blog.approval.undo', $post)); ?>" onsubmit="return confirmSwal(this, 'Undo Decision?', 'Revert <?php echo e(addslashes($post->title)); ?> back to Pending?', 'question', 'Yes, Undo')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" title="Undo Decision (Revert to Pending)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition-colors">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

         <?php $__env->slot('pagination', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal3a8b7815bf55366b2036b8aef8f795af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.pagination','data' => ['paginator' => $posts]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($posts)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3a8b7815bf55366b2036b8aef8f795af)): ?>
<?php $attributes = $__attributesOriginal3a8b7815bf55366b2036b8aef8f795af; ?>
<?php unset($__attributesOriginal3a8b7815bf55366b2036b8aef8f795af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3a8b7815bf55366b2036b8aef8f795af)): ?>
<?php $component = $__componentOriginal3a8b7815bf55366b2036b8aef8f795af; ?>
<?php unset($__componentOriginal3a8b7815bf55366b2036b8aef8f795af); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72603426510669aaf343a2492f1d2357)): ?>
<?php $attributes = $__attributesOriginal72603426510669aaf343a2492f1d2357; ?>
<?php unset($__attributesOriginal72603426510669aaf343a2492f1d2357); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72603426510669aaf343a2492f1d2357)): ?>
<?php $component = $__componentOriginal72603426510669aaf343a2492f1d2357; ?>
<?php unset($__componentOriginal72603426510669aaf343a2492f1d2357); ?>
<?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'view-blog-post-'.$post->id,'title' => $post->title,'width' => 'max-w-2xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('view-blog-post-'.$post->id),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post->title),'width' => 'max-w-2xl']); ?>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-gray-500">Author Account</dt><dd class="font-medium text-gray-900"><?php echo e($post->account?->display_name ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-gray-500">Category</dt><dd class="font-medium text-gray-900"><?php echo e($post->category?->name ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-gray-500">Status</dt><dd><?php if (isset($component)) { $__componentOriginal1790892cf8031768f200c26522db45cd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1790892cf8031768f200c26522db45cd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.status-badge','data' => ['status' => $post->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($post->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $attributes = $__attributesOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__attributesOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1790892cf8031768f200c26522db45cd)): ?>
<?php $component = $__componentOriginal1790892cf8031768f200c26522db45cd; ?>
<?php unset($__componentOriginal1790892cf8031768f200c26522db45cd); ?>
<?php endif; ?></dd></div>
                <div><dt class="text-xs text-gray-500">Submitted</dt><dd class="font-medium text-gray-900"><?php echo e($post->created_at->format('d M Y, h:i A')); ?></dd></div>
                <div><dt class="text-xs text-gray-500">Reviewed By</dt><dd class="font-medium text-gray-900"><?php echo e($post->approvedBy?->name ?? '—'); ?></dd></div>
                <div><dt class="text-xs text-gray-500">Reading Time</dt><dd class="font-medium text-gray-900"><?php echo e($post->reading_time_minutes); ?> min</dd></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($post->cover_image): ?>
                    <div class="sm:col-span-2">
                        <img src="<?php echo e($post->coverImageUrl()); ?>" class="w-full h-48 object-cover rounded-lg border border-gray-200">
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($post->excerpt): ?>
                    <div class="sm:col-span-2"><dt class="text-xs text-gray-500">Excerpt</dt><dd class="text-xs text-gray-700 mt-0.5"><?php echo e($post->excerpt); ?></dd></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-500 mb-1">Content</dt>
                    <dd class="text-xs text-gray-700 prose prose-sm max-w-none max-h-64 overflow-y-auto border border-gray-100 rounded-lg p-3 bg-gray-50/50"><?php echo $post->content; ?></dd>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($post->status === 'rejected' && $post->rejection_reason): ?>
                    <div class="sm:col-span-2"><dt class="text-xs text-red-500 font-semibold">Rejection Reason</dt><dd class="text-xs text-gray-700 mt-0.5"><?php echo e($post->rejection_reason); ?></dd></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </dl>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($post->status === 'pending'): ?>
            <?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'reject-blog-post-'.$post->id,'title' => 'Reject Blog Post']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('reject-blog-post-'.$post->id),'title' => 'Reject Blog Post']); ?>
                <form method="POST" action="<?php echo e(route('admin.blog.approval.reject', $post)); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'reason','label' => 'Reason for Rejection','placeholder' => 'State why this post is being rejected...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'reason','label' => 'Reason for Rejection','placeholder' => 'State why this post is being rejected...','required' => true]); ?>
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
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Reject Post</button>
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
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('backend.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\edushopify\resources\views/backend/admin/blog/approval/index.blade.php ENDPATH**/ ?>