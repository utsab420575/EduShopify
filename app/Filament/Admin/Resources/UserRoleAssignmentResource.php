<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserRoleAssignmentResource\Pages;
use App\Models\Role;
use App\Models\User;
use App\Services\RbacAuditLogger;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserRoleAssignmentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';

    protected static string | \UnitEnum | null $navigationGroup = 'Roles And Permission';

    protected static ?string $navigationLabel = 'User Role Assignment';

    protected static ?string $modelLabel = 'User Role';

    protected static ?string $pluralModelLabel = 'User Role Assignments';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_staff']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles', 'accountMember.account']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('User Details')
                ->schema([
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->disabled(),
                    TextInput::make('phone')->disabled(),
                ])
                ->columns(3),

            Section::make('Assign Roles')
                ->description('Select the roles that this user holds within their company/account context.')
                ->schema([
                    CheckboxList::make('roles')
                        ->relationship('roles', 'display_name')
                        ->columns(3)
                        ->gridDirection('row'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('SI')
                    ->sortable(),

                ImageColumn::make('avatar')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(fn (User $record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=6366f1&color=fff'),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('roles.display_name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Super Admin', 'Admin' => 'danger',
                        'Product Manager', 'Buyer Manager' => 'success',
                        'Primary Owner', 'Co-Owner' => 'primary',
                        default => 'info',
                    })
                    ->separator(','),

                TextColumn::make('accountMember.account.display_name')
                    ->label('Company / Account')
                    ->default('System Account')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'display_name'),
            ])
            ->actions([
                Action::make('assign_role')
                    ->label('Edit Role')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->form(function (User $record) {
                        $account = $record->accountMember?->account;
                        $accountId = $account?->id;

                        $usableRoles = Role::usableBy($accountId ?? 0)->active()->get();

                        return [
                            Select::make('role_ids')
                                ->label('Assigned Roles')
                                ->multiple()
                                ->options($usableRoles->pluck('display_name', 'id'))
                                ->default(fn () => $record->roles()->wherePivot('account_id', $accountId)->pluck('roles.id')->toArray())
                                ->required(),
                        ];
                    })
                    ->action(function (User $record, array $data) {
                        $account = $record->accountMember?->account;
                        $accountId = $account?->id;

                        // Sync roles under the user's account_id
                        $record->activateTeamContext();
                        $roles = Role::whereIn('id', $data['role_ids'])->get();
                        $record->syncRoles($roles);

                        foreach ($roles as $role) {
                            RbacAuditLogger::logRoleAssigned($record, $role, $accountId);
                        }

                        Notification::make()
                            ->title('User Roles Updated')
                            ->body("Updated role assignments for {$record->name}.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserRoleAssignments::route('/'),
        ];
    }
}
