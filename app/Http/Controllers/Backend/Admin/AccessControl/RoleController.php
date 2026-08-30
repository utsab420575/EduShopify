<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\AccessControl\StoreRoleRequest;
use App\Http\Requests\Backend\Admin\AccessControl\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RbacAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            ->with('permissions')
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
        $this->authorize('platform.access_control.manage');

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

        $permissions = $request->input('permissions', []);
        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        RbacAuditLogger::logRoleCreated($role);

        return redirect()->route('admin.access-control.roles.index')->with('success', "Role '{$role->display_name}' created successfully.");
    }

    public function edit(Role $role)
    {
        $this->authorize('platform.access_control.manage');

        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');

        return view('backend.admin.access-control.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::active()->orderBy('group_name')->get()->groupBy('group_name'),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('platform.access_control.manage');
        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');

        $role->update([
            'display_name' => $request->string('display_name'),
            'description' => $request->string('description'),
        ]);

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $request->input('permissions', []);
        $role->syncPermissions($newPermissions);

        RbacAuditLogger::logPermissionsSynced($role, $oldPermissions, $newPermissions);

        return redirect()->route('admin.access-control.roles.index')->with('success', "Default permissions for '{$role->display_name}' updated successfully.");
    }

    public function duplicate(Request $request, Role $role)
    {
        $this->authorize('platform.access_control.manage');
        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');

        $validated = $request->validate([
            'new_display_name' => ['required', 'string', 'max:100'],
        ]);

        $newSlug = Str::slug($validated['new_display_name'], '_');
        if (Role::where('name', $newSlug)->exists()) {
            $newSlug .= '_' . time();
        }

        $newRole = Role::create([
            'account_id'         => null,
            'name'               => $newSlug,
            'guard_name'         => 'web',
            'display_name'       => $validated['new_display_name'],
            'capability_scope'   => $role->capability_scope,
            'description'        => $role->description . ' (Duplicated from ' . $role->display_name . ')',
            'is_system'          => false,
            'is_owner_role'      => false,
            'is_active'          => true,
            'created_by_user_id' => $this->admin()->id,
        ]);

        $permissions = $role->permissions->pluck('name')->toArray();
        $newRole->syncPermissions($permissions);

        RbacAuditLogger::logRoleDuplicated($role, $newRole);

        return redirect()->route('admin.access-control.roles.edit', $newRole)
            ->with('success', "Role duplicated as '{$newRole->display_name}'. You can now adjust its default permissions.");
    }

    public function destroy(Role $role)
    {
        $this->authorize('platform.access_control.manage');

        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');
        abort_if(in_array($role->name, ['super_admin', 'admin']), 403, 'Core root administrator roles cannot be deleted.');
        abort_if($role->users()->exists(), 422, 'This role is currently assigned to users. Unassign it from users before deleting.');

        $displayName = $role->display_name;
        $role->delete();

        return back()->with('success', "Role '{$displayName}' deleted successfully.");
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

        RbacAuditLogger::logRoleAssigned($user, $role, $accountId);

        return back()->with('success', "Role '{$role->display_name}' assigned to {$user->name}.");
    }

    public function unassign(Request $request, Role $role)
    {
        $this->authorize('platform.access_control.manage');

        abort_unless($role->isGlobal(), 404, 'Account-scoped roles are managed within their own portal.');

        $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $user = User::with('accountMember')->findOrFail($request->integer('user_id'));
        $accountId = $user->accountMember?->account_id;

        abort_if(! $accountId, 422, 'This user has no account to assign the role within.');

        app(PermissionRegistrar::class)->setPermissionsTeamId($accountId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->removeRole($role);

        RbacAuditLogger::logRoleUnassigned($user, $role, $accountId);

        return back()->with('success', "Role '{$role->display_name}' removed from {$user->name}.");
    }
}
