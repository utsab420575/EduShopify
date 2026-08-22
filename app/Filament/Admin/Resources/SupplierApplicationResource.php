<?php

namespace App\Filament\Admin\Resources;

use App\Models\AccountCapability;
use App\Services\CapabilityReviewService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SupplierApplicationResource extends Resource
{
    protected static ?string $model = AccountCapability::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'Supplier Verification';

    protected static ?string $navigationLabel = 'Supplier Applications';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.capabilities.review') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.display_name')
                    ->label('Supplier Account')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'            => 'success',
                        'pending'           => 'warning',
                        'revision_required' => 'info',
                        'rejected'          => 'danger',
                        default             => 'gray',
                    }),

                Tables\Columns\TextColumn::make('application_attempts')
                    ->label('Attempt #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('applied_at')
                    ->label('Applied At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'           => 'Pending Review',
                        'active'            => 'Active / Approved',
                        'revision_required' => 'Revision Required',
                        'rejected'          => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (AccountCapability $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (AccountCapability $record, CapabilityReviewService $service) {
                        try {
                            $service->approve($record, auth()->user());
                            \Filament\Notifications\Notification::make()
                                ->title('Supplier application approved successfully!')
                                ->success()
                                ->send();
                        } catch (\Illuminate\Validation\ValidationException $e) {
                            $msg = implode(' ', array_merge(...array_values($e->errors())));
                            \Filament\Notifications\Notification::make()
                                ->title('Approval Guard Blocked')
                                ->body($msg)
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('request_revision')
                    ->label('Request Revision')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (AccountCapability $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('revision_reason')
                            ->label('Revision Notes')
                            ->required()
                            ->placeholder('Specify missing details or profile corrections needed...'),
                    ])
                    ->action(function (AccountCapability $record, array $data, CapabilityReviewService $service) {
                        $service->requestRevision($record, auth()->user(), $data['revision_reason']);
                        \Filament\Notifications\Notification::make()
                            ->title('Revision requested.')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AccountCapability $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Specify reason for rejecting this Supplier capability...'),
                    ])
                    ->action(function (AccountCapability $record, array $data, CapabilityReviewService $service) {
                        $service->reject($record, auth()->user(), $data['rejection_reason']);
                        \Filament\Notifications\Notification::make()
                            ->title('Application rejected.')
                            ->danger()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SupplierApplicationResource\Pages\ListSupplierApplications::route('/'),
        ];
    }
}
