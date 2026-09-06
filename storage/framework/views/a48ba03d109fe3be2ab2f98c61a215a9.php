<div x-data="{
    iconType: '<?php echo e(old('icon_type', $icon->icon_type ?: 'fontawesome')); ?>',
    iconValue: '<?php echo e(addslashes(old('icon_value', $icon->icon_value ?: ''))); ?>'
}">
    <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Icon Details']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Icon Details']); ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="library_id" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Icon Library <span class="text-red-500">*</span>
                </label>
                <select name="library_id" id="library_id" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="">Select a library</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $libraries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lib): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($lib->id); ?>" <?php if(old('library_id', $icon->library_id) == $lib->id): echo 'selected'; endif; ?>>
                            <?php echo e($lib->name); ?> (<?php echo e($lib->type); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['library_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'name','label' => 'Icon Display Name','required' => true,'value' => $icon->name,'placeholder' => 'e.g. Fast Delivery, Quality Guarantee']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => 'Icon Display Name','required' => true,'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon->name),'placeholder' => 'e.g. Fast Delivery, Quality Guarantee']); ?>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="icon_type" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Icon Type <span class="text-red-500">*</span>
                </label>
                <select name="icon_type" id="icon_type" x-model="iconType" required class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white">
                    <option value="fontawesome">FontAwesome (CSS Class)</option>
                    <option value="svg">Raw SVG Markup</option>
                    <option value="image_url">Image / Icon URL</option>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['icon_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Live Preview
                </label>
                <div class="h-10 w-16 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden">
                    <template x-if="iconType === 'fontawesome' && iconValue">
                        <i :class="iconValue" class="text-xl text-indigo-600"></i>
                    </template>
                    <template x-if="iconType === 'svg' && iconValue">
                        <div class="w-6 h-6 flex items-center justify-center [&>svg]:w-full [&>svg]:h-full text-indigo-600" x-html="iconValue"></div>
                    </template>
                    <template x-if="iconType === 'image_url' && iconValue">
                        <img :src="iconValue" alt="Preview" class="w-6 h-6 object-contain">
                    </template>
                    <template x-if="!iconValue">
                        <span class="text-xs text-gray-400">None</span>
                    </template>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label for="icon_value" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                Icon Value / Content <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 mb-1.5" x-show="iconType === 'fontawesome'">
                Enter the FontAwesome or font classes, e.g. <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">fa-solid fa-truck-fast</code> or <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">bi bi-shield-check</code>
            </p>
            <p class="text-xs text-gray-500 mb-1.5" x-show="iconType === 'svg'">
                Paste the raw <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">&lt;svg ...&gt;...&lt;/svg&gt;</code> markup.
            </p>
            <p class="text-xs text-gray-500 mb-1.5" x-show="iconType === 'image_url'">
                Enter full image URL or CDN link (e.g. <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">https://.../icon.png</code>).
            </p>
            <textarea name="icon_value" id="icon_value" rows="3" required x-model="iconValue"
                      class="focus-accent w-full text-sm rounded-lg border border-gray-300 p-3 font-mono text-xs"
                      placeholder="e.g. fa-solid fa-truck-fast"><?php echo e(old('icon_value', $icon->icon_value)); ?></textarea>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['icon_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="flex items-center gap-2 mt-5 pt-3 border-t border-gray-100">
            <input type="checkbox" name="is_active" id="is_active" value="1" <?php if(old('is_active', $icon->exists ? $icon->is_active : true)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)" class="rounded border-gray-300 w-4 h-4">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active (Available in service icon pickers across the platform)</label>
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
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\icons\_form.blade.php ENDPATH**/ ?>