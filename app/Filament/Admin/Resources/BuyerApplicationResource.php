<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BuyerApplicationResource\Pages;
use App\Models\AccountCapability;
use App\Services\CapabilityReviewService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BuyerApplicationResource extends Resource
{
    protected static ?string $model = AccountCapability::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Buyer Applications';
    protected static string|\UnitEnum|null $navigationGroup = 'Marketplace';
    protected static ?int    $navigationSort  = 10;
    protected static ?string $slug            = 'buyer-applications';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))
            ->with(['account.primaryOwner', 'account.buyerProfile.buyerType', 'account.buyerProfile.country']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account.display_name')
                    ->label('Account')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.account_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => $state === 'organization' ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('account.buyerProfile.buyerType.name')
                    ->label('Buyer Type')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('account.buyerProfile.country.name')
                    ->label('Country')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending'           => 'warning',
                        'active'            => 'success',
                        'revision_required' => 'info',
                        'rejected'          => 'danger',
                        'draft'             => 'gray',
                        default             => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('application_attempts')
                    ->label('Attempts')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('applied_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Not submitted'),

                Tables\Columns\TextColumn::make('account.primaryOwner.email')
                    ->label('Owner Email')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'           => 'Pending',
                        'active'            => 'Active',
                        'revision_required' => 'Revision Required',
                        'rejected'          => 'Rejected',
                        'draft'             => 'Draft',
                    ]),

                Tables\Filters\SelectFilter::make('account_type')
                    ->label('Account Type')
                    ->relationship('account', 'account_type')
                    ->options([
                        'individual'   => 'Individual',
                        'organization' => 'Organisation',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('applied_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuyerApplications::route('/'),
            'view'  => Pages\ViewBuyerApplication::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
