<?php

namespace App\Http\Controllers\Backend\Supplier\Concerns;

use App\Models\Account;
use App\Models\User;
use App\Support\SupplierWorkspace;
use Illuminate\Support\Facades\Auth;

/**
 * Every Supplier controller acts on behalf of the current user's active account
 * (the Spatie Teams context set by SetAccountContext/RequireSupplierCapability).
 * Centralizing resolution here keeps that one line consistent everywhere.
 *
 * When an admin manages a supplier from the admin panel (ManageSupplierAccount
 * middleware), the same controllers act on that supplier's account instead —
 * every ownership check below still compares against it.
 */
trait InteractsWithSupplierAccount
{
    protected function currentUser(): User
    {
        return Auth::user();
    }

    protected function currentAccount(): Account
    {
        $workspace = app(SupplierWorkspace::class);

        return $workspace->isAdminManaging()
            ? $workspace->managedAccount()
            : $this->currentUser()->activateTeamContext();
    }
}
