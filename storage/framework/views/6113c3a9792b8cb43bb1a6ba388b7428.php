<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
<?php $__env->stopPush(); ?>

<div x-data="{ categoryId: '<?php echo e(old('category_id', $post->category_id)); ?>' }">

    <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Post Details']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Post Details']); ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'title','label' => 'Title','required' => true,'value' => old('title', $post->title),'placeholder' => 'e.g. 5 Tips for Streamlining School Procurement']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'title','label' => 'Title','required' => true,'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('title', $post->title)),'placeholder' => 'e.g. 5 Tips for Streamlining School Procurement']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <div class="flex items-center gap-2">
                    <select name="category_id" x-model="categoryId" id="category-select" class="focus-accent flex-1 text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                        <option value="">Uncategorized</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <button type="button" @click="$dispatch('open-modal-quick-add-category')" title="Add new category"
                            class="w-10 h-10 shrink-0 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 flex items-center justify-center">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select name="status" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                    <option value="draft" <?php if(old('status', $post->status ?: 'draft') === 'draft'): echo 'selected'; endif; ?>>Draft</option>
                    <option value="approved" <?php if(old('status', $post->status) === 'approved'): echo 'selected'; endif; ?>>Published</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'excerpt','label' => 'Excerpt','value' => old('excerpt', $post->excerpt),'placeholder' => 'A short summary shown on listing cards (optional)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'excerpt','label' => 'Excerpt','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('excerpt', $post->excerpt)),'placeholder' => 'A short summary shown on listing cards (optional)']); ?>
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

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="featured" value="1" <?php if(old('featured', $post->featured)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                    Feature this post
                </label>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Cover Image']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Cover Image']); ?>
        <div class="flex items-center gap-4">
            <img id="cover-preview"
                 src="<?php echo e($post->cover_image ? $post->coverImageUrl() : ''); ?>"
                 class="w-28 h-20 rounded-lg object-cover border border-gray-200 bg-gray-50" style="<?php echo e($post->cover_image ? '' : 'display:none'); ?>" alt="">
            <div class="flex-1">
                <input type="file" name="cover_image" accept="image/*" onchange="previewCoverImage(this)"
                       class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">JPG, PNG or WEBP, up to 10MB. Shown on listing cards and at the top of the post.</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cover_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Content']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Content']); ?>
        <textarea name="content" id="blog-content-editor"><?php echo e(old('content', $post->content)); ?></textarea>
        <input type="file" id="blog-image-file-input" accept="image/*" multiple class="hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Tags']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Tags']); ?>
        <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'tags','label' => 'Tags','value' => old('tags', $tagNames),'placeholder' => 'e.g. Procurement, EdTech, STEM (comma separated)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'tags','label' => 'Tags','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('tags', $tagNames)),'placeholder' => 'e.g. Procurement, EdTech, STEM (comma separated)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
        <p class="text-xs text-gray-400 mt-1.5">Separate multiple tags with a comma. New tags are created automatically.</p>
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

    <div x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium mb-3" style="color:var(--theme-primary)">
            <i class="fa-solid fa-chevron-right text-xs transition-transform" :class="open && 'rotate-90'"></i> SEO (optional)
        </button>
        <div x-show="open" x-transition x-cloak>
            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Search Engine Optimization']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Search Engine Optimization']); ?>
                <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'meta_title','label' => 'Meta Title','value' => old('meta_title', $post->meta_title)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'meta_title','label' => 'Meta Title','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('meta_title', $post->meta_title))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'meta_description','label' => 'Meta Description','value' => old('meta_description', $post->meta_description)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'meta_description','label' => 'Meta Description','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('meta_description', $post->meta_description))]); ?>
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
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'meta_keywords','label' => 'Meta Keywords','value' => old('meta_keywords', $post->meta_keywords),'placeholder' => 'Comma separated keywords']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'meta_keywords','label' => 'Meta Keywords','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('meta_keywords', $post->meta_keywords)),'placeholder' => 'Comma separated keywords']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $attributes = $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc)): ?>
<?php $component = $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc; ?>
<?php unset($__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc); ?>
<?php endif; ?>
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
        </div>
    </div>
</div>

