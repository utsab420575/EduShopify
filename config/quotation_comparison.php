<?php

return [
    'max_items' => (int) env('QUOTATION_COMPARE_MAX_ITEMS', 5),

    'eligible_statuses' => ['submitted', 'under_review', 'revised', 'shortlisted', 'awarded'],
];
