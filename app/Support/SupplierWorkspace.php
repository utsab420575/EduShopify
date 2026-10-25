<?php

namespace App\Support;

use App\Models\Account;

/**
 * Which supplier account the Supplier "workspace" pages (Business Profile,
 * Catalog › Listings) are acting on for this request.
 *
 * The same controllers/views serve two entry points:
 *   /supplier/...                              → the logged-in supplier's own account
 *   /admin/suppliers/{supplierAccount}/manage/ → an admin managing that supplier
 *                                                (ManageSupplierAccount middleware)
 *
 * Route names and the page layout follow the entry point, so a form on the
 * admin side posts back to the admin side and renders inside the admin panel.
 */
class SupplierWorkspace
{
    private ?Account $managedAccount = null;

    public function manage(Account $account): void
    {
        $this->managedAccount = $account;
    }

    /** True when an admin is managing a supplier account (not the supplier themself). */
    public function isAdminManaging(): bool
    {
        return $this->managedAccount !== null;
    }

    public function managedAccount(): ?Account
    {
        return $this->managedAccount;
    }

    /** Full route name for a workspace route, e.g. 'company.profile'. */
    public function routeName(string $name): string
    {
        return ($this->isAdminManaging() ? 'admin.supplier-manage.' : 'supplier.') . $name;
    }

    public function layout(): string
    {
        return $this->isAdminManaging() ? 'backend.layouts.admin' : 'backend.layouts.supplier';
    }
}
