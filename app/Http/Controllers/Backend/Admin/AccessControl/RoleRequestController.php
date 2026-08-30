<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\RoleRequest;
use App\Services\RoleRequestService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleRequestController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.access_control.manage');

        $roleRequests = RoleRequest::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('account', fn ($q2) => $q2->where('display_name', 'like', '%'.$request->string('search').'%'));
            })
            ->with(['account', 'requestedBy'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.access-control.role-requests.index', [
            'roleRequests' => $roleRequests,
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(RoleRequest $roleRequest)
    {
        $this->authorize('platform.access_control.manage');

        $roleRequest->load(['account', 'requestedBy', 'reviewedBy']);

        return view('backend.admin.access-control.role-requests.show', ['roleRequest' => $roleRequest]);
    }

    public function approve(RoleRequest $roleRequest, RoleRequestService $service)
    {
        $this->authorize('platform.access_control.manage');

        try {
            $service->approve($roleRequest, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        // 'rbac', not 'access_control' — RbacAuditLogController only ever
        // queries log_name = 'rbac', so this must match or the entry is
        // silently invisible on the Audit Logs page.
        activity('rbac')->causedBy($this->admin())->performedOn($roleRequest)->log('Role request approved');

        return back()->with('success', 'Role request approved — the role is now available to the account.');
    }

    public function reject(ReasonRequest $request, RoleRequest $roleRequest, RoleRequestService $service)
    {
        $this->authorize('platform.access_control.manage');

        try {
            $service->reject($roleRequest, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('rbac')->causedBy($this->admin())->performedOn($roleRequest)
            ->withProperties(['reason' => $request->string('reason')])->log('Role request rejected');

        return back()->with('success', 'Role request rejected.');
    }
}
