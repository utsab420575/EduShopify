<?php

namespace App\Filament\Admin\Resources;

use App\Models\CategorySuggestion;
use App\Services\CategorySuggestionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategorySuggestionResource extends Resource
{
    protected static ?string $model = CategorySuggestion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-light-bulb';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Category Suggestions';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.categories.manage') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['supplierAccount.supplierProfile', 'parentCategory']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('proposed_name')->searchable(),

                TextColumn::make('parentCategory.name')->label('Parent')->placeholder('— root —'),

                TextColumn::make('supplierAccount.supplierProfile.display_name')->label('Suggested by')->searchable(),

                TextColumn::make('proposed_type')->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected', 'withdrawn' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (CategorySuggestion $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (CategorySuggestion $record, CategorySuggestionService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('Category created.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CategorySuggestion $record) => $record->status === 'pending')
                    ->schema([Textarea::make('comment')->label('Reason')->required()])
                    ->action(function (CategorySuggestion $record, array $data, CategorySuggestionService $service) {
                        $service->reject($record, auth()->user(), $data['comment']);
                        Notification::make()->title('Suggestion rejected.')->danger()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => CategorySuggestionResource\Pages\ListCategorySuggestions::route('/'),
        ];
    }
}
