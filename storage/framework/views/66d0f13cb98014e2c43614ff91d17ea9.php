<?php
    $quotation = $quotation ?? null;
    $revisionRequest = $revisionRequest ?? null;
    $isRevision = $isRevision ?? false;
    $isEdit = $quotation?->exists ?? false;
    $action = $isRevision
        ? route('supplier.quotations.revision.store', $quotation)
        : ($isEdit ? route('supplier.quotations.update', $quotation) : route('supplier.quotations.store', $rfq));

    // Read-only "what the buyer asked for" lookup, keyed by rfq_item id —
    // shared by every response row so the comparison always reflects the
    // buyer's actual requirement, never the supplier's own listing data.
    $rfqItemsById = $rfq->items->keyBy('id')->map(fn ($i) => [
        'item_name' => $i->item_name,
        'category_id' => $i->category_id,
        'category_name' => $i->category?->name,
        'quantity' => rtrim(rtrim((string) $i->quantity, '0'), '.'),
        'unit' => $i->unit?->symbol ?? $i->unit?->name ?? $i->custom_unit,
        'description' => $i->description,
        'attributes_by_id' => $i->attributeValues->mapWithKeys(fn ($v) => [$v->attribute_id => $v->formattedValue()]),
    ]);

    if ($isEdit || $isRevision) {
        $initialItems = $quotation->items->where('is_optional_addon', false)->values()->map(fn ($item) => [
            'id' => $item->id, 'rfq_item_id' => $item->rfq_item_id,
            'offered_listing_id' => $item->offered_listing_id, 'offered_variant_id' => $item->offered_variant_id,
            'is_alternative' => (bool) $item->is_alternative,
            'item_name' => $item->item_name, 'description' => $item->description, 'quantity' => (string) $item->quantity,
            'unit_id' => $item->unit_id, 'custom_unit' => $item->custom_unit,
            'unit_price' => $item->unit_price, 'tax_rate' => $item->tax_rate, 'discount_amount' => $item->discount_amount,
            'lead_time_days' => $item->lead_time_days,
            'attribute_values' => (object) $item->attributeValues->mapWithKeys(fn ($v) => [$v->attribute_id => [
                'attribute_value_id' => $v->attribute_value_id, 'custom_value' => $v->custom_value,
                'value_text' => $v->value_text, 'value_number' => $v->value_number,
                'value_boolean' => $v->value_boolean, 'value_date' => $v->value_date, 'value_json' => $v->value_json,
            ]])->all(),
            '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [], '_variants' => [],
        ])->values();

        $initialAddons = $quotation->items->where('is_optional_addon', true)->values()->map(fn ($item) => [
            'id' => $item->id, 'item_name' => $item->item_name, 'description' => $item->description,
            'quantity' => (string) $item->quantity, 'unit_id' => $item->unit_id,
            'unit_price' => $item->unit_price, 'tax_rate' => $item->tax_rate, 'discount_amount' => $item->discount_amount,
            'lead_time_days' => $item->lead_time_days,
        ])->values();
    } else {
        $initialItems = $rfq->items->map(fn ($item) => [
            'id' => null, 'rfq_item_id' => $item->id,
            'offered_listing_id' => null, 'offered_variant_id' => null, 'is_alternative' => false,
            'item_name' => $item->item_name, 'description' => null, 'quantity' => (string) $item->quantity,
            'unit_id' => $item->unit_id, 'custom_unit' => $item->custom_unit,
            'unit_price' => null, 'tax_rate' => null, 'discount_amount' => null, 'lead_time_days' => null,
            'attribute_values' => (object) [],
            '_attrLoading' => false, '_attrGroups' => [], '_listingQuery' => '', '_listingResults' => [], '_variants' => [],
        ])->values();

        $initialAddons = collect();
    }
?>

<form
    method="POST"
    action="<?php echo e($action); ?>"
    x-data="quotationForm({
        items: <?php echo e($initialItems->toJson()); ?>,
        addons: <?php echo e($initialAddons->toJson()); ?>,
        rfqItemsById: <?php echo e($rfqItemsById->toJson()); ?>,
        allowAlternativeProducts: <?php echo e($rfq->allow_alternative_products ? 'true' : 'false'); ?>,
        currencyCode: '<?php echo e(old('currency_code', $quotation?->currency_code ?? $rfq->currency_code ?? 'USD')); ?>',
        shippingCharge: <?php echo e((float) old('shipping_charge', $quotation?->shipping_charge ?? 0)); ?>,
        categoryAttributesUrl: '<?php echo e(url('/supplier/quotations/categories')); ?>',
        listingsSearchUrl: '<?php echo e(route('supplier.quotations.listings.search')); ?>',
        listingsPrefillUrl: '<?php echo e(url('/supplier/quotations/listings')); ?>',
    })"
    x-init="init()"
