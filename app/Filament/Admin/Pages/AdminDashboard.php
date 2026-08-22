<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class AdminDashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\AdminStatsWidget::class,
            \App\Filament\Admin\Widgets\MarketplaceStatsWidget::class,
            \App\Filament\Admin\Widgets\RecentApprovalsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
