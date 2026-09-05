<?php

namespace App\Support;

/**
 * Deterministic (per supplier ID, not per list position) demo badge flags
 * for concepts with no backing schema — see config/frontend_new_demo.php.
 * ID-based rather than positional so the same real supplier shows the same
 * cosmetic badges everywhere they appear (homepage grid, their own profile,
 * "You may also like" rows), instead of flags that shift with list order.
 */
class FrontendNewDemo
{
    public static function supplierBadges(int $supplierId): array
    {
        $pattern = config('frontend_new_demo.supplier_badge_pattern', []);

        if (empty($pattern)) {
            return ['founding' => false, 'ise' => false, 'bett' => false];
        }

        return $pattern[$supplierId % count($pattern)];
    }
}
