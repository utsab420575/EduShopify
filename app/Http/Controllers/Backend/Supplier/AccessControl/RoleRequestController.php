<?php

namespace App\Http\Controllers\Backend\Supplier\AccessControl;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleRequest;
use App\Services\RoleRequestService;
use Illuminate\Http\Request;

class RoleRequestController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $requests = $account->roleRequests()->with(['user', 'role', 'reviewedBy'])->latest()->paginate(10);

        return view('backend.supplier.access-control.role-requests.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'roleRequests' => $requests,
        ]);
    }

    public function create()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $roles = Role::where(function ($q) use ($account) {
            $q->whereNull('account_id')->orWhere('account_id', $account->id);
        })->get();

        return view('backend.supplier.access-control.role-requests.create', [
            'account' => $account,
            'user' => $this->currentUser(),
            'roles' => $roles,
        ]);
    }

    public function store(Request $request, RoleRequestService $service)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
            'justification' => ['required', 'string', 'max:1000'],
        ]);

        $service->submit($account, $this->currentUser(), $validated);

        return redirect()->route('supplier.role-requests.index')->with('success', 'Role request submitted.');
    }

    public function cancel(RoleRequest $roleRequest, RoleRequestService $service)
    {
        $account = $this->currentAccount();
        abort_unless($roleRequest->account_id === $account->id, 403);

        $service->cancel($roleRequest, $this->currentUser());

        return redirect()->route('supplier.role-requests.index')->with('success', 'Role request cancelled.');
    }
}
