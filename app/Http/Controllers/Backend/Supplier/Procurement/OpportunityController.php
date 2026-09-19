<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Procurement\DeclineOpportunityRequest;
use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use App\Models\SupplierRfqAction;
use App\Services\RfqOpportunityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    use InteractsWithSupplierAccount;

    /**
     * RFQ source tabs — how the opportunity reached this supplier. Kept
     * exactly as-is and independent of the STATUS_OPTIONS / ACTIVITY_OPTIONS
     * dropdown filters below — never mixed into the same query branch.
     */
    private const FILTER_OPTIONS = [
        'all' => 'All',
        'new' => 'New',
        'direct' => 'Direct',
        'invited' => 'Invited',
        'open_matching' => 'Open Matching',
        'broadcast' => 'Broadcast',
    ];

    /**
     * RFQ Status filter — the RFQ's own lifecycle. Bucket definitions live
     * on Rfq::scopeInLifecycleBucket() / Rfq::lifecycleBucket() so the
     * table's per-row badge and this filter can never drift apart.
     */
    private const STATUS_OPTIONS = [
        'active' => 'Active',
        'expiring_soon' => 'Expiring Soon',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'closed' => 'Closed',
    ];

    /**
     * Supplier Activity filter — what THIS supplier has done on the RFQ.
     * 'viewed' / 'not_interested' read rfq_supplier_queue's current state;
     * the rest read the supplier_rfq_actions engagement log.
     */
    private const ACTIVITY_OPTIONS = [
        'viewed' => 'Viewed',
        'interested' => 'Interested',
        'not_interested' => 'Not Interested',
        'messaged' => 'Messaged',
        'preparing_quote' => 'Preparing Quote',
        'quoted' => 'Quoted',
    ];

    /**
     * Highest-priority activity wins the single "Supplier Activity" badge/
     * action-button state shown per row — a supplier who messaged AND is
     * now preparing a quote shows as "Preparing Quote", not "Messaged".
     */
    private const ACTIVITY_STAGE_PRIORITY = ['quoted', 'preparing_quote', 'not_interested', 'messaged', 'interested', 'viewed'];

    /**
     * Default sort is always "newest published first" — no dropdown for
     * this; the table's Deadline column header is a clickable sort toggle
     * between these two instead.
     */
    private const VALID_SORTS = ['published_desc', 'deadline_asc', 'deadline_desc'];

    public function index(Request $request)
    {
        $account = $this->currentAccount();
        $filter = $request->get('filter', 'all');
        $search = trim((string) $request->get('q', ''));
        $sort = in_array($request->get('sort'), self::VALID_SORTS, true) ? $request->get('sort') : 'published_desc';
        $selectedStatuses = array_intersect((array) $request->query('status', []), array_keys(self::STATUS_OPTIONS));
        $selectedActivities = array_intersect((array) $request->query('activity', []), array_keys(self::ACTIVITY_OPTIONS));

        $queueQuery = RfqSupplierQueue::where('supplier_account_id', $account->id)
            ->released()
            ->with(['rfq' => fn ($q) => $q->with(['buyerAccount.buyerProfile', 'visibilityType'])]);

        match ($filter) {
            'new' => $queueQuery->whereNull('seen_at'),
            'direct' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'direct')),
            'invited' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'invited')),
            'open_matching' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'open_matching')),
            'broadcast' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'broadcast_all')),
            default => null,
        };

        if ($search !== '') {
            $queueQuery->whereHas('rfq', fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('rfq_number', 'like', "%{$search}%"));
        }

        // Badge counts reflect the source tab + search only, not the two
        // dropdown filters being counted, so ticking a box never changes the
        // numbers next to the OTHER options in the same dropdown.
        $totalCount = (clone $queueQuery)->count();
        $statusCounts = $this->statusCounts((clone $queueQuery));
        $activityCounts = $this->activityCounts((clone $queueQuery), $account->id);

        $this->applyStatusFilter($queueQuery, $selectedStatuses);
        $this->applyActivityFilter($queueQuery, $selectedActivities, $account->id);

        // latest() on its own sorts by rfq_supplier_queue.created_at (when the
        // queue row itself was generated) rather than the RFQ's actual
        // publish time — join rfqs so sorting reflects the RFQ's own dates
        // regardless of queue-row bookkeeping.
        $queueQuery->join('rfqs', 'rfqs.id', '=', 'rfq_supplier_queue.rfq_id')->select('rfq_supplier_queue.*');

        match ($sort) {
            'deadline_asc' => $queueQuery->orderBy('rfqs.quotation_deadline', 'asc'),
            'deadline_desc' => $queueQuery->orderByDesc('rfqs.quotation_deadline'),
            default => $queueQuery->orderByDesc('rfqs.published_at'),
        };

        $opportunities = $queueQuery->paginate(15)->withQueryString();

        $rfqIds = $opportunities->getCollection()->pluck('rfq_id');
        $quotationsByRfq = $account->quotations()->whereIn('rfq_id', $rfqIds)->get()->keyBy('rfq_id');
        $stages = $this->computeActivityStages($opportunities->getCollection(), $account->id, $rfqIds);

        $tableData = [
            'opportunities' => $opportunities,
            'sort' => $sort,
            'quotationsByRfq' => $quotationsByRfq,
            'stages' => $stages,
        ];

        // Live filtering/search/sort/pagination all hit this same GET route
        // via fetch() — only the table (not the whole page) needs to come
        // back, plus the dropdown counts so they never go stale client-side.
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('backend.supplier.procurement.opportunities.partials._table', $tableData)->render(),
                'total_count' => $totalCount,
                'status_counts' => $statusCounts,
                'activity_counts' => $activityCounts,
            ]);
        }

        return view('backend.supplier.procurement.opportunities.index', $tableData + [
            'account' => $account,
            'user' => $this->currentUser(),
            'filter' => $filter,
            'search' => $search,
            'filterOptions' => self::FILTER_OPTIONS,
            'statusOptions' => self::STATUS_OPTIONS,
            'activityOptions' => self::ACTIVITY_OPTIONS,
            'selectedStatuses' => $selectedStatuses,
            'selectedActivities' => $selectedActivities,
            'totalCount' => $totalCount,
            'statusCounts' => $statusCounts,
            'activityCounts' => $activityCounts,
        ]);
    }

    /**
     * OR's the RFQ's own lifecycle state against every checked bucket.
     * Empty selection (or none checked) means no restriction at all.
     */
    private function applyStatusFilter(Builder $queueQuery, array $selectedStatuses): void
    {
        if (empty($selectedStatuses)) {
            return;
        }

        $queueQuery->whereHas('rfq', function ($q) use ($selectedStatuses) {
            $q->where(function ($outer) use ($selectedStatuses) {
                foreach ($selectedStatuses as $status) {
                    $outer->orWhere(fn ($inner) => $inner->inLifecycleBucket($status));
                }
            });
        });
    }

    /**
     * OR's the supplier's own engagement signals against every checked box.
     * Empty selection falls back to the original default: hide RFQs this
     * supplier has already marked Not Interested.
     */
    private function applyActivityFilter(Builder $queueQuery, array $selectedActivities, int $supplierAccountId): void
    {
        if (empty($selectedActivities)) {
            $queueQuery->where('rfq_supplier_queue.status', '!=', 'ignored');

            return;
        }

        $queueQuery->where(function ($outer) use ($selectedActivities, $supplierAccountId) {
            foreach ($selectedActivities as $activity) {
                $outer->orWhere(fn ($inner) => $this->scopeActivityBucket($inner, $activity, $supplierAccountId));
            }
        });
    }

    private function scopeActivityBucket($query, string $bucket, int $supplierAccountId): void
    {
        match ($bucket) {
            'viewed' => $query->whereNotNull('rfq_supplier_queue.seen_at'),
            'not_interested' => $query->where('rfq_supplier_queue.status', 'ignored'),
            default => $query->whereHas(
                'rfq.supplierActions',
                fn ($q) => $q->where('supplier_account_id', $supplierAccountId)->where('action_type', $bucket)
            ),
        };
    }

    private function statusCounts(Builder $queueQuery): array
    {
        $counts = [];
        foreach (array_keys(self::STATUS_OPTIONS) as $bucket) {
            $counts[$bucket] = (clone $queueQuery)->whereHas('rfq', fn ($q) => $q->inLifecycleBucket($bucket))->count();
        }

        return $counts;
    }

    private function activityCounts(Builder $queueQuery, int $supplierAccountId): array
    {
        $counts = [];
        foreach (array_keys(self::ACTIVITY_OPTIONS) as $bucket) {
            $counts[$bucket] = (clone $queueQuery)->where(fn ($q) => $this->scopeActivityBucket($q, $bucket, $supplierAccountId))->count();
        }

        return $counts;
    }

    /**
     * One DB query (not one per row) to determine each RFQ's single
     * "current stage" for this supplier — the table's Activity badge and
     * the dynamic action buttons both key off this. Priority order is
     * ACTIVITY_STAGE_PRIORITY; a row with no matching action at all and an
     * unseen queue entry falls back to 'new'.
     *
     * @return array<int, string> rfq_id => stage
     */
    private function computeActivityStages($queueRows, int $supplierAccountId, $rfqIds): array
    {
        $actionsByRfq = SupplierRfqAction::where('supplier_account_id', $supplierAccountId)
            ->whereIn('rfq_id', $rfqIds)
            ->get(['rfq_id', 'action_type'])
            ->groupBy('rfq_id')
            ->map(fn ($rows) => $rows->pluck('action_type')->unique());

        $stages = [];
        foreach ($queueRows as $row) {
            $actions = $actionsByRfq->get($row->rfq_id, collect());
            $has = fn (string $type) => match ($type) {
                'quoted' => $row->status === 'quotation_submitted' || $actions->contains('quoted'),
                'not_interested' => $row->status === 'ignored' || $actions->contains('not_interested'),
                'viewed' => $row->seen_at !== null,
                default => $actions->contains($type),
            };

            $stage = collect(self::ACTIVITY_STAGE_PRIORITY)->first($has) ?? 'new';
            $stages[$row->rfq_id] = $stage;
        }

        return $stages;
    }

    public function show(Rfq $rfq, RfqOpportunityService $service)
    {
        $this->authorize('viewAsOpportunity', $rfq);

        $account = $this->currentAccount();

        $rfq->load([
            'buyerAccount.buyerProfile',
            'items.unit', 'items.category', 'items.media', 'items.listing.attributeValues.attribute', 'items.listing.media', 'items.attributeValues.attribute.unit', 'items.attributeValues.attributeValue',
            'questions' => fn ($q) => $q->where('status', 'answered')->orWhere('supplier_account_id', $account->id),
        ]);

        $service->markSeen($rfq, $account);

        $existingQuotation = $account->quotations()->where('rfq_id', $rfq->id)->with(['items', 'revisions'])->first();

        $queueRow = RfqSupplierQueue::where('rfq_id', $rfq->id)->where('supplier_account_id', $account->id)->first();

        return view('backend.supplier.procurement.opportunities.show', [
            'account' => $account,
            'user' => $this->currentUser(),
            'rfq' => $rfq,
            'existingQuotation' => $existingQuotation,
            'queueRow' => $queueRow,
        ]);
    }

    public function askQuestion(Request $request, Rfq $rfq, RfqOpportunityService $service)
    {
        $this->authorize('askQuestion', $rfq);

        $account = $this->currentAccount();

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $service->askQuestion($rfq, $account, $this->currentUser(), $validated['question']);

        return redirect()->route('supplier.opportunities.show', $rfq)->with('success', 'Your question has been sent to the buyer.');
    }

    public function decline(DeclineOpportunityRequest $request, Rfq $rfq, RfqOpportunityService $service)
    {
        $this->authorize('decline', $rfq);

        $service->decline($rfq, $this->currentAccount(), $request->validated()['reason'] ?? null);

        return redirect()->back()->with('success', 'You have declined this RFQ opportunity.');
    }

    public function interested(Rfq $rfq, RfqOpportunityService $service)
    {
        $this->authorize('markInterested', $rfq);

        $service->markInterested($rfq, $this->currentAccount());

        return redirect()->back()->with('success', 'Marked as interested.');
    }
}
