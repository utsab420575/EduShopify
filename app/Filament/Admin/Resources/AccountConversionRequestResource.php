<?php

namespace App\Filament\Admin\Resources;

use App\Models\AccountConversionRequest;
use App\Services\AccountConversionService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountConversionRequestResource extends Resource
{
    protected static ?string $model = AccountConversionRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string | \UnitEnum | null $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Conversion Requests';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.conversions.review') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['account', 'submittedBy']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.display_name')->label('Current name')->searchable(),

                TextColumn::make('proposed_display_name')->label('Proposed name')->searchable(),

                TextColumn::make('submittedBy.name')->label('Submitted by'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected', 'cancelled' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('submitted_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'            => 'Pending',
                        'approved'           => 'Approved',
                        'rejected'           => 'Rejected',
                        'revision_required'  => 'Revision required',
                        'cancelled'          => 'Cancelled',
                    ]),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (AccountConversionRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('The account becomes an organization. If it holds an active Supplier capability, that capability is reset to pending and must be re-approved.')
                    ->action(function (AccountConversionRequest $record, AccountConversionService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('Conversion approved.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AccountConversionRequest $record) => $record->status === 'pending')
                    ->schema([Textarea::make('comment')->label('Reason')->required()])
                    ->action(function (AccountConversionRequest $record, array $data, AccountConversionService $service) {
                        $service->reject($record, auth()->user(), $data['comment']);
                        Notification::make()->title('Conversion rejected.')->danger()->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => AccountConversionRequestResource\Pages\ListAccountConversionRequests::route('/'),
        ];
    }
}
