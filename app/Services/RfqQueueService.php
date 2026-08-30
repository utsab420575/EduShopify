<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use App\Models\RfqTargetFilter;
use App\Models\User;
use App\Notifications\DashboardNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Fans an RFQ out to rfq_supplier_queue on publish — the gate that drives
 * everything on the supplier side of the marketplace (spec §18.5).
 *
 * The two "open" visibility codes are deliberately NOT treated the same:
 * - broadcast_all reaches every active-subscription supplier, unfiltered.
 * - open_matching narrows that same pool by the RFQ's rfq_target_filters
 *   row (category, via supplier_categories — "what a supplier is capable
 *   of supplying," not just what they currently have listed — and,
 *   optionally, location, via supplier_service_areas). An open_matching
 *   RFQ with no target filter row degrades to the unfiltered pool rather
 *   than matching nobody.
 *
 * direct/invited RFQs reach only the invited suppliers with no delay and no
 * matching applied, per the buyer's explicit selection.
 */
class RfqQueueService
{
    public function generateForRfq(Rfq $rfq): void
    {
        DB::transaction(function () use ($rfq) {
            if ($rfq->isOpenMarketplace()) {
                $this->queueOpen($rfq);
            } else {
                $this->queueSelected($rfq);
            }
        });

        $this->notifyQueuedSuppliers($rfq);
    }

    /**
     * One-shot: generateForRfq() only ever runs once per RFQ, at the moment
     * it's published (RfqService::publish()/approve()), so it's safe to
     * notify every supplier just fanned into the queue without risking
     * duplicate notifications on a later call.
     */
    private function notifyQueuedSuppliers(Rfq $rfq): void
    {
        $supplierAccountIds = RfqSupplierQueue::where('rfq_id', $rfq->id)->pluck('supplier_account_id');

        if ($supplierAccountIds->isEmpty()) {
            return;
        }

        $users = User::whereHas('accountMember', fn ($q) => $q->whereIn('account_id', $supplierAccountIds)->where('status', 'active'))->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new DashboardNotification(
                "New RFQ opportunity: \"{$rfq->title}\".",
                route('supplier.opportunities.show', $rfq)
            ));
        }
    }

    private function queueOpen(Rfq $rfq): void
    {
        $code = $rfq->getRelationValue('visibilityType')?->code;
        $filter = $code === 'open_matching' ? RfqTargetFilter::where('rfq_id', $rfq->id)->first() : null;

        $query = Account::query()
            ->marketplace()
            ->where('status', 'active')
            ->whereHas('capabilities', fn ($q) => $q->where('status', 'active')->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'supplier')))
            ->with('activeSubscription.plan');

        if ($filter) {
            $this->applyCategoryMatch($query, $filter);
            $this->applyLocationMatch($query, $filter, $rfq);
        }

        $query->chunkById(200, function ($suppliers) use ($rfq) {
            foreach ($suppliers as $supplierAccount) {
                $this->enqueue($rfq, $supplierAccount, 'global');
            }
        });
    }

    private function applyCategoryMatch(Builder $query, RfqTargetFilter $filter): void
    {
        if (! $filter->category_id) {
            return;
        }

        $category = Category::find($filter->category_id);
        if (! $category) {
            return;
        }

        $categoryIds = array_merge([$category->id], $category->descendantIds());

        $query->whereHas('supplierCategories', fn ($q) => $q->active()->whereIn('category_id', $categoryIds));
    }

    /**
     * A broader supplier service area satisfies a narrower RFQ location
     * requirement (a supplier covering the whole country qualifies for a
     * city-level request), never the other way round. Radius-based areas
     * are only checked at city level, matched against the RFQ's own
     * delivery coordinates — target filters don't carry a separate point.
     */
    private function applyLocationMatch(Builder $query, RfqTargetFilter $filter, Rfq $rfq): void
    {
        $level = $filter->location_match_level ?? 'none';

        if ($level === 'none') {
            return;
        }

        $query->whereHas('serviceAreas', function (Builder $q) use ($level, $filter, $rfq) {
            $q->where('is_active', true);

            $q->where(function (Builder $q2) use ($level, $filter, $rfq) {
                // A country-level coverage row satisfies any target level —
                // but only a row whose OWN area_level is 'country' counts;
                // a state/city row incidentally sharing the same country_id
                // is not "country-wide" coverage and must not match here.
                if ($filter->country_id) {
                    $q2->orWhere(fn (Builder $q3) => $q3->where('area_level', 'country')->where('country_id', $filter->country_id));
                }

                if (($level === 'state' || $level === 'city') && $filter->state_id) {
                    $q2->orWhere(fn (Builder $q3) => $q3->where('area_level', 'state')->where('state_id', $filter->state_id));
                }

                if ($level === 'city') {
                    if ($filter->city_id) {
                        $q2->orWhere(fn (Builder $q3) => $q3->where('area_level', 'city')->where('city_id', $filter->city_id));
                    }

                    $lat = $rfq->delivery_latitude;
                    $lng = $rfq->delivery_longitude;
                    if ($lat !== null && $lng !== null) {
                        $q2->orWhere(function (Builder $q3) use ($lat, $lng) {
                            $q3->where('area_level', 'radius')
                                ->whereNotNull('center_latitude')
                                ->whereNotNull('center_longitude')
                                ->whereRaw(
                                    '6371 * acos(cos(radians(?)) * cos(radians(center_latitude)) * cos(radians(center_longitude) - radians(?)) + sin(radians(?)) * sin(radians(center_latitude))) <= radius_km',
                                    [$lat, $lng, $lat]
                                );
                        });
                    }
                }
            });
        });
    }

    private function queueSelected(Rfq $rfq): void
    {
        $rfq->invitedSupplierAccounts()
            ->with('activeSubscription.plan')
            ->get()
            ->each(function ($supplierAccount) use ($rfq) {
                $this->enqueue($rfq, $supplierAccount, 'selected_supplier', delayMinutes: 0);
            });
    }

    private function enqueue(Rfq $rfq, Account $supplierAccount, string $source, ?int $delayMinutes = null): void
    {
        $subscription = $supplierAccount->activeSubscription;
        $plan = $subscription?->plan;

        $eligibilityStatus = match (true) {
            ! $subscription => 'no_subscription',
            $subscription->expires_at && $subscription->expires_at->isPast() => 'subscription_expired',
            default => 'eligible',
        };

        $delay = $delayMinutes ?? (int) ($plan?->rfq_delay_minutes ?? 0);

        RfqSupplierQueue::updateOrCreate(
            ['rfq_id' => $rfq->id, 'supplier_account_id' => $supplierAccount->id],
            [
                'subscription_id'      => $subscription?->id,
                'subscription_plan_id' => $plan?->id,
                'source'               => $source,
                'delay_minutes'        => $delay,
                'available_at'         => now()->addMinutes($delay),
                'eligibility_status'   => $eligibilityStatus,
                'status'               => 'pending',
            ]
        );
    }
}
