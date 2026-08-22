<?php

namespace App\Http\Controllers\Backend\Buyer\AccessControl;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\AccessControl\StoreRoleRequestRequest;
use App\Models\Permission;
use App\Models\RoleRequest;

class RoleRequestController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $roleRequests = $account->roleRequests()->with('reviewedBy')->latest()->get();

        return view('backend.buyer.access-control.role-requests.index', ['roleRequests' => $roleRequests]);
    }

    public function create()
    {
        abort_unless($this->currentAccount()->isOrganization(), 403);

        return view('backend.buyer.access-control.role-requests.create', [
            'permissions' => Permission::delegatable()->whereIn('capability_scope', ['buyer', 'common', 'both'])->orderBy('group_name')->get()->groupBy('group_name'),
        ]);
    }

    public function store(StoreRoleRequestRequest $request)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        // Only permissions this account may legally delegate can be requested.
        $allowed = Permission::delegatable()
            ->whereIn('capability_scope', ['buyer', 'common', 'both'])
            ->whereIn('name', $request->input('requested_permissions'))
            ->pluck('name');

        $account->roleRequests()->create([
            'requested_by_user_id' => $this->currentUser()->id,
            'role_name' => $request->string('role_name'),
            'display_name' => $request->string('display_name'),
            'capability_scope' => 'buyer',
            'requested_permissions' => $allowed->values()->all(),
            'description' => $request->string('description'),
            'status' => 'pending',
        ]);

        return redirect()->route('buyer.role-requests.index')->with('success', 'Role request submitted for admin review.');
    }

    public function cancel(RoleRequest $roleRequest)
    {
        abort_unless($roleRequest->account_id === $this->currentAccount()->id, 403);
        abort_unless($roleRequest->status === 'pending', 422, 'Only a pending request can be cancelled.');

        $roleRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Role request cancelled.');
    }
}
