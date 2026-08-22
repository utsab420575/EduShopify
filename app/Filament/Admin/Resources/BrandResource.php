<?php

namespace App\Filament\Admin\Resources;

use App\Models\Brand;
use App\Services\BrandModerationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Brands';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.brands.manage') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('supplierAccount.supplierProfile');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')->required()->unique(ignoreRecord: true),
            TextInput::make('website')->url(),
            Textarea::make('description'),
            Select::make('owner_type')->options(['global' => 'Global', 'supplier' => 'Supplier'])->default('global')->required(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),

                TextColumn::make('owner_type')->badge(),

                TextColumn::make('supplierAccount.supplierProfile.display_name')->label('Supplier')->placeholder('—'),

                TextColumn::make('approval_status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('owner_type')->options(['global' => 'Global', 'supplier' => 'Supplier']),
                SelectFilter::make('approval_status')->options(['approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected']),
            ])
            ->defaultSort('name')
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Brand $record) => $record->approval_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Brand $record, BrandModerationService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('Brand approved.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Brand $record) => $record->approval_status === 'pending')
                    ->schema([Textarea::make('reason')->label('Reason')->required()])
                    ->action(function (Brand $record, array $data, BrandModerationService $service) {
                        $service->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Brand rejected.')->danger()->send();
                    }),

                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => BrandResource\Pages\ListBrands::route('/'),
            'create' => BrandResource\Pages\CreateBrand::route('/create'),
            'edit'   => BrandResource\Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
