<?php
    $inputTypes = $inputTypes ?? \App\Models\InputType::active()->ordered()->get();
    $selectedInputType = old('input_type', $attribute->input_type ?: 'text');
    $selectedInputTypeId = old('input_type_id', $attribute->input_type_id ?: ($inputTypes->where('code', $selectedInputType)->first()?->id ?? 1));

    $existingValues = old('values');
    if ($existingValues === null && $attribute->exists) {
        $existingValues = $attribute->values->map(fn($v) => [
            'id' => $v->id,
            'value' => $v->value,
            'slug' => $v->slug,
            'color_hex' => $v->color_hex,
            'sort_order' => $v->sort_order,
            'is_active' => $v->is_active,
        ])->toArray();
    }
    $existingValues = $existingValues ?: [];
    $validationRules = (array) ($attribute->validation_rules ?? []);
?>

<div x-data="{
    inputTypes: <?php echo e(Js::from($inputTypes->map(fn($t) => ['id' => $t->id, 'code' => $t->code, 'name' => $t->name, 'has_options' => (bool)$t->has_options, 'is_multiple' => (bool)$t->is_multiple]))); ?>,
    inputTypeId: <?php echo e($selectedInputTypeId); ?>,
    get currentInputType() {
        return this.inputTypes.find(t => t.id == this.inputTypeId) || this.inputTypes[0] || {};
    },
    get inputType() {
        return this.currentInputType.code || 'text';
    },
    get hasOptions() {
        return !!this.currentInputType.has_options;
    },
    values: <?php echo e(Js::from($existingValues)); ?>,
    addValue() {
        this.values.push({
            id: null,
            value: '',
            slug: '',
            color_hex: '#000000',
            sort_order: this.values.length,
            is_active: true
        });
    },
    removeValue(index) {
        this.values.splice(index, 1);
    },
    autoSlug(index) {
        if (!this.values[index].slug || this.values[index].slug === '') {
            this.values[index].slug = this.values[index].value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        }
    }
}" class="space-y-6">

    
    <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Attribute Details','description' => 'Define the specification name, grouping, input format, and measurement unit.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Attribute Details','description' => 'Define the specification name, grouping, input format, and measurement unit.']); ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'name','label' => 'Attribute Name','required' => true,'value' => old('name', $attribute->name),'placeholder' => 'e.g. Bluetooth Version, Amplifier Power, Battery Capacity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => 'Attribute Name','required' => true,'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('name', $attribute->name)),'placeholder' => 'e.g. Bluetooth Version, Amplifier Power, Battery Capacity']); ?>
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
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'slug','label' => 'Slug (optional)','value' => old('slug', $attribute->slug),'placeholder' => 'auto-generated-from-name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'slug','label' => 'Slug (optional)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('slug', $attribute->slug)),'placeholder' => 'auto-generated-from-name']); ?>
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

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Attribute Group (Specification Section)</label>
                <select name="attribute_group_id" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">— No Group (Unassigned) —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attributeGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($group->id); ?>" <?php if(old('attribute_group_id', $attribute->attribute_group_id) == $group->id): echo 'selected'; endif; ?>>
                            <?php echo e($group->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <p class="text-xs text-gray-400 mt-1">Organizes this field under a section heading (e.g. Main Features, Connectivity) on the product page.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Input Format / Type <span class="text-red-500">*</span></label>
                <select name="input_type_id" x-model="inputTypeId" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $inputTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t->id); ?>" <?php if($selectedInputTypeId == $t->id): echo 'selected'; endif; ?>>
                            <?php echo e($t->name); ?> <?php echo e($t->has_options ? '(Predefined options)' : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <input type="hidden" name="input_type" :value="inputType">
                <p class="text-xs text-gray-400 mt-1" x-text="currentInputType.description || ''"></p>
            </div>

            <?php if (isset($component)) { $__componentOriginalbed0546cb676c4dfa33e9654d0b1c543 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.select','data' => ['name' => 'unit_id','label' => 'Measurement Unit (optional)','placeholder' => '— No Unit —','selected' => old('unit_id', $attribute->unit_id)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'unit_id','label' => 'Measurement Unit (optional)','placeholder' => '— No Unit —','selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('unit_id', $attribute->unit_id))]); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($unit->id); ?>" <?php if(old('unit_id', $attribute->unit_id) == $unit->id): echo 'selected'; endif; ?>>
                        <?php echo e($unit->name); ?> (<?php echo e($unit->symbol); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543)): ?>
