<?php

namespace App\Filament\Admin\Resources;

use App\Models\RoleRequest;
use App\Services\RoleRequestService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoleRequestResource extends Resource
{
    protected static ?string $model = RoleRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';

    protected static string | \UnitEnum | null $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Role Requests';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['account', 'requestedBy']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.display_name')->label('Account')->searchable(),

                TextColumn::make('display_name')->label('Requested role')->searchable(),

                TextColumn::make('capability_scope')->badge(),

                TextColumn::make('requestedBy.name')->label('Requested by'),

                TextColumn::make('requested_permissions')
                    ->label('Permissions')
                    ->formatStateUsing(fn (?array $state) => count($state ?? []) . ' permission(s)'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected', 'cancelled' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (RoleRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalDescription('Creates a new account-specific role with the requested permissions.')
                    ->action(function (RoleRequest $record, RoleRequestService $service) {
                        $service->approve($record, auth()->user());
                        Notification::make()->title('Role created and request approved.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (RoleRequest $record) => $record->status === 'pending')
                    ->schema([Textarea::make('comment')->label('Reason')->required()])
                    ->action(function (RoleRequest $record, array $data, RoleRequestService $service) {
                        $service->reject($record, auth()->user(), $data['comment']);
                        Notification::make()->title('Request rejected.')->danger()->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => RoleRequestResource\Pages\ListRoleRequests::route('/'),
        ];
    }
}
