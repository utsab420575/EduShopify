<?php

namespace App\Http\Controllers\Backend\Supplier\AccessControl;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\AccountMember;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RbacAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $roles = Role::usableBy($account->id)
            ->active()
            ->whereIn('capability_scope', ['supplier', 'common', 'both'])
            ->orderBy('is_system', 'desc')
            ->orderBy('display_name')
            ->get()
            ->each(fn (Role $role) => $role->setAttribute(
                'users_count',
                $role->users()->wherePivot('account_id', $account->id)->count()
            ));

        return view('backend.supplier.access-control.roles.index', [
            'account' => $account,
            'user'    => $this->currentUser(),
            'roles'   => $roles,
        ]);
    }

    public function create()
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $permissionGroups = Permission::active()
            ->whereIn('capability_scope', ['supplier', 'common', 'both', 'all'])
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('backend.supplier.access-control.roles.create', [
            'account'          => $account,
            'user'             => $this->currentUser(),
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:500'],
            'permissions'  => ['nullable', 'array'],
            'permissions.*'=> ['string', 'exists:permissions,name'],
        ]);

        $baseSlug = Str::slug($validated['display_name'], '_');
        $slug = $baseSlug . '_' . $account->id;

        $role = Role::create([
            'account_id'         => $account->id,
            'name'               => $slug,
            'guard_name'         => 'web',
            'display_name'       => $validated['display_name'],
            'capability_scope'   => 'supplier',
            'description'        => $validated['description'] ?? null,
            'is_system'          => false,
            'is_owner_role'      => false,
            'is_active'          => true,
            'created_by_user_id' => $this->currentUser()->id,
        ]);

        $selectedPermissions = $validated['permissions'] ?? [];
        $role->syncPermissions($selectedPermissions);

        RbacAuditLogger::logRoleCreated($role, $account->id);

        return redirect()->route('supplier.roles.index')
            ->with('success', "Custom role '{$role->display_name}' created with " . count($selectedPermissions) . " permissions.");
    }

    public function show(Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($role->isGlobal() || $role->account_id === $account->id, 403);

        $role->load('permissions');

        $members = $account->members()->active()->with('user')->get();
        $assignedUserIds = $role->users()->wherePivot('account_id', $account->id)->pluck('users.id');

        $groupedPermissions = $role->permissions->groupBy('group_name');

        return view('backend.supplier.access-control.roles.show', [
            'account'            => $account,
            'user'               => $this->currentUser(),
            'role'               => $role,
            'members'            => $members,
            'assignedUserIds'    => $assignedUserIds,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function edit(Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);

        // Security Guard: System roles are read-only and cannot be edited by tenant accounts
        abort_if($role->is_system || $role->account_id !== $account->id, 403, 'System roles cannot be edited. Please duplicate this role to customize permissions.');

        $role->load('permissions');
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $permissionGroups = Permission::active()
            ->whereIn('capability_scope', ['supplier', 'common', 'both', 'all'])
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('backend.supplier.access-control.roles.edit', [
            'account'          => $account,
            'user'             => $this->currentUser(),
            'role'             => $role,
            'rolePermissions'  => $rolePermissions,
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_if($role->is_system || $role->account_id !== $account->id, 403, 'System roles cannot be edited.');

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:500'],
            'permissions'  => ['nullable', 'array'],
            'permissions.*'=> ['string', 'exists:permissions,name'],
        ]);

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $validated['permissions'] ?? [];

        $role->update([
            'display_name' => $validated['display_name'],
            'description'  => $validated['description'] ?? null,
        ]);

        $role->syncPermissions($newPermissions);

        RbacAuditLogger::logPermissionsSynced($role, $oldPermissions, $newPermissions, $account->id);

        return redirect()->route('supplier.roles.index')
            ->with('success', "Role '{$role->display_name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_if($role->is_system || $role->account_id !== $account->id, 403, 'System roles cannot be deleted.');

        $displayName = $role->display_name;
        $role->delete();

        return redirect()->route('supplier.roles.index')
            ->with('success', "Custom role '{$displayName}' deleted successfully.");
    }

    public function duplicate(Request $request, Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($role->isGlobal() || $role->account_id === $account->id, 403);

        $validated = $request->validate([
            'new_display_name' => ['required', 'string', 'max:100'],
        ]);

        $baseSlug = Str::slug($validated['new_display_name'], '_');
        $slug = $baseSlug . '_' . $account->id . '_' . time();

        $newRole = Role::create([
            'account_id'         => $account->id,
            'name'               => $slug,
            'guard_name'         => 'web',
            'display_name'       => $validated['new_display_name'],
            'capability_scope'   => $role->capability_scope,
            'description'        => "Custom copy of {$role->display_name} for {$account->display_name}",
            'is_system'          => false,
            'is_owner_role'      => false,
            'is_active'          => true,
            'created_by_user_id' => $this->currentUser()->id,
        ]);

        $permissions = $role->permissions->pluck('name')->toArray();
        $newRole->syncPermissions($permissions);

        RbacAuditLogger::logRoleDuplicated($role, $newRole, $account->id);

        return redirect()->route('supplier.roles.edit', $newRole)
            ->with('success', "Role duplicated as '{$newRole->display_name}'. You can now customize its permissions.");
    }

    public function assign(Request $request, Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($role->isGlobal() || $role->account_id === $account->id, 403);
        abort_if($role->is_owner_role, 422, 'Ownership roles are managed from the Ownership page.');

        $request->validate(['member_id' => ['required', 'integer', 'exists:account_members,id']]);

        $member = AccountMember::where('account_id', $account->id)->findOrFail($request->integer('member_id'));

        $member->user->activateTeamContext();
        $member->user->assignRole($role);

        RbacAuditLogger::logRoleAssigned($member->user, $role, $account->id);

        return back()->with('success', "Role '{$role->display_name}' assigned to {$member->user->name}.");
    }

    public function unassign(Request $request, Role $role)
    {
        $account = $this->currentAccount();
        abort_unless($account->isOrganization(), 403);
        abort_unless($role->isGlobal() || $role->account_id === $account->id, 403);

        $request->validate(['member_id' => ['required', 'integer', 'exists:account_members,id']]);

        $member = AccountMember::where('account_id', $account->id)->findOrFail($request->integer('member_id'));

        $member->user->activateTeamContext();
        $member->user->removeRole($role);

        RbacAuditLogger::logRoleUnassigned($member->user, $role, $account->id);

        return back()->with('success', "Role '{$role->display_name}' removed from {$member->user->name}.");
    }
}
