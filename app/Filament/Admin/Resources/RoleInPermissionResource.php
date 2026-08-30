<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleInPermissionResource\Pages;
use App\Models\Permission;
use App\Models\Role;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoleInPermissionResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string | \UnitEnum | null $navigationGroup = 'Roles And Permission';

    protected static ?string $navigationLabel = 'All Roles in Permission';

    protected static ?string $modelLabel = 'Role Permissions';

    protected static ?string $pluralModelLabel = 'All Roles in Permission';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_staff']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('permissions');
    }

    public static function form(Schema $schema): Schema
    {
        $permissionGroups = Permission::active()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        $groupSections = [];

        foreach ($permissionGroups as $groupName => $permissions) {
            $options = $permissions->pluck('display_name', 'name')->toArray();
            $permNames = array_keys($options);

            $groupSections[] = Section::make($groupName ?: 'General')
                ->description(count($options) . ' permissions in this category')
                ->schema([
                    CheckboxList::make("permissions_group_{$groupName}")
                        ->label('')
                        ->options($options)
                        ->columns(3)
                        ->gridDirection('row')
                        ->bulkToggleable(),
                ])
                ->collapsible()
                ->compact();
        }

        return $schema->components([
            Section::make('Select Role')
                ->schema([
                    Select::make('id')
                        ->label('Select Role')
                        ->options(Role::orderBy('display_name')->pluck('display_name', 'id'))
                        ->required()
                        ->searchable()
                        ->live()
                        ->disabled(fn (?Role $record) => $record !== null),
                ]),

            Section::make('Role Permissions Matrix')
                ->description('Check the permissions that this role should possess by default.')
                ->schema($groupSections),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('SI')
                    ->sortable(),

                TextColumn::make('display_name')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Role $record) => $record->name),

                TextColumn::make('permissions.name')
                    ->label('Permission Name')
                    ->badge()
                    ->color('danger')
                    ->separator(',')
                    ->limitList(12)
                    ->expandableLimitedList(),
            ])
            ->actions([
                Action::make('edit_permissions')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Role $record) => Pages\EditRoleInPermission::getUrl(['record' => $record->id])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRolesInPermission::route('/'),
            'create' => Pages\CreateRoleInPermission::route('/create'),
            'edit'   => Pages\EditRoleInPermission::route('/{record}/edit'),
        ];
    }
}
