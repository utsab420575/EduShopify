<?php

namespace App\Filament\Admin\Resources;

use App\Models\Setting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.settings.manage') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('group_name')->disabled(),
            TextInput::make('name')->disabled(),
            TextInput::make('value')
                ->required()
                ->helperText('true / false, a number, or free text — matched to the setting\'s current type.')
                ->afterStateHydrated(function ($component, $record) {
                    $component->state($record ? json_encode($record->payload['value'] ?? null) : null);
                }),
            Toggle::make('locked')->helperText('Locked settings are shown for reference but should not normally change.'),
        ]);
    }

    /**
     * The form's "value" field isn't dehydrated (see ->dehydrated(false) above,
     * since Setting has no such column) — EditSetting::mutateFormDataBeforeSave()
     * folds the raw value back into payload.value via this.
     */
    public static function coerce(?string $raw): mixed
    {
        $decoded = json_decode($raw ?? 'null', true);

        return $decoded === null && $raw !== 'null' ? $raw : $decoded;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group_name')->badge()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('payload')
                    ->label('Value')
                    ->formatStateUsing(fn (?array $state) => is_bool($state['value'] ?? null)
                        ? (($state['value']) ? 'true' : 'false')
                        : ($state['value'] ?? '—')),
                IconColumn::make('locked')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('group_name')
                    ->options(fn () => Setting::query()->distinct()->pluck('group_name', 'group_name')->all()),
            ])
            ->defaultSort('group_name')
            ->actions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SettingResource\Pages\ListSettings::route('/'),
            'edit'  => SettingResource\Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
