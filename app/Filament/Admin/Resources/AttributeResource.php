<?php

namespace App\Filament\Admin\Resources;

use App\Models\Attribute;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Attributes';

    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.attributes.manage') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Select::make('input_type')
                ->options([
                    'text' => 'Text', 'textarea' => 'Textarea', 'number' => 'Number',
                    'select' => 'Select', 'multi_select' => 'Multi-select',
                    'boolean' => 'Boolean', 'date' => 'Date', 'color' => 'Color',
                ])
                ->required()
                ->live(),
            TextInput::make('placeholder'),
            Toggle::make('is_filterable')->label('Filterable'),
            Toggle::make('is_variant')->label('Usable as a variant'),
            Toggle::make('is_required')->label('Required'),
            Toggle::make('is_active')->default(true),

            Repeater::make('values')
                ->relationship()
                ->visible(fn (callable $get) => in_array($get('input_type'), ['select', 'multi_select', 'color']))
                ->schema([
                    TextInput::make('value')->required(),
                    TextInput::make('slug')->required(),
                    TextInput::make('color_hex')->label('Color (hex)')->placeholder('#000000'),
                ])
                ->columns(3)
                ->addActionLabel('Add value')
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('input_type')->badge(),
                IconColumn::make('is_filterable')->boolean()->toggleable(),
                IconColumn::make('is_variant')->boolean()->toggleable(),
                IconColumn::make('is_required')->boolean()->toggleable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('values_count')->counts('values')->label('Values'),
            ])
            ->filters([
                SelectFilter::make('input_type')
                    ->options([
                        'text' => 'Text', 'textarea' => 'Textarea', 'number' => 'Number',
                        'select' => 'Select', 'multi_select' => 'Multi-select',
                        'boolean' => 'Boolean', 'date' => 'Date', 'color' => 'Color',
                    ]),
            ])
            ->defaultSort('sort_order')
            ->actions([EditAction::make()])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => AttributeResource\Pages\ListAttributes::route('/'),
            'create' => AttributeResource\Pages\CreateAttribute::route('/create'),
            'edit'   => AttributeResource\Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
