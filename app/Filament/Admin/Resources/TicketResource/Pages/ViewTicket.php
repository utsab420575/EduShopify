<?php

namespace App\Filament\Admin\Resources\TicketResource\Pages;

use App\Filament\Admin\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketAdminService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected string $view = 'filament.admin.resources.ticket-resource.pages.view-ticket';

    public function getMessages()
    {
        return $this->record->messages()->with('senderUser')->orderBy('created_at')->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_to_me')
                ->label('Assign to me')
                ->icon('heroicon-o-user-plus')
                ->visible(fn () => $this->record->assigned_admin_user_id !== auth()->id())
                ->action(function (TicketAdminService $service) {
                    $service->assign($this->record, auth()->user());
                    $this->record->refresh();
                    Notification::make()->title('Assigned to you.')->success()->send();
                }),

            Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('primary')
                ->schema([
                    Textarea::make('message')->label('Reply')->required()->rows(4),
                    Checkbox::make('is_internal_note')->label('Internal note (not visible to the account)'),
                ])
                ->action(function (array $data, TicketAdminService $service) {
                    $service->reply($this->record, auth()->user(), $data['message'], (bool) ($data['is_internal_note'] ?? false));
                    $this->record->refresh();
                    Notification::make()->title('Reply sent.')->success()->send();
                }),

            Action::make('resolve')
                ->label('Resolve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => ! in_array($this->record->status, ['resolved', 'closed']))
                ->requiresConfirmation()
                ->action(function (TicketAdminService $service) {
                    $service->resolve($this->record);
                    $this->record->refresh();
                    Notification::make()->title('Ticket resolved.')->success()->send();
                }),

            Action::make('close')
                ->label('Close')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->visible(fn () => $this->record->status !== 'closed')
                ->requiresConfirmation()
                ->action(function (TicketAdminService $service) {
                    $service->close($this->record);
                    $this->record->refresh();
                    Notification::make()->title('Ticket closed.')->success()->send();
                }),

            Action::make('reopen')
                ->label('Reopen')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'closed')
                ->action(function (TicketAdminService $service) {
                    $service->reopen($this->record);
                    $this->record->refresh();
                    Notification::make()->title('Ticket reopened.')->success()->send();
                }),
        ];
    }
}
