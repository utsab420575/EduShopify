<?php

namespace App\Filament\Admin\Resources;

use App\Models\Rfq;
use App\Services\RfqService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Platform oversight of RFQs — buyers create/publish their own from the Buyer
 * Dashboard; this resource exists for moderation (approve-when-required,
 * cancel-for-cause) and visibility across the whole marketplace.
 */
class RfqResource extends Resource
{
    protected static ?string $model = Rfq::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'RFQs';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.rfqs.moderate') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('buyerAccount.buyerProfile');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rfq_number')->searchable(),

                TextColumn::make('title')->searchable()->limit(40),

                TextColumn::make('buyerAccount.buyerProfile.display_name')->label('Buyer')->searchable(),

                TextColumn::make('visibility_type')->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'open'     => 'success',
                        'draft', 'pending_approval' => 'warning',
                        'awarded'  => 'info',
                        'cancelled', 'expired' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('quotations_count')->label('Quotes')->sortable(),

                TextColumn::make('quotation_deadline')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'            => 'Draft',
                        'pending_approval' => 'Pending approval',
                        'open'             => 'Open',
                        'closed'           => 'Closed',
                        'award_pending'    => 'Award pending',
                        'awarded'          => 'Awarded',
                        'cancelled'        => 'Cancelled',
                        'expired'          => 'Expired',
                        'completed'        => 'Completed',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('approve')
                    ->label('Approve & Publish')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Rfq $record) => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->action(function (Rfq $record, RfqService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('RFQ approved and published.')->success()->send();
                    }),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Rfq $record) => ! in_array($record->status, ['cancelled', 'completed', 'expired']))
                    ->schema([Textarea::make('reason')->label('Cancellation reason')->required()])
                    ->action(function (Rfq $record, array $data, RfqService $service) {
                        $service->cancel($record, $data['reason']);
                        Notification::make()->title('RFQ cancelled.')->danger()->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => RfqResource\Pages\ListRfqs::route('/'),
            'view'  => RfqResource\Pages\ViewRfq::route('/{record}'),
        ];
    }
}
