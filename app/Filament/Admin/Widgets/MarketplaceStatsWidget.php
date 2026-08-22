<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Listing;
use App\Models\Review;
use App\Models\Rfq;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Moderation queues across the parts of the marketplace this round added
 * admin control for — listings, reviews, tickets, and (when
 * rfq_requires_admin_approval is on) RFQs.
 */
class MarketplaceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $pendingListings = Listing::where('approval_status', 'pending')->count();
        $pendingReviews  = Review::whereIn('status', ['pending', 'flagged'])->count();
        $openTickets     = Ticket::whereNotIn('status', ['resolved', 'closed'])->count();
        $pendingRfqs     = Rfq::where('status', 'pending_approval')->count();

        return [
            Stat::make('Listings to Review', number_format($pendingListings))
                ->description('Pending approval')
                ->descriptionIcon('heroicon-m-cube')
                ->color($pendingListings > 0 ? 'warning' : 'success'),

            Stat::make('Reviews to Moderate', number_format($pendingReviews))
                ->description('Pending or flagged')
                ->descriptionIcon('heroicon-m-star')
                ->color($pendingReviews > 0 ? 'warning' : 'success'),

            Stat::make('Open Support Tickets', number_format($openTickets))
                ->description('Awaiting resolution')
                ->descriptionIcon('heroicon-m-lifebuoy')
                ->color($openTickets > 0 ? 'warning' : 'success'),

            Stat::make('RFQs Awaiting Approval', number_format($pendingRfqs))
                ->description('Only shown when rfq_requires_admin_approval is on')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($pendingRfqs > 0 ? 'warning' : 'success'),
        ];
    }
}
