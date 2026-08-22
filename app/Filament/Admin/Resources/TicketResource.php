<?php

namespace App\Filament\Admin\Resources;

use App\Models\Ticket;
use App\Services\TicketAdminService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-lifebuoy';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Support Tickets';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.tickets.manage') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['account', 'createdBy', 'assignedAdmin']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')->searchable(),

                TextColumn::make('subject')->searchable()->limit(40),

                TextColumn::make('account.display_name')
                    ->label('Account')
                    ->searchable(),

                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'urgent' => 'danger',
                        'high'   => 'warning',
                        default  => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'open', 'pending' => 'info',
                        'answered' => 'warning',
                        'resolved' => 'success',
                        'closed'   => 'gray',
                        default    => 'gray',
                    }),

                TextColumn::make('assignedAdmin.name')
                    ->label('Assigned to')
                    ->placeholder('Unassigned'),

                TextColumn::make('last_reply_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open'     => 'Open',
                        'pending'  => 'Pending',
                        'answered' => 'Answered',
                        'resolved' => 'Resolved',
                        'closed'   => 'Closed',
                    ]),
                SelectFilter::make('priority')
                    ->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent']),
            ])
            ->defaultSort('last_reply_at', 'desc')
            ->actions([
                Action::make('assign_to_me')
                    ->label('Assign to me')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (Ticket $record) => $record->assigned_admin_user_id !== auth()->id())
                    ->action(function (Ticket $record, TicketAdminService $service) {
                        $service->assign($record, auth()->user());
                        Notification::make()->title('Ticket assigned to you.')->success()->send();
                    }),

                Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Ticket $record) => ! in_array($record->status, ['resolved', 'closed']))
                    ->requiresConfirmation()
                    ->action(function (Ticket $record, TicketAdminService $service) {
                        $service->resolve($record);
                        Notification::make()->title('Ticket resolved.')->success()->send();
                    }),

                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Ticket $record) => $record->status !== 'closed')
                    ->requiresConfirmation()
                    ->action(function (Ticket $record, TicketAdminService $service) {
                        $service->close($record);
                        Notification::make()->title('Ticket closed.')->success()->send();
                    }),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => TicketResource\Pages\ListTickets::route('/'),
            'view'  => TicketResource\Pages\ViewTicket::route('/{record}'),
        ];
    }
}
