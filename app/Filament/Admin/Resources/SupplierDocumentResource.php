<?php

namespace App\Filament\Admin\Resources;

use App\Models\SupplierDocument;
use App\Services\SupplierDocumentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class SupplierDocumentResource extends Resource
{
    protected static ?string $model = SupplierDocument::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Supplier Verification';

    protected static ?string $navigationLabel = 'Supplier Documents';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('platform.supplier_documents.verify') ?? false;
    }



    public static function table(Table $table): Table
    {
        return $table
            ->query(SupplierDocument::query()->where('is_current', true))
            ->columns([
                Tables\Columns\TextColumn::make('supplierAccount.display_name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_name')
                    ->label('Document Name')
                    ->badge()
                    ->state(fn (SupplierDocument $record) => $record->document_name),

                Tables\Columns\TextColumn::make('original_name')
                    ->label('File Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending Verification',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_file')
                    ->label('View File')
                    ->icon('heroicon-o-eye')
                    ->url(fn (SupplierDocument $record) => Storage::url($record->file_path), shouldOpenInNewTab: true),

                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SupplierDocument $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (SupplierDocument $record, SupplierDocumentService $service) {
                        $service->verify($record, auth()->user());
                        \Filament\Notifications\Notification::make()
                            ->title('Document verified successfully.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (SupplierDocument $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Specify why this compliance document is rejected...'),
                    ])
                    ->action(function (SupplierDocument $record, array $data, SupplierDocumentService $service) {
                        $service->reject($record, auth()->user(), $data['rejection_reason']);
                        \Filament\Notifications\Notification::make()
                            ->title('Document rejected.')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('reset')
                    ->label('Undo / Reset')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SupplierDocument $record) => $record->status !== 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Revert Document Decision')
                    ->modalDescription('Are you sure you want to reset this document back to Pending review?')
                    ->action(function (SupplierDocument $record, SupplierDocumentService $service) {
                        $service->resetToPending($record, auth()->user());
                        \Filament\Notifications\Notification::make()
                            ->title('Document reset to Pending.')
                            ->info()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => SupplierDocumentResource\Pages\ListSupplierDocuments::route('/'),
        ];
    }
}
