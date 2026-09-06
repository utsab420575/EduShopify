<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'map' => []]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status', 'map' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $default = [
        // green — positive / completed
        'active' => 'success', 'approved' => 'success', 'verified' => 'success', 'completed' => 'success',
        'published' => 'success', 'accepted' => 'success', 'open' => 'success', 'paid' => 'success',
        'answered' => 'success', 'resolved' => 'success', 'eligible' => 'success', 'confirmed' => 'success',

        // amber — pending / attention
        'pending' => 'warning', 'pending_approval' => 'warning', 'draft' => 'neutral',
        'revision_required' => 'warning', 'revision_requested' => 'warning', 'under_review' => 'warning',
        'submitted' => 'warning', 'pending_supplier_response' => 'warning', 'award_pending' => 'warning',
        'shortlisted' => 'info', 'revised' => 'warning', 'in_progress' => 'warning',
        'ready_for_delivery' => 'warning', 'not_recorded' => 'neutral', 'unpaid' => 'warning',
        'partially_paid' => 'warning', 'invited' => 'warning', 'delivered' => 'info',

        // red — negative
        'rejected' => 'danger', 'rejected_by_supplier' => 'danger', 'suspended' => 'danger',
        'cancelled' => 'danger', 'expired' => 'danger', 'failed' => 'danger', 'declined' => 'danger',
        'withdrawn' => 'danger', 'disputed' => 'danger', 'removed' => 'danger', 'closed' => 'neutral',
        'blocked' => 'danger', 'flagged' => 'danger', 'superseded' => 'neutral', 'hidden' => 'neutral',

        // gray — neutral / historical
        'inactive' => 'neutral', 'deletion_pending' => 'neutral', 'awarded' => 'info', 'issued' => 'info',
    ];

    $tone = $map[$status] ?? $default[$status] ?? 'neutral';

    $classes = [
        'success' => 'bg-green-50 text-green-700 border-green-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'danger'  => 'bg-red-50 text-red-700 border-red-200',
        'neutral' => 'bg-gray-100 text-gray-600 border-gray-200',
        'info'    => 'bg-blue-50 text-blue-700 border-blue-200',
    ][$tone];

    $label = $label ?? ucwords(str_replace('_', ' ', (string) $status));
?>

<span <?php echo e($attributes->merge(['class' => "inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border $classes"])); ?>>
    <i class="fa-solid fa-circle text-[6px]"></i>
    <?php echo e($label); ?>

</span>
<?php /**PATH C:\laragon\www\edushopify\resources\views/components/backend/status-badge.blade.php ENDPATH**/ ?>