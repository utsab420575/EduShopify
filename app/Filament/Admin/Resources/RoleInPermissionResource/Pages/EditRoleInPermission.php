<?php

namespace App\Filament\Admin\Resources\RoleInPermissionResource\Pages;

use App\Filament\Admin\Resources\RoleInPermissionResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RbacAuditLogger;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditRoleInPermission extends EditRecord
{
    protected static string $resource = RoleInPermissionResource::class;

    protected static ?string $title = 'Edit Role In Permission';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Role $role */
        $role = $this->record;
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $permissionGroups = Permission::active()->get()->groupBy('group_name');

        foreach ($permissionGroups as $groupName => $permissions) {
            $groupPerms = $permissions->pluck('name')->toArray();
            $data["permissions_group_{$groupName}"] = array_values(array_intersect($rolePermissions, $groupPerms));
        }

        $data['id'] = $role->id;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Role $role */
        $role = $record;

        $selectedPermissions = [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'permissions_group_') && is_array($value)) {
                $selectedPermissions = array_merge($selectedPermissions, $value);
            }
        }
        $selectedPermissions = array_unique($selectedPermissions);

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $role->syncPermissions($selectedPermissions);

        RbacAuditLogger::logPermissionsSynced($role, $oldPermissions, $selectedPermissions);

        Notification::make()
            ->title('Permissions Updated Successfully')
            ->body("Updated permissions for role '{$role->display_name}' (" . count($selectedPermissions) . " total).")
            ->success()
            ->send();

        return $role;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