>
    <?php echo csrf_field(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isRevision || $isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-8 space-y-6">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isRevision && $revisionRequest): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-amber-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-rotate"></i> Buyer Requested Changes
                    </h4>
                    <p class="text-xs text-amber-800 mt-1"><?php echo e($revisionRequest->requested_changes); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Requested Items','description' => 'Respond to each RFQ item — use one of your listings, offer an alternative, or create a fully custom offer.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Requested Items','description' => 'Respond to each RFQ item — use one of your listings, offer an alternative, or create a fully custom offer.']); ?>
                <template x-for="(item, index) in items" :key="index">
                    <?php echo $__env->make('backend.supplier.procurement.quotations.partials._item', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </template>

                <button type="button" @click="addItem()" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Another Item
                </button>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Optional Add-Ons','description' => 'Products or services you\'d like to offer that the buyer didn\'t request — shown separately and never treated as a response to an RFQ item.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Optional Add-Ons','description' => 'Products or services you\'d like to offer that the buyer didn\'t request — shown separately and never treated as a response to an RFQ item.']); ?>
                <template x-for="(addon, index) in addons" :key="index">
                    <?php echo $__env->make('backend.supplier.procurement.quotations.partials._addon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </template>

                <button type="button" @click="addAddon()" class="text-sm font-medium px-4 py-2 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Optional Item
                </button>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Commercial Proposal &amp; Terms']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Commercial Proposal &amp; Terms']); ?>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isRevision): ?>
                        <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'change_summary','label' => 'What changed in this revision?','required' => true,'value' => old('change_summary')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'change_summary','label' => 'What changed in this revision?','required' => true,'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('change_summary'))]); ?>
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
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <?php if (isset($component)) { $__componentOriginalbed0546cb676c4dfa33e9654d0b1c543 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbed0546cb676c4dfa33e9654d0b1c543 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.select','data' => ['name' => 'currency_code','label' => 'Currency','placeholder' => 'Select currency']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'currency_code','label' => 'Currency','placeholder' => 'Select currency']); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($currency->code); ?>" <?php if(old('currency_code', $quotation?->currency_code ?? $rfq->currency_code) === $currency->code): echo 'selected'; endif; ?>><?php echo e($currency->code); ?> — <?php echo e($currency->name); ?></option>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'number','name' => 'lead_time_days','label' => 'Overall Lead Time (Days)','value' => old('lead_time_days', $quotation?->lead_time_days)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','name' => 'lead_time_days','label' => 'Overall Lead Time (Days)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('lead_time_days', $quotation?->lead_time_days))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'date','name' => 'valid_until','label' => 'Quotation Validity Date','value' => old('valid_until', optional($quotation?->valid_until)->format('Y-m-d'))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'date','name' => 'valid_until','label' => 'Quotation Validity Date','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('valid_until', optional($quotation?->valid_until)->format('Y-m-d')))]); ?>
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
                    <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['type' => 'number','name' => 'shipping_charge','label' => 'Shipping Charge','step' => '0.01','min' => '0','value' => old('shipping_charge', $quotation?->shipping_charge ?? 0)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','name' => 'shipping_charge','label' => 'Shipping Charge','step' => '0.01','min' => '0','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('shipping_charge', $quotation?->shipping_charge ?? 0))]); ?>
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
                    <?php if (isset($component)) { $__componentOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb5285f6ddae5fa9f0bdd5c8c6f8c1f6e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.textarea','data' => ['name' => 'proposal','label' => 'Executive Summary / Proposal','value' => old('proposal', $quotation?->proposal),'placeholder' => 'Explain your proposal, brand advantages, quality assurances...']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'proposal','label' => 'Executive Summary / Proposal','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('proposal', $quotation?->proposal)),'placeholder' => 'Explain your proposal, brand advantages, quality assurances...']); ?>
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <?php if (isset($component)) { $__componentOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4c4712d2927ea1c3ea11625f9f54dbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'warranty_terms','label' => 'Warranty Terms','value' => old('warranty_terms', $quotation?->warranty_terms),'placeholder' => 'e.g. 1 Year Standard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'warranty_terms','label' => 'Warranty Terms','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('warranty_terms', $quotation?->warranty_terms)),'placeholder' => 'e.g. 1 Year Standard']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'support_terms','label' => 'Support Terms','value' => old('support_terms', $quotation?->support_terms),'placeholder' => 'e.g. 24/7 Phone Support']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'support_terms','label' => 'Support Terms','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('support_terms', $quotation?->support_terms)),'placeholder' => 'e.g. 24/7 Phone Support']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.input','data' => ['name' => 'payment_terms','label' => 'Payment Terms','value' => old('payment_terms', $quotation?->payment_terms),'placeholder' => 'e.g. Net 30']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'payment_terms','label' => 'Payment Terms','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('payment_terms', $quotation?->payment_terms)),'placeholder' => 'e.g. Net 30']); ?>
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

        <div class="xl:col-span-4 space-y-6">
            <?php if (isset($component)) { $__componentOriginal3c6ebeac636fe1a833069360516dddbb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c6ebeac636fe1a833069360516dddbb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.form-card','data' => ['title' => 'Quotation Summary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.form-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Quotation Summary']); ?>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Subtotal</span><span class="font-semibold text-gray-800" x-text="formatMoney(subtotal())"></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Tax</span><span class="font-semibold text-gray-800" x-text="formatMoney(totalTax())"></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Discount</span><span class="font-semibold text-gray-800">-<span x-text="formatMoney(totalDiscount())"></span></span></div>
                    <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Shipping</span><span class="font-semibold text-gray-800" x-text="formatMoney(parseFloat(shippingCharge || 0))"></span></div>
                    <div class="flex justify-between py-2 text-sm"><span class="text-gray-700 font-semibold">Grand Total</span><span class="font-bold text-indigo-700 text-base" x-text="formatMoney(grandTotal())"></span></div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isRevision): ?>
                    <button type="submit" class="btn-primary w-full text-sm font-bold py-3 rounded-xl flex items-center justify-center gap-2 mt-4 shadow-sm">
                        <i class="fa-solid fa-rotate"></i> Submit Revised Quotation
                    </button>
                <?php else: ?>
                    <button type="submit" class="w-full text-sm font-semibold py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 mt-4">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Save Draft
                    </button>
                    <p class="text-[11px] text-gray-400 mt-2 text-center">Review and submit from the quotation page when you're ready.</p>
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
</form>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quotationForm', (config) => ({
            items: config.items,
            addons: config.addons,
            rfqItemsById: config.rfqItemsById,
            allowAlternativeProducts: config.allowAlternativeProducts,
            currencyCode: config.currencyCode,
            shippingCharge: config.shippingCharge,

            init() {
                this.items.forEach(item => {
                    const buyerItem = this.rfqItemsById[item.rfq_item_id];
                    if (buyerItem) this.fetchItemAttributes(item, buyerItem.category_id);
                });
            },

            addItem() {
                this.items.push({
                    id: null, rfq_item_id: null, offered_listing_id: null, offered_variant_id: null, is_alternative: false,
                    item_name: '', description: '', quantity: '1', unit_id: null, custom_unit: null,
                    unit_price: null, tax_rate: null, discount_amount: null, lead_time_days: null,
                    attribute_values: {}, _attrLoading: false, _attrGroups: [], _listingQuery: '', _listingResults: [], _variants: [],
                });
            },
            removeItem(index) { this.items.splice(index, 1); },

            addAddon() {
                this.addons.push({ id: null, item_name: '', description: '', quantity: '1', unit_id: null, unit_price: null, tax_rate: null, discount_amount: null, lead_time_days: null });
            },
            removeAddon(index) { this.addons.splice(index, 1); },

            getOfferType(item) {
                if (item.is_alternative) return 'alternative';
                return item.offered_listing_id ? 'existing' : 'custom';
            },
            setOfferType(item, type) {
                item.is_alternative = (type === 'alternative');
                if (type === 'custom') {
                    item.offered_listing_id = null;
                    item.offered_variant_id = null;
                    item._variants = [];
                }
            },

            searchListingsForItem(item) {
                if (item._listingQuery.trim().length < 2) { item._listingResults = []; return; }
                const buyerItem = this.rfqItemsById[item.rfq_item_id];
                const categoryParam = buyerItem && buyerItem.category_id ? '&category_id=' + buyerItem.category_id : '';
                fetch(config.listingsSearchUrl + '?q=' + encodeURIComponent(item._listingQuery) + categoryParam)
                    .then(r => r.json())
                    .then(data => { item._listingResults = data; });
            },
            selectListingForItem(item, listing) {
                item._listingQuery = '';
                item._listingResults = [];
                fetch(config.listingsPrefillUrl + '/' + listing.id + '/prefill')
                    .then(r => r.json())
                    .then(data => {
                        item.offered_listing_id = data.item.offered_listing_id;
                        item.item_name = data.item.item_name;
                        item.description = data.item.description;
                        item.unit_id = data.item.unit_id;
                        item.unit_price = data.item.unit_price;
                        item.attribute_values = data.attribute_values || {};
                        item._variants = data.variants || [];
                    });
            },
            clearListingForItem(item) {
                item.offered_listing_id = null;
                item.offered_variant_id = null;
                item._variants = [];
            },

            fetchItemAttributes(item, categoryId) {
                if (!categoryId) { item._attrGroups = []; return; }
                item._attrLoading = true;
                // Editing/revising an existing quotation that already has a
                // value for an attribute since deactivated — keep it visible
                // instead of silently dropping it.
                let url = config.categoryAttributesUrl + '/' + categoryId + '/attributes';
                const keepIds = Object.keys(item.attribute_values || {});
                if (keepIds.length > 0) {
                    url += '?keep_attribute_ids=' + keepIds.join(',');
                }
                fetch(url)
                    .then(r => r.json())
                    .then(data => { item._attrGroups = data.groups || []; })
                    .finally(() => { item._attrLoading = false; });
            },

            getAttrVal(item, attrId) {
                if (!item.attribute_values[attrId]) {
                    item.attribute_values[attrId] = {
                        attribute_value_id: null, custom_value: null, value_text: null,
                        value_number: null, value_boolean: null, value_date: null, value_json: [],
                    };
                }
                return item.attribute_values[attrId];
            },
            isOtherSelected(item, attrId) {
                return this.getAttrVal(item, attrId).attribute_value_id === '__other__';
            },
            isMultiSelected(item, attrId, value) {
                const v = this.getAttrVal(item, attrId).value_json;
                return Array.isArray(v) && v.includes(value);
            },
            toggleMultiSelect(item, attrId, value) {
                const val = this.getAttrVal(item, attrId);
                if (!Array.isArray(val.value_json)) val.value_json = [];
                const idx = val.value_json.indexOf(value);
                if (idx === -1) val.value_json.push(value); else val.value_json.splice(idx, 1);
            },

            lineTotal(row) {
                const qty = parseFloat(row.quantity || 0);
                const price = parseFloat(row.unit_price || 0);
                const lineSubtotal = qty * price;
                const discount = parseFloat(row.discount_amount || 0);
                const taxRate = (row.tax_rate !== null && row.tax_rate !== '' && row.tax_rate !== undefined) ? parseFloat(row.tax_rate) : null;
                const tax = taxRate !== null ? (lineSubtotal - discount) * taxRate / 100 : 0;
                return lineSubtotal - discount + tax;
            },
            lineTax(row) {
                const qty = parseFloat(row.quantity || 0);
                const price = parseFloat(row.unit_price || 0);
                const lineSubtotal = qty * price;
                const discount = parseFloat(row.discount_amount || 0);
                const taxRate = (row.tax_rate !== null && row.tax_rate !== '' && row.tax_rate !== undefined) ? parseFloat(row.tax_rate) : null;
                return taxRate !== null ? (lineSubtotal - discount) * taxRate / 100 : 0;
            },
            formatMoney(n) {
                return (this.currencyCode || 'USD') + ' ' + (isNaN(n) ? '0.00' : n.toFixed(2));
            },
            allRows() { return [...this.items, ...this.addons]; },
            subtotal() { return this.allRows().reduce((s, r) => s + parseFloat(r.quantity || 0) * parseFloat(r.unit_price || 0), 0); },
            totalTax() { return this.allRows().reduce((s, r) => s + this.lineTax(r), 0); },
            totalDiscount() { return this.allRows().reduce((s, r) => s + parseFloat(r.discount_amount || 0), 0); },
            grandTotal() { return this.subtotal() - this.totalDiscount() + this.totalTax() + parseFloat(this.shippingCharge || 0); },
        }));
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\edushopify\resources\views\backend\supplier\procurement\quotations\partials\_form.blade.php ENDPATH**/ ?>