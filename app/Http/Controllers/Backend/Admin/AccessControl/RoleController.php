<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\AccessControl\StoreRoleRequest;
use App\Http\Requests\Backend\Admin\AccessControl\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.access_control.manage');

        $roles = Role::query()
            ->when($request->filled('scope'), fn ($q) => $q->where('capability_scope', $request->string('scope')))
            ->when($request->filled('search'), fn ($q) => $q->where('display_name', 'like', '%'.$request->string('search').'%'))
            ->withCount('users')
            ->orderBy('capability_scope')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.access-control.roles.index', [
            'roles' => $roles,
            'scope' => $request->string('scope')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.access_control.manage');

        return view('backend.admin.access-control.roles.create', [
            'role' => new Role(),
            'permissions' => Permission::active()->orderBy('group_name')->get()->groupBy('group_name'),
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create([
            'account_id' => null,
            'name' => $request->string('name'),
            'guard_name' => 'web',
            'display_name' => $request->string('display_name'),
            'capability_scope' => $request->string('capability_scope'),
            'description' => $request->string('description'),
            'is_system' => false,
            'is_owner_role' => false,
            'is_active' => true,
            'created_by_user_id' => $this->admin()->id,
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('admin.access-control.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        $this->authorize('platform.access_control.manage');

        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');
        abort_if($role->is_system, 403, 'System roles cannot be edited here.');

        return view('backend.admin.access-control.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::active()->orderBy('group_name')->get()->groupBy('group_name'),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');
        abort_if($role->is_system, 403, 'System roles cannot be edited here.');

        $role->update([
            'display_name' => $request->string('display_name'),
            'description' => $request->string('description'),
        ]);

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.access-control.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $this->authorize('platform.access_control.manage');

        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');
        abort_if($role->is_system, 403, 'System roles cannot be deleted.');
        abort_if($role->users()->exists(), 422, 'This role is assigned to users and cannot be deleted.');

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    public function assign(Request $request, Role $role)
    {
        $this->authorize('platform.access_control.manage');

        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');

        $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $user = User::with('accountMember')->findOrFail($request->integer('user_id'));
        $accountId = $user->accountMember?->account_id;

        abort_if(! $accountId, 422, 'This user has no account to assign the role within.');

        app(PermissionRegistrar::class)->setPermissionsTeamId($accountId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->assignRole($role);

        activity('access_control')->causedBy($this->admin())->performedOn($role)
            ->withProperties(['user_id' => $user->id])->log('Role assigned by admin');

        return back()->with('success', 'Role assigned.');
    }

    public function unassign(Request $request, Role $role)
    {
        $this->authorize('platform.access_control.manage');

        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');

        $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $user = User::with('accountMember')->findOrFail($request->integer('user_id'));
        $accountId = $user->accountMember?->account_id;

        abort_if(! $accountId, 422, 'This user has no account context.');

        app(PermissionRegistrar::class)->setPermissionsTeamId($accountId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->removeRole($role);

        activity('access_control')->causedBy($this->admin())->performedOn($role)
            ->withProperties(['user_id' => $user->id])->log('Role unassigned by admin');

        return back()->with('success', 'Role unassigned.');
    }
}
