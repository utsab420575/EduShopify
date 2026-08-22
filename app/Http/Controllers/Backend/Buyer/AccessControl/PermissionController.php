<?php

namespace App\Http\Controllers\Backend\Buyer\AccessControl;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        abort_unless($this->currentAccount()->isOrganization(), 403);

        $permissions = Permission::active()
            ->whereIn('capability_scope', ['buyer', 'common', 'both'])
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group_name');

        return view('backend.buyer.access-control.permissions.index', ['permissionGroups' => $permissions]);
    }
}
