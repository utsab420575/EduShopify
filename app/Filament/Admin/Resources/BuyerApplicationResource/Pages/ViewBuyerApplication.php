<?php

namespace App\Filament\Admin\Resources\BuyerApplicationResource\Pages;

use App\Filament\Admin\Resources\BuyerApplicationResource;
use App\Models\AccountCapability;
use App\Services\CapabilityReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewBuyerApplication extends ViewRecord
{
    protected static string $resource = BuyerApplicationResource::class;

    protected string $view = 'filament.admin.resources.buyer-application-resource.pages.view-buyer-application';

    protected function getHeaderActions(): array
    {
        /** @var AccountCapability $cap */
        $cap = $this->record;

        return [
            // ── APPROVE ───────────────────────────────────────────────────
            Action::make('approve')
                ->label('Approve Application')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Buyer Application')
                ->modalDescription('This will activate the Buyer capability for this account. The applicant will be notified.')
                ->form([
                    Textarea::make('comment')
                        ->label('Optional note to applicant')
                        ->rows(3)
                        ->placeholder('Welcome message or notes…'),
                ])
                ->action(function (array $data) use ($cap) {
                    try {
                        app(CapabilityReviewService::class)->approve(
                            $cap,
                            Auth::user(),
                            $data['comment'] ?? null
                        );
                        Notification::make()->title('Application Approved')->success()->send();
                        $this->refreshRecord();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                })
                ->visible(fn () => $cap->status === 'pending'),

            // ── REQUEST REVISION ──────────────────────────────────────────
            Action::make('request_revision')
                ->label('Request Revision')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->form([
                    Textarea::make('reason')
                        ->label('Revision instructions (required)')
                        ->rows(4)
                        ->required()
                        ->placeholder('Explain what the applicant needs to change or provide…'),
                ])
                ->modalHeading('Request Revision')
                ->action(function (array $data) use ($cap) {
                    try {
                        app(CapabilityReviewService::class)->requestRevision(
                            $cap,
                            Auth::user(),
                            $data['reason']
                        );
                        Notification::make()->title('Revision Requested')->warning()->send();
                        $this->refreshRecord();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                })
                ->visible(fn () => $cap->status === 'pending'),

            // ── REJECT ─────────────────────────────────────────────────────
            Action::make('reject')
                ->label('Reject Application')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Application')
                ->modalDescription('This will reject the Buyer application. Provide a clear reason so the applicant understands.')
                ->form([
                    Textarea::make('reason')
                        ->label('Rejection reason (required)')
                        ->rows(4)
                        ->required()
                        ->placeholder('Explain why the application cannot be approved…'),
                ])
                ->action(function (array $data) use ($cap) {
                    try {
                        app(CapabilityReviewService::class)->reject(
                            $cap,
                            Auth::user(),
                            $data['reason']
                        );
                        Notification::make()->title('Application Rejected')->danger()->send();
                        $this->refreshRecord();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                })
                ->visible(fn () => $cap->status === 'pending'),
        ];
    }

    protected function getViewData(): array
    {
        /** @var AccountCapability $cap */
        $cap     = $this->record;
        $account = $cap->account()->with([
            'primaryOwner',
            'buyerProfile.buyerType',
            'buyerProfile.country',
            'buyerProfile.state',
            'buyerProfile.city',
        ])->first();

        $history = $cap->applicationHistory()->orderBy('attempt_no', 'desc')->get();

        return compact('cap', 'account', 'history');
    }
}
