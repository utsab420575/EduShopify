<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin approval turns a pending request into a real account-specific Spatie
 * role (spec §27.2 / §8.2) — roles.account_id scopes it to the requesting
 * account; role_has_permissions carries no team column, so a plain
 * syncPermissions() is enough once the Role row itself is created.
 */
class RoleRequestService
{
    public function approve(RoleRequest $request, User $admin): RoleRequest
    {
        DB::transaction(function () use ($request, $admin) {
            // Re-check status under a row lock inside the transaction — two
            // concurrent "Approve" clicks must not both create an account role
            // (admin_dashboard_workflow.md Part 13.3 / Part 19 worked example).
            $request = RoleRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only a pending request can be approved.']);
            }

            // Only permissions that are active, explicitly assignable, not
            // platform-scoped, not owner-only, and compatible with the
            // request's own capability scope may be granted — a role request
            // must never escalate into platform/sensitive/owner-only/
            // capability-incompatible permissions (Part 13.3).
            $validPermissionNames = Permission::delegatable()
                ->where('is_owner_only', false)
                ->whereIn('name', $request->requested_permissions ?? [])
                ->whereIn('capability_scope', [$request->capability_scope, 'common', 'both'])
                ->pluck('name')
                ->all();

            $role = Role::create([
                'account_id'         => $request->account_id,
                'name'                => $request->role_name,
                'guard_name'          => 'web',
                'display_name'        => $request->display_name,
                'capability_scope'    => $request->capability_scope,
                'description'         => $request->description,
                'is_system'           => false,
                'is_owner_role'       => false,
                'is_active'           => true,
                'created_by_user_id'  => $admin->id,
            ]);

            if (! empty($validPermissionNames)) {
                $role->syncPermissions($validPermissionNames);
            }

            $request->update([
                'status'              => 'approved',
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at'         => now(),
            ]);
        });

        return $request->fresh();
    }

    public function reject(RoleRequest $request, User $admin, string $comment): RoleRequest
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only a pending request can be rejected.']);
        }

        $request->update([
            'status'              => 'rejected',
            'reviewed_by_user_id' => $admin->id,
            'reviewed_at'         => now(),
            'review_comment'      => $comment,
        ]);

        return $request->fresh();
    }
}
