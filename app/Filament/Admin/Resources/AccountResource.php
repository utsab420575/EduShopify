<?php

namespace App\Filament\Admin\Resources;

use App\Models\Account;
use App\Services\AccountModerationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static string | \UnitEnum | null $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Accounts';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.accounts.view') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->marketplace()->with(['capabilities.capabilityType', 'primaryOwner']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_number')->searchable()->toggleable(),

                TextColumn::make('display_name')->searchable()->sortable(),

                TextColumn::make('primaryOwner.email')->label('Owner')->searchable()->placeholder('—'),

                TextColumn::make('account_type')->badge(),

                TextColumn::make('capabilities.capabilityType.code')
                    ->label('Capabilities')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'  => 'success',
                        'draft', 'pending_approval' => 'warning',
                        'suspended', 'deletion_pending', 'deleted' => 'danger',
                        default   => 'gray',
                    }),

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('account_type')->options(['individual' => 'Individual', 'organization' => 'Organization']),
                SelectFilter::make('status')
                    ->options([
                        'draft'             => 'Draft',
                        'pending_approval'  => 'Pending approval',
                        'active'            => 'Active',
                        'inactive'          => 'Inactive',
                        'suspended'         => 'Suspended',
                        'deletion_pending'  => 'Deletion pending',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Account $record) => $record->status === 'pending_approval')
                    ->requiresConfirmation()
                    ->action(function (Account $record, AccountModerationService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('Account approved.')->success()->send();
                    }),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Account $record) => ! in_array($record->status, ['suspended', 'deleted']) && ! $record->is_system_account)
                    ->schema([Textarea::make('reason')->label('Suspension reason')->required()])
                    ->action(function (Account $record, array $data, AccountModerationService $service) {
                        $service->suspend($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Account suspended.')->danger()->send();
                    }),

                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Account $record) => $record->status === 'suspended')
                    ->requiresConfirmation()
                    ->action(function (Account $record, AccountModerationService $service) {
                        $service->reactivate($record);
                        Notification::make()->title('Account reactivated.')->success()->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => AccountResource\Pages\ListAccounts::route('/'),
            'view'  => AccountResource\Pages\ViewAccount::route('/{record}'),
        ];
    }
}
