<?php

namespace App\Filament\Admin\Widgets;

use App\Models\AccountCapability;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Approval state lives in account_capabilities, so that is what this lists.
 */
class RecentApprovalsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AccountCapability::query()
                    ->with(['account', 'reviewedBy'])
                    ->whereIn('status', ['pending', 'active', 'rejected', 'revision_required'])
                    ->latest('updated_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('account.display_name')
                    ->label('Account')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('capabilityType.code')
                    ->label('Capability')
                    ->badge()
                    ->colors([
                        'primary' => 'buyer',
                        'success' => 'supplier',
                    ]),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => fn ($state): bool => in_array($state, ['pending', 'revision_required'], true),
                        'success' => 'active',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('reviewedBy.name')
                    ->label('Reviewed by')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
