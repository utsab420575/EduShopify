<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\RbacAuditLogger;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class UserRoleAssignmentController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.access_control.manage');

        $users = User::with(['roles', 'accountMember.account'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->whereHas('roles', fn ($sq) => $sq->where('roles.id', $request->integer('role')));
            })
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('display_name')->get();

        return view('backend.admin.access-control.user-roles.index', [
            'users'  => $users,
            'roles'  => $roles,
            'search' => $request->string('search')->toString(),
            'roleId' => $request->integer('role'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('platform.access_control.manage');

        $validated = $request->validate([
            'role_ids'   => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $accountId = $user->accountMember?->account_id;
        app(PermissionRegistrar::class)->setPermissionsTeamId($accountId);

        $roles = Role::whereIn('id', $validated['role_ids'] ?? [])->get();
        $user->syncRoles($roles);

        foreach ($roles as $role) {
            RbacAuditLogger::logRoleAssigned($user, $role, $accountId);
        }

        return redirect()->route('admin.access-control.user-roles.index')
            ->with('success', "Updated role assignments for {$user->name}.");
    }
}
