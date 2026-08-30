<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleResource\Pages;
use App\Models\Role;
use App\Services\RbacAuditLogger;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | \UnitEnum | null $navigationGroup = 'Roles And Permission';

    protected static ?string $navigationLabel = 'All Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'All Roles';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_staff']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('display_name')
                ->label('Role Display Name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('name', Str::snake(Str::lower($state))))
                ->helperText('e.g. Product Manager, Procurement Supervisor'),

            TextInput::make('name')
                ->label('Role Identifier (Slug)')
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('Unique technical slug, e.g. product_manager'),

            Select::make('capability_scope')
                ->label('Capability Scope')
                ->options([
                    'both'     => 'Both (Supplier & Buyer)',
                    'supplier' => 'Supplier Only',
                    'buyer'    => 'Buyer Only',
                    'platform' => 'Platform Staff Only',
                    'common'   => 'Common',
                ])
                ->default('supplier')
                ->required(),

            Toggle::make('is_system')
                ->label('Platform System Role')
                ->helperText('System roles apply globally and are protected from deletion.')
                ->default(true),

            Toggle::make('is_active')
                ->label('Is Active')
                ->default(true),

            Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),
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
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Slug')
                    ->searchable()
                    ->color('gray')
                    ->fontFamily('mono'),

                TextColumn::make('capability_scope')
                    ->label('Scope')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'supplier' => 'success',
                        'buyer'    => 'warning',
                        'platform' => 'danger',
                        default    => 'info',
                    }),

                TextColumn::make('account_id')
                    ->label('Type')
                    ->formatStateUsing(fn ($record) => $record->account_id ? 'Custom Account' : 'Global System')
                    ->badge()
                    ->color(fn ($record) => $record->account_id ? 'gray' : 'primary'),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('capability_scope')
                    ->label('Scope')
                    ->options([
                        'supplier' => 'Supplier',
                        'buyer'    => 'Buyer',
                        'platform' => 'Platform',
                        'both'     => 'Both',
                    ]),
            ])
            ->actions([
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Role')
                    ->modalDescription('This will create a new copy of this role with all its permissions pre-populated.')
                    ->form([
                        TextInput::make('new_display_name')
                            ->label('New Role Display Name')
                            ->required()
                            ->default(fn (Role $record) => $record->display_name . ' (Copy)'),
                    ])
                    ->action(function (Role $record, array $data) {
                        $newSlug = Str::snake(Str::lower($data['new_display_name']));
                        if (Role::where('name', $newSlug)->exists()) {
                            $newSlug .= '_' . time();
                        }

                        $newRole = Role::create([
                            'name'             => $newSlug,
                            'guard_name'       => $record->guard_name,
                            'display_name'     => $data['new_display_name'],
                            'capability_scope' => $record->capability_scope,
                            'description'      => $record->description . ' (Duplicated from ' . $record->display_name . ')',
                            'is_system'        => false,
                            'is_active'        => true,
                        ]);

                        // Clone permissions
                        $permissions = $record->permissions->pluck('name')->toArray();
                        $newRole->syncPermissions($permissions);

                        RbacAuditLogger::logRoleDuplicated($record, $newRole);

                        Notification::make()
                            ->title('Role Duplicated Successfully')
                            ->body("Created role '{$newRole->display_name}' with " . count($permissions) . " permissions.")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

                DeleteAction::make()
                    ->hidden(fn (Role $record) => $record->is_system && $record->account_id === null),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
