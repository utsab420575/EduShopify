<?php

namespace App\Http\Controllers\Backend\Supplier\AccessControl;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Permission;

class PermissionController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $permissions = Permission::where(function ($q) {
            $q->whereNull('capability_scope')->orWhere('capability_scope', 'supplier')->orWhere('capability_scope', 'all');
        })->get()->groupBy('group_name');

        return view('backend.supplier.access-control.permissions.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'permissionGroups' => $permissions,
        ]);
    }
}
