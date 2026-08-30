<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PermissionResource\Pages;
use App\Models\Permission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected static string | \UnitEnum | null $navigationGroup = 'Roles And Permission';

    protected static ?string $navigationLabel = 'All Permission';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $pluralModelLabel = 'All Permissions';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_staff']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Permission Name (Slug)')
                ->required()
                ->unique(ignoreRecord: true)
                ->helperText('e.g. listing.create, quotation.submit, team.manage'),

            TextInput::make('display_name')
                ->label('Display Name')
                ->required()
                ->helperText('e.g. Create Listing, Submit Quotation'),

            TextInput::make('group_name')
                ->label('Group Name')
                ->required()
                ->datalist(fn () => Permission::distinct()->pluck('group_name')->filter()->values()->toArray())
                ->helperText('Categorization group (e.g. Catalog, RFQ, Quotations, Billing, User)'),

            Select::make('capability_scope')
                ->label('Capability Scope')
                ->options([
                    'common'   => 'Common (All Roles)',
                    'supplier' => 'Supplier',
                    'buyer'    => 'Buyer',
                    'platform' => 'Platform Staff',
                    'all'      => 'All Scopes',
                ])
                ->default('common')
                ->required(),

            Textarea::make('description')
                ->label('Description')
                ->rows(2)
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Is Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('SI')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Permission Name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('display_name')
                    ->label('Display Name')
                    ->searchable(),

                TextColumn::make('group_name')
                    ->label('Group Name')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('capability_scope')
                    ->label('Scope')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'supplier' => 'success',
                        'buyer'    => 'warning',
                        'platform' => 'danger',
                        default    => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group_name')
                    ->label('Group')
                    ->options(fn () => Permission::distinct()->pluck('group_name', 'group_name')->filter()->toArray()),

                SelectFilter::make('capability_scope')
                    ->label('Scope')
                    ->options([
                        'common'   => 'Common',
                        'supplier' => 'Supplier',
                        'buyer'    => 'Buyer',
                        'platform' => 'Platform',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index'  => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit'   => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
