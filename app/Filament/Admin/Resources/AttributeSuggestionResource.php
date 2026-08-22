<?php

namespace App\Filament\Admin\Resources;

use App\Models\AttributeSuggestion;
use App\Services\AttributeSuggestionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttributeSuggestionResource extends Resource
{
    protected static ?string $model = AttributeSuggestion::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-light-bulb';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Attribute Suggestions';

    protected static ?int $navigationSort = 6;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.attributes.manage') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['supplierAccount.supplierProfile', 'category']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('proposed_name')->searchable(),

                TextColumn::make('category.name')->label('Category')->placeholder('—'),

                TextColumn::make('supplierAccount.supplierProfile.display_name')->label('Suggested by')->searchable(),

                TextColumn::make('proposed_input_type')->badge(),

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
                    ->visible(fn (AttributeSuggestion $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (AttributeSuggestion $record, AttributeSuggestionService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('Attribute created.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AttributeSuggestion $record) => $record->status === 'pending')
                    ->schema([Textarea::make('comment')->label('Reason')->required()])
                    ->action(function (AttributeSuggestion $record, array $data, AttributeSuggestionService $service) {
                        $service->reject($record, auth()->user(), $data['comment']);
                        Notification::make()->title('Suggestion rejected.')->danger()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => AttributeSuggestionResource\Pages\ListAttributeSuggestions::route('/'),
        ];
    }
}
