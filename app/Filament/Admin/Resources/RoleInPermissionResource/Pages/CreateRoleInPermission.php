<?php

namespace App\Filament\Admin\Resources\RoleInPermissionResource\Pages;

use App\Filament\Admin\Resources\RoleInPermissionResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RbacAuditLogger;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRoleInPermission extends CreateRecord
{
    protected static string $resource = RoleInPermissionResource::class;

    protected static ?string $title = 'Add Role In Permission';

    protected function handleRecordCreation(array $data): Model
    {
        $role = Role::findOrFail($data['id']);

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
            ->title('Permissions Assigned Successfully')
            ->body("Assigned " . count($selectedPermissions) . " permissions to role '{$role->display_name}'.")
            ->success()
            ->send();

        return $role;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
