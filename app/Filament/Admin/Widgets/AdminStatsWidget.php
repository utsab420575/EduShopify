<?php

namespace App\Filament\Admin\Widgets;

use App\Models\AccountCapability;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Buyer/Supplier counts come from account_capabilities — there is no users.type
 * and no review_status on the profiles (spec rules 22 and 31).
 */
class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $buyers = AccountCapability::whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))
            ->where('status', 'active')
            ->count();

        $suppliers = AccountCapability::whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))
            ->where('status', 'active')
            ->count();

        $pending = AccountCapability::whereIn('status', ['pending', 'revision_required'])->count();

        $suspended = AccountCapability::where('status', 'suspended')->count();

        return [
            Stat::make('Active Buyers', number_format($buyers))
                ->description('Accounts with an approved Buyer capability')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([0, $buyers]),

            Stat::make('Active Suppliers', number_format($suppliers))
                ->description('Accounts with an approved Supplier capability')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success')
                ->chart([0, $suppliers]),

            Stat::make('Awaiting Review', number_format($pending))
                ->description('Capability applications needing a decision')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([0, $pending]),

            Stat::make('Suspended', number_format($suspended))
                ->description('Capabilities currently suspended')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('danger'),
        ];
    }
}
