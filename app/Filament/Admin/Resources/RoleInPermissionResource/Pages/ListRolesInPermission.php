<?php

namespace App\Filament\Admin\Resources\RoleInPermissionResource\Pages;

use App\Filament\Admin\Resources\RoleInPermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRolesInPermission extends ListRecords
{
    protected static string $resource = RoleInPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add Role In Permission'),
        ];
    }
}
