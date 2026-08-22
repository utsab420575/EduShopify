<?php

namespace App\Http\Controllers\Backend\Buyer\Concerns;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Every Buyer controller acts on behalf of the current user's active account
 * (the Spatie Teams context set by SetAccountContext/RequireBuyerCapability).
 * Centralizing resolution here keeps that one line consistent everywhere.
 */
trait InteractsWithBuyerAccount
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
