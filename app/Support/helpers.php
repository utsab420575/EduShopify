<?php

use App\Support\SupplierWorkspace;

/*
| Loaded from AppServiceProvider::register() (no composer "files" autoload,
| so a plain git pull is enough on deploy).
*/

if (! function_exists('supplier_route')) {
    /**
     * URL for a Supplier workspace route ('company.profile', 'catalog.listings.edit', ...)
     * that stays inside the current entry point: the supplier's own dashboard,
     * or the admin panel when an admin is managing that supplier (the
     * {supplierAccount} parameter is filled in via URL::defaults()).
     */
    function supplier_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(app(SupplierWorkspace::class)->routeName($name), $parameters, $absolute);
    }
}

if (! function_exists('supplier_layout')) {
    /** Layout for Supplier workspace pages — the admin layout when an admin is managing. */
    function supplier_layout(): string
    {
        return app(SupplierWorkspace::class)->layout();
    }
}
