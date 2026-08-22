<?php

namespace App\Filament\Admin\Resources;

use App\Models\Listing;
use App\Services\ListingModerationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static string | \UnitEnum | null $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Listings';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.listings.moderate') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['supplierAccount.supplierProfile', 'mainCategory']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('listing_number')
                    ->label('Number')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('supplierAccount.supplierProfile.display_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('listing_type')
                    ->badge()
                    ->color(fn (string $state) => $state === 'product' ? 'info' : 'purple'),

                TextColumn::make('approval_status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('listing_type')
                    ->options(['product' => 'Product', 'service' => 'Service']),

                SelectFilter::make('approval_status')
                    ->options([
                        'draft'    => 'Draft',
                        'pending'  => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Listing $record) => $record->approval_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Listing $record, ListingModerationService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('Listing approved.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Listing $record) => $record->approval_status === 'pending')
                    ->schema([
                        Textarea::make('reason')->label('Rejection reason')->required(),
                    ])
                    ->action(function (Listing $record, array $data, ListingModerationService $service) {
                        $service->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Listing rejected.')->danger()->send();
                    }),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->visible(fn (Listing $record) => $record->is_active && $record->approval_status === 'approved')
                    ->schema([
                        Textarea::make('reason')->label('Suspension reason')->required(),
                    ])
                    ->action(function (Listing $record, array $data, ListingModerationService $service) {
                        $service->suspend($record, $data['reason']);
                        Notification::make()->title('Listing suspended.')->warning()->send();
                    }),

                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Listing $record) => ! $record->is_active && $record->approval_status === 'approved')
                    ->requiresConfirmation()
                    ->action(function (Listing $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()->title('Listing reactivated.')->success()->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListingResource\Pages\ListListings::route('/'),
            'view'  => ListingResource\Pages\ViewListing::route('/{record}'),
        ];
    }
}
