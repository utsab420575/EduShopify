<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RbacAuditLogger;
use Illuminate\Http\Request;

class RoleInPermissionController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.access_control.manage');

        $roles = Role::with('permissions')
            ->when($request->filled('search'), fn ($q) => $q->where('display_name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('capability_scope')
            ->orderBy('display_name')
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.access-control.roles-in-permission.index', [
            'roles' => $roles,
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.access_control.manage');

        $roles = Role::orderBy('display_name')->get();
        $permissionGroups = Permission::active()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('backend.admin.access-control.roles-in-permission.create', [
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('platform.access_control.manage');

        $validated = $request->validate([
            'role_id'       => ['required', 'exists:roles,id'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $validated['permissions'] ?? [];

        $role->syncPermissions($newPermissions);

        RbacAuditLogger::logPermissionsSynced($role, $oldPermissions, $newPermissions);

        return redirect()->route('admin.access-control.roles-in-permission.index')
            ->with('success', "Assigned " . count($newPermissions) . " default permissions to role '{$role->display_name}'.");
    }

    public function edit(Role $role)
    {
        $this->authorize('platform.access_control.manage');

        $role->load('permissions');
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $roles = Role::orderBy('display_name')->get();
        $permissionGroups = Permission::active()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('backend.admin.access-control.roles-in-permission.edit', [
            'role' => $role,
            'roles' => $roles,
            'rolePermissions' => $rolePermissions,
            'permissionGroups' => $permissionGroups,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('platform.access_control.manage');

        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $validated['permissions'] ?? [];

        $role->syncPermissions($newPermissions);

        RbacAuditLogger::logPermissionsSynced($role, $oldPermissions, $newPermissions);

        return redirect()->route('admin.access-control.roles-in-permission.index')
            ->with('success', "Updated permissions for role '{$role->display_name}'.");
    }

    public function getRolePermissions(Role $role)
    {
        $this->authorize('platform.access_control.manage');

        return response()->json([
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }
}
