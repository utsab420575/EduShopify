<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class RbacAuditLogger
{
    public static function logRoleCreated(Role $role, ?int $accountId = null): void
    {
        activity('rbac')
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties([
                'action'       => 'role_created',
                'account_id'   => $accountId ?? $role->account_id,
                'role_name'    => $role->name,
                'display_name' => $role->display_name,
                'is_system'    => $role->is_system,
                'ip_address'   => request()->ip(),
                'user_agent'   => request()->userAgent(),
            ])
            ->log("Role '{$role->display_name}' created.");
    }

    public static function logRoleDuplicated(Role $sourceRole, Role $newRole, ?int $accountId = null): void
    {
        activity('rbac')
            ->causedBy(Auth::user())
            ->performedOn($newRole)
            ->withProperties([
                'action'         => 'role_duplicated',
                'account_id'     => $accountId ?? $newRole->account_id,
                'source_role_id' => $sourceRole->id,
                'source_name'    => $sourceRole->display_name,
                'new_role_name'  => $newRole->display_name,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
            ])
            ->log("Role '{$newRole->display_name}' duplicated from '{$sourceRole->display_name}'.");
    }

    public static function logPermissionsSynced(Role $role, array $oldPermissions, array $newPermissions, ?int $accountId = null): void
    {
        $added = array_values(array_diff($newPermissions, $oldPermissions));
        $removed = array_values(array_diff($oldPermissions, $newPermissions));

        if (empty($added) && empty($removed)) {
            return;
        }

        activity('rbac')
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties([
                'action'              => 'permissions_synced',
                'account_id'          => $accountId ?? $role->account_id,
                'role_name'           => $role->name,
                'display_name'        => $role->display_name,
                'added_permissions'   => $added,
                'removed_permissions' => $removed,
                'ip_address'          => request()->ip(),
                'user_agent'          => request()->userAgent(),
            ])
            ->log("Permissions updated for role '{$role->display_name}'. (Added: " . count($added) . ", Removed: " . count($removed) . ")");
    }

    public static function logRoleAssigned(User $user, Role $role, ?int $accountId = null): void
    {
        activity('rbac')
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'action'       => 'role_assigned',
                'account_id'   => $accountId,
                'user_email'   => $user->email,
                'role_name'    => $role->name,
                'display_name' => $role->display_name,
                'ip_address'   => request()->ip(),
                'user_agent'   => request()->userAgent(),
            ])
            ->log("Assigned role '{$role->display_name}' to user '{$user->name}' ({$user->email}).");
    }

    public static function logRoleUnassigned(User $user, Role $role, ?int $accountId = null): void
    {
        activity('rbac')
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'action'       => 'role_unassigned',
                'account_id'   => $accountId,
                'user_email'   => $user->email,
                'role_name'    => $role->name,
                'display_name' => $role->display_name,
                'ip_address'   => request()->ip(),
                'user_agent'   => request()->userAgent(),
            ])
            ->log("Removed role '{$role->display_name}' from user '{$user->name}' ({$user->email}).");
    }

    public static function logUserPermissionOverride(User $user, array $added, array $removed, ?int $accountId = null): void
    {
        activity('rbac')
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'action'              => 'user_permission_override',
                'account_id'          => $accountId,
                'user_email'          => $user->email,
                'added_permissions'   => $added,
                'removed_permissions' => $removed,
                'ip_address'          => request()->ip(),
                'user_agent'          => request()->userAgent(),
            ])
            ->log("Direct permissions overridden for user '{$user->name}'. (Added: " . count($added) . ", Removed: " . count($removed) . ")");
    }
}