<?php if (isset($component)) { $__componentOriginal5845bcee7aa8bdff54827061cf154d18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5845bcee7aa8bdff54827061cf154d18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.modal','data' => ['id' => 'quick-add-category','title' => 'New Blog Category','width' => 'max-w-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'quick-add-category','title' => 'New Blog Category','width' => 'max-w-sm']); ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category Name</label>
        <input type="text" id="quick-add-category-name" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5" placeholder="e.g. Sustainability">
        <p id="quick-add-category-error" class="text-xs text-red-600 mt-1 hidden"></p>
    </div>
    <div class="flex justify-end gap-2 mt-4">
        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="button" onclick="quickAddBlogCategory()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Add Category</button>
    </div>
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

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script>
        $(function () {
            // Summernote's own built-in "Insert Image" dialog/backdrop was
            // freezing the whole page for some users, with no way to
            // interact with anything underneath it. This custom toolbar
            // button replaces it entirely — it never opens Summernote's
            // dialog at all, just triggers the browser's native file
            // picker directly via the hidden <input> below, uploading
            // through the same already-verified endpoint.
            $.extend($.summernote.plugins, {
                blogImageButton: function (context) {
                    var ui = $.summernote.ui;
                    context.memo('button.blogImage', function () {
                        var button = ui.button({
                            contents: '<i class="note-icon-picture"></i>',
                            tooltip: 'Insert Image',
                            click: function () {
                                document.getElementById('blog-image-file-input').click();
                            },
                        });
                        return button.render();
                    });
                },
            });

            $('#blog-content-editor').summernote({
                height: 420,
                placeholder: 'Write the blog post content here...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'blogImage', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']],
                ],
                // Kept so a pasted or drag-and-dropped image still uploads
                // as a real file instead of Summernote's default fallback
                // of embedding it as base64 directly in the content HTML.
                // Neither path touches the dialog this replaces above.
                callbacks: {
                    onImageUpload: function (files) {
                        for (let i = 0; i < files.length; i++) {
                            uploadBlogContentImage(files[i]);
                        }
                    },
                },
            });

            document.getElementById('blog-image-file-input').addEventListener('change', function () {
                Array.from(this.files || []).forEach(uploadBlogContentImage);
                this.value = ''; // allow choosing the same file again next time
            });
        });

        function previewCoverImage(input) {
            const file = input.files && input.files[0];
            if (!file) return;

            try {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('cover-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } catch (e) {
                // Preview is a nice-to-have — if it fails for any reason
                // (unsupported API, browser extension interference, etc.)
                // the file is still attached and uploads normally on submit.
                console.warn('Cover image preview failed', e);
            }
        }

        function uploadBlogContentImage(file) {
            const data = new FormData();
            data.append('file', file);
            data.append('_token', '<?php echo e(csrf_token()); ?>');

            fetch('<?php echo e(route('admin.blog.posts.upload-image')); ?>', {
                method: 'POST',
                body: data,
                headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' },
            })
                .then(async (r) => {
                    let body = null;
                    try { body = await r.json(); } catch (e) { /* non-JSON response, e.g. an HTML error page */ }

                    if (!r.ok) {
                        console.error('Blog image upload failed', r.status, body);

                        if (r.status === 419) {
                            throw new Error('Your session has expired. Please refresh the page and try again.');
                        }
                        if (r.status === 422 && body?.errors?.file) {
                            throw new Error(body.errors.file[0]);
                        }
                        throw new Error(body?.message || ('Upload failed (HTTP ' + r.status + ').'));
                    }

                    return body;
                })
                .then((res) => $('#blog-content-editor').summernote('insertImage', res.url))
                .catch((e) => Swal.fire('Image upload failed', e.message, 'error'));
        }

        function quickAddBlogCategory() {
            const input = document.getElementById('quick-add-category-name');
            const errorEl = document.getElementById('quick-add-category-error');
            errorEl.classList.add('hidden');

            fetch('<?php echo e(route('admin.blog.categories.quick-add')); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' },
                body: JSON.stringify({ name: input.value }),
            })
                .then(async (r) => {
                    const body = await r.json();
                    if (!r.ok) throw new Error(body.errors?.name?.[0] || 'Could not add category.');
                    return body;
                })
                .then((category) => {
                    const select = document.getElementById('category-select');
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    option.selected = true;
                    select.appendChild(option);
                    select.dispatchEvent(new Event('change'));
                    input.value = '';
                    window.dispatchEvent(new CustomEvent('close-modal-quick-add-category'));
                })
                .catch((e) => {
                    errorEl.textContent = e.message;
                    errorEl.classList.remove('hidden');
                });
        }
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\blog\posts\_form.blade.php ENDPATH**/ ?>