<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RbacAuditLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class RbacAuditLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static string | \UnitEnum | null $navigationGroup = 'Roles And Permission';

    protected static ?string $navigationLabel = 'Permission Audit Logs';

    protected static ?string $modelLabel = 'Permission Audit Log';

    protected static ?string $pluralModelLabel = 'Permission Audit Logs';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_staff']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('log_name', 'rbac')->with('causer');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('Performed By')
                    ->placeholder('System')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Action Summary')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('properties.action')
                    ->label('Action Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'role_created' => 'success',
                        'role_duplicated' => 'warning',
                        'permissions_synced' => 'info',
                        'role_assigned' => 'primary',
                        'role_unassigned' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('properties.ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRbacAuditLogs::route('/'),
        ];
    }
}
