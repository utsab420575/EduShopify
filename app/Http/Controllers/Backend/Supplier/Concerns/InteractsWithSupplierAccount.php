<?php

namespace App\Http\Controllers\Backend\Supplier\Concerns;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Every Supplier controller acts on behalf of the current user's active account
 * (the Spatie Teams context set by SetAccountContext/RequireSupplierCapability).
 * Centralizing resolution here keeps that one line consistent everywhere.
 */
trait InteractsWithSupplierAccount
{
    protected function currentUser(): User
    {
        return Auth::user();
    }

    protected function currentAccount(): Account
    {
        return $this->currentUser()->activateTeamContext();
    }
}
