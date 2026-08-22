<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Users';
    protected static string|\UnitEnum|null $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('phone')
                ->tel()
                ->maxLength(20),
            Select::make('status')
                ->options([
                    'pending_verification' => 'Pending verification',
                    'active'               => 'Active',
                    'inactive'             => 'Inactive',
                    'suspended'            => 'Suspended',
                    'deleted'              => 'Deleted',
                ])
                ->default('pending_verification')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('accountMember.account.display_name')
                    ->label('Account')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('accountMember.account.capabilities.capability')
                    ->label('Capabilities')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger'  => 'suspended',
                    ])
                    ->formatStateUsing(fn (string $state) => ucfirst($state ?? 'active'))
                    ->placeholder('Active'),
                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime('d M Y')
                    ->placeholder('Not verified')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('capability')
                    ->label('Capability')
                    ->options([
                        'buyer'    => 'Buyers',
                        'supplier' => 'Suppliers',
                    ])
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'accountMember.account.capabilities',
                            fn ($q) => $q->whereHas('capabilityType', fn($ct) => $ct->where('code', $data['value']))->where('status', 'active')
                        );
                    }),
                SelectFilter::make('status')
                    ->options([
                        'pending_verification' => 'Pending verification',
                        'active'               => 'Active',
                        'inactive'             => 'Inactive',
                        'suspended'            => 'Suspended',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => ($record->status ?? 'active') !== 'suspended')
                    ->action(fn (User $record) => $record->update(['status' => 'suspended'])),
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => ($record->status ?? 'active') === 'suspended')
                    ->action(fn (User $record) => $record->update(['status' => 'active'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