<?php $attributes = $__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543; ?>
<?php unset($__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbed0546cb676c4dfa33e9654d0b1c543)): ?>
<?php $component = $__componentOriginalbed0546cb676c4dfa33e9654d0b1c543; ?>
<?php unset($__componentOriginalbed0546cb676c4dfa33e9654d0b1c543); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'placeholder','label' => 'Input Placeholder / Hint','value' => old('placeholder', $attribute->placeholder),'placeholder' => 'e.g. e.g. 5.4 or Enter wattage']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'placeholder','label' => 'Input Placeholder / Hint','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('placeholder', $attribute->placeholder)),'placeholder' => 'e.g. e.g. 5.4 or Enter wattage']); ?>
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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'number','name' => 'sort_order','label' => 'Default Sort Order','value' => old('sort_order', $attribute->sort_order ?? 0),'min' => '0','placeholder' => '0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','name' => 'sort_order','label' => 'Default Sort Order','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('sort_order', $attribute->sort_order ?? 0)),'min' => '0','placeholder' => '0']); ?>
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
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?php if(old('is_active', $attribute->exists ? $attribute->is_active : true)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                <label for="is_active" class="text-sm font-medium text-gray-700">Active in Catalog</label>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100">
            <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_filterable" value="1" <?php if(old('is_filterable', $attribute->is_filterable)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Filterable by Default</span>
                    <span class="text-[11px] text-gray-400">Sidebar search filter</span>
                </div>
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_variant" value="1" <?php if(old('is_variant', $attribute->is_variant)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Variant Attribute</span>
                    <span class="text-[11px] text-gray-400">Creates product variants</span>
                </div>
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_required" value="1" <?php if(old('is_required', $attribute->is_required)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Required by Default</span>
                    <span class="text-[11px] text-gray-400">Mandatory for suppliers</span>
                </div>
            </label>
            <label x-show="hasOptions" class="flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2.5 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="allow_custom_value" value="1" <?php if(old('allow_custom_value', $attribute->allow_custom_value)): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                <div>
                    <span class="font-semibold block text-xs text-gray-900">Allow "Other"</span>
                    <span class="text-[11px] text-gray-400">Supplier can type custom value</span>
                </div>
            </label>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Input Validation Rules','description' => 'Configure input boundaries for suppliers.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Input Validation Rules','description' => 'Configure input boundaries for suppliers.']); ?>
        
        <div x-show="inputType === 'number'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'number','step' => 'any','name' => 'min_value','label' => 'Minimum Value (optional)','value' => old('min_value', $validationRules['min'] ?? ''),'placeholder' => 'e.g. 0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','step' => 'any','name' => 'min_value','label' => 'Minimum Value (optional)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('min_value', $validationRules['min'] ?? '')),'placeholder' => 'e.g. 0']); ?>
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
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'number','step' => 'any','name' => 'max_value','label' => 'Maximum Value (optional)','value' => old('max_value', $validationRules['max'] ?? ''),'placeholder' => 'e.g. 5000']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','step' => 'any','name' => 'max_value','label' => 'Maximum Value (optional)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('max_value', $validationRules['max'] ?? '')),'placeholder' => 'e.g. 5000']); ?>
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
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="decimal_allowed" id="decimal_allowed" value="1" <?php if(old('decimal_allowed', !empty($validationRules['decimal']))): echo 'checked'; endif; ?> style="accent-color:var(--theme-primary)">
                <label for="decimal_allowed" class="text-sm font-medium text-gray-700">Allow Decimal Numbers</label>
            </div>
        </div>

        
        <div x-show="inputType === 'text' || inputType === 'textarea'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'number','name' => 'min_length','label' => 'Minimum Length (characters)','value' => old('min_length', $validationRules['min_length'] ?? ''),'placeholder' => 'e.g. 2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','name' => 'min_length','label' => 'Minimum Length (characters)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('min_length', $validationRules['min_length'] ?? '')),'placeholder' => 'e.g. 2']); ?>
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
            <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'number','name' => 'max_length','label' => 'Maximum Length (characters)','value' => old('max_length', $validationRules['max_length'] ?? ''),'placeholder' => 'e.g. 255']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','name' => 'max_length','label' => 'Maximum Length (characters)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('max_length', $validationRules['max_length'] ?? '')),'placeholder' => 'e.g. 255']); ?>
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

        <div x-show="!hasOptions && inputType !== 'number' && inputType !== 'text' && inputType !== 'textarea'" class="text-xs text-gray-400 py-2">
            Standard format validation will be applied.
        </div>

        <div x-show="hasOptions" class="text-xs text-gray-400 py-2">
            Values are validated against the predefined choices below (or the custom value if allowed).
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

    
    <div x-show="hasOptions" class="space-y-4">
        <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Predefined Attribute Values','description' => 'Add selectable options for this attribute. Suppliers will choose from these values.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Predefined Attribute Values','description' => 'Add selectable options for this attribute. Suppliers will choose from these values.']); ?>
            <div class="space-y-3">
                <template x-for="(val, index) in values" :key="index">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                        
                        <div class="text-gray-400 text-xs font-semibold px-1" x-text="index + 1"></div>

                        
                        <input type="hidden" :name="'values[' + index + '][id]'" :value="val.id">

                        
                        <div class="flex-1 min-w-[150px]">
                            <input type="text" :name="'values[' + index + '][value]'" x-model="val.value" @input="autoSlug(index)"
                                   placeholder="Value (e.g. Bluetooth 5.4, 16GB, Black)" required
                                   class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        
                        <div class="flex-1 min-w-[120px]">
                            <input type="text" :name="'values[' + index + '][slug]'" x-model="val.slug"
                                   placeholder="Slug"
                                   class="w-full text-xs font-mono rounded-lg border border-gray-300 px-3 py-2 bg-white text-gray-600 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        
                        <template x-if="inputType === 'color'">
                            <div class="flex items-center gap-1.5 shrink-0">
                                <input type="color" x-model="val.color_hex" class="w-8 h-8 rounded border border-gray-300 cursor-pointer p-0.5 bg-white">
                                <input type="text" :name="'values[' + index + '][color_hex]'" x-model="val.color_hex"
                                       placeholder="#000000" class="w-20 text-xs font-mono rounded-lg border border-gray-300 px-2 py-2 bg-white">
                            </div>
                        </template>

                        
                        <div class="w-20 shrink-0">
                            <input type="number" :name="'values[' + index + '][sort_order]'" x-model="val.sort_order"
                                   placeholder="Order" class="w-full text-xs rounded-lg border border-gray-300 px-2 py-2 text-center bg-white">
                        </div>

                        
                        <button type="button" @click="removeValue(index)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-100/60 transition shrink-0" title="Remove value">
                            <i class="fa-regular fa-trash-can text-sm"></i>
                        </button>
                    </div>
                </template>

                <div x-show="values.length === 0" class="border border-dashed border-gray-300 rounded-xl p-6 text-center text-xs text-gray-500">
                    No predefined values added yet. Click <strong>"Add Value"</strong> below to create options.
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="addValue()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition border border-indigo-200">
                        <i class="fa-solid fa-plus text-xs"></i> Add Value
                    </button>
                    <span class="text-xs text-gray-400" x-text="values.length + ' option(s) defined'"></span>
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
    </div>

</div>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\admin\catalog\attributes\_form.blade.php ENDPATH**/ ?>