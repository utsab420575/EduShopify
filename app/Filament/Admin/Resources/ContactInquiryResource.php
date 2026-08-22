<?php

namespace App\Filament\Admin\Resources;

use App\Models\ContactInquiry;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactInquiryResource extends Resource
{
    protected static ?string $model = ContactInquiry::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Contact Inquiries';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'admin_staff', 'support_agent']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('supplierAccount.supplierProfile');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inquiry_number')->searchable()->toggleable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('supplierAccount.supplierProfile.display_name')->label('About supplier')->placeholder('General inquiry'),
                TextColumn::make('subject')->limit(30)->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'new'     => 'warning',
                        'read'    => 'info',
                        'replied' => 'success',
                        'spam', 'closed' => 'gray',
                        default   => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'spam' => 'Spam', 'closed' => 'Closed']),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('mark_read')
                    ->label('Mark read')
                    ->icon('heroicon-o-envelope-open')
                    ->visible(fn (ContactInquiry $record) => $record->status === 'new')
                    ->action(fn (ContactInquiry $record) => $record->update([
                        'status' => 'read', 'handled_by_user_id' => auth()->id(), 'handled_at' => now(),
                    ])),

                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (ContactInquiry $record) => $record->status !== 'closed')
                    ->action(fn (ContactInquiry $record) => $record->update(['status' => 'closed', 'closed_at' => now()])),

                Action::make('mark_spam')
                    ->label('Mark spam')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (ContactInquiry $record) => $record->status !== 'spam')
                    ->action(fn (ContactInquiry $record) => $record->update(['status' => 'spam'])),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ContactInquiryResource\Pages\ListContactInquiries::route('/'),
            'view'  => ContactInquiryResource\Pages\ViewContactInquiry::route('/{record}'),
        ];
    }
}
