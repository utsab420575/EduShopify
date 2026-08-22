<?php

namespace App\Filament\Admin\Resources\SupplierApplicationResource\Pages;

use App\Filament\Admin\Resources\SupplierApplicationResource;
use App\Mail\SupplierApproved;
use App\Mail\SupplierRejected;
use App\Models\AccountCapability;
use App\Models\CapabilityApplicationHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Admin review of a Supplier capability application.
 *
 * Every decision writes account_capabilities and appends an immutable row to
 * capability_application_history (spec section 9.2). Approving the capability
 * does NOT grant a plan — the supplier selects one afterwards (spec rule 29).
 */
class ViewSupplierApplication extends ViewRecord
{
    protected static string $resource = SupplierApplicationResource::class;

    /**
     * Statuses on which a decision can still be taken.
     */
    private const DECIDABLE = ['pending', 'revision_required', 'draft'];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve the Supplier capability')
                ->modalDescription('The account becomes an active supplier. They will then choose a subscription plan before they can publish listings or see RFQs.')
                ->visible(fn (): bool => $this->decidable())
                ->action(function (): void {
                    $this->decide('active', function (AccountCapability $capability): void {
                        $capability->update([
                            'status'              => 'active',
                            'activated_at'        => now(),
                            'rejection_reason'    => null,
                            'revision_reason'     => null,
                        ]);
                    }, 'approved');

                    $this->notifySupplier(SupplierApproved::class);

                    Notification::make()
                        ->title('Supplier approved')
                        ->body('They can now select a subscription plan.')
                        ->success()
                        ->send();
                }),

            Action::make('request_revision')
                ->label('Request Revision')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->visible(fn (): bool => $this->decidable())
                ->schema([
                    Textarea::make('revision_reason')
                        ->label('What does the supplier need to change?')
                        ->placeholder('e.g. The trade licence has expired — please re-upload a current copy.')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    $this->decide('revision_required', function (AccountCapability $capability) use ($data): void {
                        $capability->update([
                            'status'          => 'revision_required',
                            'revision_reason' => $data['revision_reason'],
                        ]);
                    }, 'revision_required', $data['revision_reason']);

                    Notification::make()
                        ->title('Revision requested')
                        ->warning()
                        ->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->decidable())
                ->schema([
                    Textarea::make('rejection_reason')
                        ->label('Rejection reason')
                        ->placeholder('e.g. The application does not meet marketplace requirements.')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    $this->decide('rejected', function (AccountCapability $capability) use ($data): void {
                        $capability->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }, 'rejected', $data['rejection_reason']);

                    $this->notifySupplier(SupplierRejected::class);

                    Notification::make()
                        ->title('Application rejected')
                        ->danger()
                        ->send();
                }),

            Action::make('suspend')
                ->label('Suspend')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === 'active')
                ->schema([
                    Textarea::make('suspension_reason')
                        ->label('Suspension reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status'            => 'suspended',
                        'suspension_reason' => $data['suspension_reason'],
                        'suspended_at'      => now(),
                        'reviewed_by_user_id' => Auth::id(),
                        'reviewed_at'       => now(),
                    ]);

                    $this->refreshFormData(['status']);

                    Notification::make()->title('Supplier capability suspended')->danger()->send();
                }),
        ];
    }

    private function decidable(): bool
    {
        return in_array($this->record->status, self::DECIDABLE, true);
    }

    /**
     * Apply a decision and append the immutable history row in one transaction.
     */
    private function decide(string $status, callable $mutate, string $historyStatus, ?string $comment = null): void
    {
        DB::transaction(function () use ($mutate, $historyStatus, $comment): void {
            /** @var AccountCapability $capability */
            $capability = $this->record;

            $capability->forceFill([
                'reviewed_by_user_id' => Auth::id(),
                'reviewed_at'         => now(),
            ]);

            $mutate($capability);

            $attempt = max(1, (int) $capability->application_attempts);

            CapabilityApplicationHistory::updateOrCreate(
                [
                    'account_capability_id' => $capability->id,
                    'attempt_no'            => $attempt,
                ],
                [
                    'submitted_snapshot'  => $this->snapshot($capability),
                    'status'              => $historyStatus,
                    'reviewed_by_user_id' => Auth::id(),
                    'review_comment'      => $comment,
                    'reviewed_at'         => now(),
                ]
            );
        });

        $this->record->refresh();
        $this->refreshFormData(['status']);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(AccountCapability $capability): array
    {
        $account = $capability->account;
        $profile = $account?->supplierProfile;

        return [
            'account_id'      => $account?->id,
            'account_number'  => $account?->account_number,
            'display_name'    => $account?->display_name,
            'legal_name'      => $profile?->legal_name,
            'company_type'    => $profile?->company_type,
            'contact_email'   => $profile?->contact_email,
            'contact_phone'   => $profile?->contact_phone,
            'website'         => $profile?->website,
            'country_id'      => $profile?->country_id,
            'documents_count' => $account?->supplierDocuments()->count(),
            'captured_at'     => now()->toIso8601String(),
        ];
    }

    private function notifySupplier(string $mailable): void
    {
        $recipient = $this->record->account?->primaryOwner?->email
            ?? $this->record->account?->supplierProfile?->contact_email;

        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send(new $mailable($this->record));
        } catch (\Throwable $e) {
            Log::warning('Supplier decision email failed: ' . $e->getMessage());
        }
    }
}
