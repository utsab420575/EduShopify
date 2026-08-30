<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product Comparison
    |--------------------------------------------------------------------------
    |
    | Selection state lives entirely in the browser (localStorage) — this
    | value is the single source of truth for the maximum comparison size,
    | enforced both client-side (resources/js/frontend/comparison.js) and
    | server-side (App\Services\Catalog\ProductComparisonService), so the two
    | never drift.
    |
    */
    'max_items' => (int) env('COMPARISON_MAX_ITEMS', 5),
];
