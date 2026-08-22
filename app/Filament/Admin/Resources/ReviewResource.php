<?php

namespace App\Filament\Admin\Resources;

use App\Models\Review;
use App\Services\ReviewModerationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Reviews';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.reviews.moderate') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['buyerAccount.buyerProfile', 'supplierAccount.supplierProfile', 'createdBy']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('buyerAccount.buyerProfile.display_name')
                    ->label('Buyer')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('supplierAccount.supplierProfile.display_name')
                    ->label('Supplier')
                    ->searchable(),

                TextColumn::make('rating')
                    ->formatStateUsing(fn (int $state) => str_repeat('★', $state) . str_repeat('☆', 5 - $state)),

                TextColumn::make('review_context')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()),

                TextColumn::make('title')->limit(30)->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'pending'   => 'warning',
                        'flagged'   => 'danger',
                        'hidden', 'rejected' => 'gray',
                        default     => 'gray',
                    }),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'published' => 'Published',
                        'hidden'    => 'Hidden',
                        'flagged'   => 'Flagged',
                        'rejected'  => 'Rejected',
                    ]),
                SelectFilter::make('review_context')
                    ->options([
                        'quotation_experience' => 'Quotation experience',
                        'purchase_experience'  => 'Purchase experience',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Review $record) => in_array($record->status, ['pending', 'flagged']))
                    ->requiresConfirmation()
                    ->action(function (Review $record, ReviewModerationService $service) {
                        $service->publish($record, auth()->user());
                        Notification::make()->title('Review published.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Review $record) => $record->status === 'pending')
                    ->schema([Textarea::make('reason')->label('Reason')->required()])
                    ->action(function (Review $record, array $data, ReviewModerationService $service) {
                        $service->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Review rejected.')->danger()->send();
                    }),

                Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (Review $record) => $record->status === 'published')
                    ->schema([Textarea::make('reason')->label('Reason')->required()])
                    ->action(function (Review $record, array $data, ReviewModerationService $service) {
                        $service->hide($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Review hidden.')->warning()->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ReviewResource\Pages\ListReviews::route('/'),
            'view'  => ReviewResource\Pages\ViewReview::route('/{record}'),
        ];
    }
}
