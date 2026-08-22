<?php

namespace App\Http\Controllers\Backend\Admin\AccessControl;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.access_control.manage');

        $permissions = Permission::query()
            ->when($request->filled('scope'), fn ($q) => $q->where('capability_scope', $request->string('scope')))
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group_name');

        return view('backend.admin.access-control.permissions.index', [
            'permissions' => $permissions,
            'scope' => $request->string('scope')->toString(),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $this->authorize('platform.access_control.manage');

        $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'is_assignable' => ['sometimes', 'boolean'],
        ]);

        $permission->update([
            'display_name' => $request->string('display_name'),
            'is_active' => $request->boolean('is_active'),
            'is_assignable' => $request->boolean('is_assignable'),
        ]);

        return back()->with('success', 'Permission updated.');
    }
}
