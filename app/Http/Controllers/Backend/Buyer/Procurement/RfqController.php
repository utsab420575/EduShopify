<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Procurement\CancelRfqRequest;
use App\Http\Requests\Backend\Buyer\Procurement\ExtendRfqDeadlineRequest;
use App\Http\Requests\Backend\Buyer\Procurement\RfqAutosaveRequest;
use App\Http\Requests\Backend\Buyer\Procurement\SaveRfqRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\RfqQuestion;
use App\Models\Unit;
use App\Services\RfqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RfqController extends Controller
{
    use InteractsWithBuyerAccount;

    /**
     * Visibility-type tabs — how the buyer chose to reach suppliers.
     * Mirrors the supplier opportunities page's source tabs (§17.1 in
     * docs/AI/design.md), just from the authoring side of the same
     * visibility_type concept instead of the receiving side.
     */
    private const VISIBILITY_FILTER_OPTIONS = [
        'all' => 'All',
        'direct' => 'Direct',
        'invited' => 'Invited',
        'open_matching' => 'Open Matching',
        'broadcast_all' => 'Broadcast',
    ];

    private const VALID_SORTS = ['created_desc', 'deadline_asc', 'deadline_desc'];

    public function index(Request $request)
    {
        $account = $this->currentAccount();
        $filter = in_array($request->get('filter'), array_keys(self::VISIBILITY_FILTER_OPTIONS), true) ? $request->get('filter') : 'all';
        $search = trim((string) $request->get('q', ''));
        $sort = in_array($request->get('sort'), self::VALID_SORTS, true) ? $request->get('sort') : 'created_desc';
        $selectedStatuses = array_intersect((array) $request->query('status', []), array_keys($this->statusOptions()));

        $rfqsQuery = $account->rfqs()
            ->when($filter !== 'all', fn ($q) => $q->whereHas('visibilityType', fn ($q2) => $q2->where('code', $filter)))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($q2) => $q2->where('title', 'like', "%{$search}%")->orWhere('rfq_number', 'like', "%{$search}%"));
            });

        // Counts reflect the tab + search only, not the status checkboxes
        // themselves being counted — ticking a box must never change the
        // numbers next to the OTHER boxes in the same dropdown.
        $statusCounts = (clone $rfqsQuery)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $rfqsQuery->when(! empty($selectedStatuses), fn ($q) => $q->whereIn('status', $selectedStatuses))
            ->withCount('quotations')
            ->with('latestAward');

        match ($sort) {
            'deadline_asc' => $rfqsQuery->orderBy('quotation_deadline', 'asc'),
            'deadline_desc' => $rfqsQuery->orderByDesc('quotation_deadline'),
            default => $rfqsQuery->latest(),
        };

        $rfqs = $rfqsQuery->paginate(10)->withQueryString();

        $tableData = ['rfqs' => $rfqs, 'sort' => $sort];

        // Live filtering/search/sort/pagination all hit this same GET route
        // via fetch() — only the table (not the whole page) needs to come
        // back, plus the dropdown counts so they never go stale client-side.
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('backend.buyer.procurement.rfqs.partials._table', $tableData)->render(),
                'status_counts' => $statusCounts,
            ]);
        }

        return view('backend.buyer.procurement.rfqs.index', $tableData + [
            'filter' => $filter,
            'search' => $search,
            'filterOptions' => self::VISIBILITY_FILTER_OPTIONS,
            'statusOptions' => $this->statusOptions(),
            'selectedStatuses' => $selectedStatuses,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Rfq::class);

        $preselectedSupplier = null;
        $itemAttributeValues = [];
        $invitedSuppliers = collect();

        if ($request->filled('supplier')) {
            $preselectedSupplier = Account::with('supplierProfile')->find($request->integer('supplier'));
        }

        $items = collect();

        if ($request->filled('listings')) {
            // Bulk RFQ from the /compare page — one item per compared
            // product, which may span several different suppliers.
            $listingIds = collect(explode(',', (string) $request->query('listings')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values();

            $listings = Listing::published()->whereIn('id', $listingIds)->get()->keyBy('id');

            $items = $listingIds
                ->map(fn ($id) => $listings->get($id))
                ->filter()
                ->values();

            $items->each(function (Listing $listing, int $idx) use (&$itemAttributeValues) {
                $itemAttributeValues[$idx] = $this->listingAttributeValuesForPrefill($listing);
            });

            $items = $items->map(function (Listing $listing) {
                $imageUrl = $listing->primaryImage?->getUrl()
                    ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
                    ?? $listing->getFirstMediaUrl('gallery')
                    ?: null;

                return (object) [
                    'id' => null,
                    'item_type' => $listing->listing_type,
                    'listing_id' => $listing->id,
                    'category_id' => $listing->main_category_id,
                    'category_name' => $listing->mainCategory?->name,
                    'item_name' => $listing->name,
                    'description' => $listing->short_description,
                    'quantity' => (string) ($listing->min_order_quantity ?: 1),
                    'unit_id' => $listing->unit_id,
                    'custom_unit' => null,
                    'estimated_unit_price' => $listing->base_price,
                    'listing_image_url' => $imageUrl,
                    'specs' => [],
                ];
            });

            $involvedSuppliers = $listings->values()
                ->map(fn ($listing) => $listing->supplierAccount()->with('supplierProfile')->first())
                ->filter()
                ->unique('id')
                ->values();

            if ($involvedSuppliers->count() === 1) {
                // Every compared product happens to come from the same
                // supplier — behaves exactly like the single-listing flow.
                $preselectedSupplier = $involvedSuppliers->first();
            } elseif ($involvedSuppliers->count() > 1) {
                // Spans several suppliers: no single "direct" target makes
                // sense — invite exactly the suppliers involved instead of
                // silently excluding some of them or opening it to everyone.
                $invitedSuppliers = $involvedSuppliers;
            }
        } elseif ($request->filled('listing')) {
            $listing = Listing::published()->find($request->integer('listing'));

            if ($listing) {
                $imageUrl = $listing->primaryImage?->getUrl()
                    ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
                    ?? $listing->getFirstMediaUrl('gallery')
                    ?: null;

                $items = collect([(object) [
                    'id' => null,
                    'item_type' => $listing->listing_type,
                    'listing_id' => $listing->id,
                    'category_id' => $listing->main_category_id,
                    'category_name' => $listing->mainCategory?->name,
                    'item_name' => $listing->name,
                    'description' => $listing->short_description,
                    'quantity' => (string) ($listing->min_order_quantity ?: 1),
                    'unit_id' => $listing->unit_id,
                    'custom_unit' => null,
                    'estimated_unit_price' => $listing->base_price,
                    'listing_image_url' => $imageUrl,
                    'specs' => [],
                ]]);

                $itemAttributeValues[0] = $this->listingAttributeValuesForPrefill($listing);

                $preselectedSupplier ??= $listing->supplierAccount()->with('supplierProfile')->first();
            }
        }

        if ($preselectedSupplier) {
            $invitedSuppliers = collect([$preselectedSupplier]);
        }

        // A product-page "Request Quotation" starts the RFQ locked to that
        // one supplier by default (spec §11) — the buyer can still switch
        // to Selected Suppliers or Open to Eligible Suppliers from the form.
        // A multi-supplier bulk RFQ from /compare defaults to "Invited"
        // instead, pre-filled with exactly the suppliers involved.
        $defaultVisibility = match (true) {
            (bool) $preselectedSupplier => \App\Models\VisibilityType::where('code', 'direct')->first(),
            $invitedSuppliers->isNotEmpty() => \App\Models\VisibilityType::where('code', 'invited')->first(),
            default => \App\Models\VisibilityType::where('code', 'open_matching')->first(),
        };

        return view('backend.buyer.procurement.rfqs.create', [
            'rfq' => new Rfq([
                'visibility_type_id' => $defaultVisibility?->id,
                'allow_partial_quotation' => true,
                'allow_alternative_products' => true,
            ]),
            'items' => $items,
            'itemAttributeValues' => $itemAttributeValues,
            'targetFilter' => null,
            'invitedSuppliers' => $invitedSuppliers,
        ] + $this->lookups());
    }

    /**
     * GET buyer/rfqs/categories/{category}/attributes — same JSON shape the
     * supplier listing wizard's equivalent endpoint returns, so the item
     * attribute form can be a near-direct reuse of that Alpine component.
     *
     * keep_attribute_ids: comma-separated attribute ids the item already
     * has a saved value for (editing an existing RFQ) — so a since-
     * deactivated attribute still shows instead of silently disappearing.
     */
    public function categoryAttributes(Request $request, Category $category)
    {
        $keepIds = $this->parseKeepAttributeIds($request);

        return response()->json($category->attributesGroupedForForm($keepIds));
    }

    private function parseKeepAttributeIds(Request $request): array
    {
        return collect(explode(',', (string) $request->query('keep_attribute_ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * GET buyer/rfqs/listings/search?q= — typeahead for "select an existing
     * marketplace listing" when adding an RFQ item (spec §4 Option A).
     */
    public function searchListings(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $listings = Listing::published()
            ->where('name', 'like', '%'.$request->string('q').'%')
            ->with('mainCategory', 'unit')
            ->limit(15)
            ->get()
            ->map(fn (Listing $l) => [
                'id' => $l->id,
                'name' => $l->name,
                'listing_type' => $l->listing_type,
                'category_id' => $l->main_category_id,
                'category_name' => $l->mainCategory?->name,
            ]);

        return response()->json($listings);
    }

    /**
     * GET buyer/rfqs/listings/{listing}/prefill — one round trip covering
     * spec §4 Option A + §7 (existing listing, buyer may still override):
     * the item fields, that category's attribute form definition, and the
     * listing's own current attribute values as the buyer's starting point.
     */
    public function listingPrefill(Listing $listing)
    {
        abort_unless($listing->approval_status === 'approved', 404);

        $existingValues = $this->listingAttributeValuesForPrefill($listing);

        $categoryAttributes = $listing->main_category_id
            ? $listing->mainCategory?->attributesGroupedForForm(array_keys($existingValues))
            : null;

        $imageUrl = $listing->primaryImage?->getUrl()
            ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
            ?? $listing->getFirstMediaUrl('gallery')
            ?: null;

        return response()->json([
            'item' => [
                'item_type' => $listing->listing_type,
                'listing_id' => $listing->id,
                'category_id' => $listing->main_category_id,
                'category_name' => $listing->mainCategory?->name,
                'item_name' => $listing->name,
                'description' => $listing->short_description,
                'quantity' => (string) ($listing->min_order_quantity ?: 1),
                'unit_id' => $listing->unit_id,
                'estimated_unit_price' => $listing->base_price,
                'listing_image_url' => $imageUrl,
                'custom_attributes' => [],
            ],
            'category_attributes' => $categoryAttributes,
            'attribute_values' => $existingValues,
        ]);
    }

    /**
     * The listing's own attribute values, shaped exactly like the buyer
     * form's item.attribute_values — a starting point the buyer can
     * override without ever touching the supplier's original listing.
     */
    private function listingAttributeValuesForPrefill(Listing $listing): array
    {
        return $listing->attributeValues->mapWithKeys(fn ($v) => [
            $v->attribute_id => [
                'attribute_value_id' => $v->attribute_value_id,
                'custom_value' => $v->custom_value,
                'value_text' => $v->value_text,
                'value_number' => $v->value_number,
                'value_boolean' => $v->value_boolean,
                'value_date' => $v->value_date,
                'value_json' => $v->value_json,
            ],
        ])->all();
    }

    /**
     * Same shape as listingAttributeValuesForPrefill(), sourced from an
     * already-saved RfqItem's own attribute_values instead of a listing's —
     * used to prefill the edit form.
     */
    private function itemAttributeValuesForPrefill(RfqItem $item): array
    {
        return $item->attributeValues->mapWithKeys(fn ($v) => [
            $v->attribute_id => [
                'attribute_value_id' => $v->attribute_value_id,
                'custom_value' => $v->custom_value,
                'value_text' => $v->value_text,
                'value_number' => $v->value_number,
                'value_boolean' => $v->value_boolean,
                'value_date' => $v->value_date,
                'value_json' => $v->value_json,
            ],
        ])->all();
    }

    public function store(SaveRfqRequest $request, RfqService $service)
    {
        $account = $this->currentAccount();
        $this->authorize('create', Rfq::class);

        $existingRfq = null;
        if ($request->filled('rfq_id')) {
            $existingRfq = $account->rfqs()->where('status', 'draft')->find($request->input('rfq_id'));
        }

        try {
            $rfq = $service->saveDraft($account, $this->currentUser(), $request->validated(), $existingRfq);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if ($request->input('action') === 'publish') {
            return $this->doPublish($rfq, $service);
        }

        return redirect()->route('buyer.rfqs.show', $rfq)->with('success', 'RFQ saved as draft.');
    }

    public function edit(Rfq $rfq)
    {
        $this->authorize('update', $rfq);

        $rfq->load(['items.attributeValues', 'items.media', 'invitedSupplierAccounts.supplierProfile', 'targetFilters', 'visibilityType', 'deliveryAddresses']);

        $itemAttributeValues = $rfq->items->values()
            ->mapWithKeys(fn (RfqItem $item, int $idx) => [$idx => $this->itemAttributeValuesForPrefill($item)])
            ->all();

        return view('backend.buyer.procurement.rfqs.edit', [
            'rfq' => $rfq,
            'items' => $rfq->items,
            'itemAttributeValues' => $itemAttributeValues,
            'targetFilter' => $rfq->targetFilters->first(),
            'invitedSuppliers' => $rfq->invitedSupplierAccounts,
        ] + $this->lookups());
    }

    public function update(SaveRfqRequest $request, Rfq $rfq, RfqService $service)
    {
        $this->authorize('update', $rfq);

        $wasDraft = $rfq->status === 'draft';

        try {
            $rfq = $service->saveDraft($this->currentAccount(), $this->currentUser(), $request->validated(), $rfq);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        if ($wasDraft && $request->input('action') === 'publish') {
            return $this->doPublish($rfq, $service);
        }

        return redirect()->route('buyer.rfqs.show', $rfq)->with('success', $wasDraft ? 'RFQ updated.' : 'RFQ updated — a new version has been recorded.');
    }

    /**
     * Per-step wizard autosave — fired from the "Next" button on each step
     * while the RFQ is still a draft. Reuses RfqService::saveDraft() under
     * the looser RfqAutosaveRequest, since the buyer hasn't reached the
     * fields later steps collect yet. Returns JSON instead of redirecting.
     */
    public function autosaveCreate(RfqAutosaveRequest $request, RfqService $service)
    {
        $this->authorize('create', Rfq::class);

        try {
            $rfq = $service->saveDraft($this->currentAccount(), $this->currentUser(), $request->validated());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return response()->json(['id' => $rfq->id, 'rfq_number' => $rfq->rfq_number]);
    }

    public function autosaveUpdate(RfqAutosaveRequest $request, Rfq $rfq, RfqService $service)
    {
        $this->authorize('update', $rfq);

        try {
            $service->saveDraft($this->currentAccount(), $this->currentUser(), $request->validated(), $rfq);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function show(Rfq $rfq)
    {
        $this->authorize('view', $rfq);

        $rfq->load([
            'items.category', 'items.unit', 'items.media', 'items.attributeValues.attribute.unit', 'items.attributeValues.attributeValue',
            'invitedSupplierAccounts.supplierProfile',
            'deliveryCountry', 'deliveryState', 'deliveryCity',
            'targetFilters.category', 'targetFilters.country', 'targetFilters.state', 'targetFilters.city',
            'questions' => fn ($q) => $q->latest(),
            'latestAward.supplierAccount.supplierProfile',
            'changeLogs' => fn ($q) => $q->latest(),
            'changeLogs.changedBy',
            'deadlineExtensions' => fn ($q) => $q->latest(),
            'deadlineExtensions.extendedBy',
        ])->loadCount('quotations');

        $statistics = $this->computeStatistics($rfq);

        return view('backend.buyer.procurement.rfqs.show', [
            'rfq' => $rfq,
            'statistics' => $statistics,
            'supplierEngagement' => $this->computeSupplierEngagement($rfq),
            'engagementFunnel' => $this->computeEngagementFunnel($rfq, $statistics),
            'activityTimeline' => $this->computeActivityTimeline($rfq),
            'recommendations' => $this->computeRecommendations($rfq, $statistics),
        ]);
    }

    /**
     * Per-supplier engagement breakdown for the show page's Statistics
     * tab — who's opened it, expressed interest, messaged the buyer, or
     * already quoted. Suppliers who were notified but never did anything
     * are deliberately excluded — a wall of "—/—/—/—" rows for every
     * matched supplier buries the ones actually worth the buyer's
     * attention (spec: "Do not show all suppliers who have no activity").
     */
    private function computeSupplierEngagement(Rfq $rfq): array
    {
        $actionsBySupplier = \App\Models\SupplierRfqAction::where('rfq_id', $rfq->id)
            ->get()
            ->groupBy('supplier_account_id');

        $quotationsBySupplier = \App\Models\Quotation::where('rfq_id', $rfq->id)->get()->keyBy('supplier_account_id');

        return \App\Models\RfqSupplierQueue::where('rfq_id', $rfq->id)
            ->with('supplierAccount.supplierProfile')
            ->orderByDesc('seen_at')
            ->get()
            ->map(function (\App\Models\RfqSupplierQueue $row) use ($actionsBySupplier, $quotationsBySupplier) {
                $actions = $actionsBySupplier->get($row->supplier_account_id, collect());
                $quotation = $quotationsBySupplier->get($row->supplier_account_id);
                $profile = $row->supplierAccount?->supplierProfile;
                $interested = $actions->contains('action_type', 'interested');
                $messageCount = $actions->where('action_type', 'messaged')->count();

                return [
                    'account' => $row->supplierAccount,
                    'name' => $profile?->display_name ?? ('Supplier #'.$row->supplier_account_id),
                    'logo_url' => $profile?->logo
                        ? asset('storage/'.$profile->logo)
                        : 'https://ui-avatars.com/api/?name='.urlencode($profile?->display_name ?? 'S').'&background=eef2ff&color=4f46e5',
                    'seen_at' => $row->seen_at,
                    'interested' => $interested,
                    'message_count' => $messageCount,
                    'quotation_status' => $quotation?->status,
                    'has_activity' => (bool) ($row->seen_at || $interested || $messageCount > 0 || $quotation),
                ];
            })
            ->filter(fn (array $row) => $row['has_activity'])
            ->values()
            ->all();
    }

    /**
     * Suppliers Notified → Viewed → Interested → Quotation Submitted, with
     * a percentage of the notified count at each later stage — the classic
     * conversion-funnel shape, computed from the same numbers
     * computeStatistics() already has so the two can never disagree.
     */
    private function computeEngagementFunnel(Rfq $rfq, array $statistics): array
    {
        $notified = $statistics['total_notified'];
        $pct = fn (int $value) => $notified > 0 ? (int) round($value / $notified * 100) : 0;

        return [
            ['label' => 'Notified', 'count' => $notified, 'percent' => 100],
            ['label' => 'Viewed', 'count' => $statistics['viewed'], 'percent' => $pct($statistics['viewed'])],
            ['label' => 'Interested', 'count' => $statistics['interested'], 'percent' => $pct($statistics['interested'])],
            ['label' => 'Quoted', 'count' => $statistics['quotations_received'], 'percent' => $pct($statistics['quotations_received'])],
        ];
    }

    /**
     * Merges queue "viewed" timestamps, the SupplierRfqAction engagement
     * log, and quotation submissions into one chronological feed — three
     * different tables, one timeline, newest first.
     */
    private function computeActivityTimeline(Rfq $rfq, ?int $limit = null): array
    {
        $actionSupplierIds = \App\Models\SupplierRfqAction::where('rfq_id', $rfq->id)->pluck('supplier_account_id');
        $queueSupplierIds = \App\Models\RfqSupplierQueue::where('rfq_id', $rfq->id)->pluck('supplier_account_id');
        $quotationSupplierIds = \App\Models\Quotation::where('rfq_id', $rfq->id)->pluck('supplier_account_id');

        $allSupplierIds = $actionSupplierIds->merge($queueSupplierIds)->merge($quotationSupplierIds)->filter()->unique();

        $supplierAccounts = \App\Models\Account::whereIn('id', $allSupplierIds)
            ->with('supplierProfile')
            ->get()
            ->keyBy('id');

        $events = collect();

        $actionMeta = [
            'viewed' => ['label' => 'viewed this RFQ', 'icon' => 'fa-eye', 'color' => 'text-slate-600 bg-slate-100'],
            'interested' => ['label' => 'marked this RFQ as Interested', 'icon' => 'fa-thumbs-up', 'color' => 'text-blue-600 bg-blue-100'],
            'not_interested' => ['label' => 'marked this RFQ as Not Interested', 'icon' => 'fa-ban', 'color' => 'text-rose-600 bg-rose-100'],
            'messaged' => ['label' => 'sent a message', 'icon' => 'fa-comment-dots', 'color' => 'text-purple-600 bg-purple-100'],
            'preparing_quote' => ['label' => 'started preparing a quotation', 'icon' => 'fa-file-pen', 'color' => 'text-amber-600 bg-amber-100'],
            'quoted' => ['label' => 'submitted a quotation', 'icon' => 'fa-sack-dollar', 'color' => 'text-emerald-600 bg-emerald-100'],
        ];

        $suppliersWithViewedAction = [];

        foreach (\App\Models\SupplierRfqAction::where('rfq_id', $rfq->id)->get() as $action) {
            $account = $supplierAccounts->get($action->supplier_account_id);
            $supplierName = $account?->supplierProfile?->display_name ?? ('Supplier #'.$action->supplier_account_id);
            $meta = $actionMeta[$action->action_type] ?? [
                'label' => str_replace('_', ' ', $action->action_type),
                'icon' => 'fa-circle-info',
                'color' => 'text-gray-500 bg-gray-100',
            ];
            $events->push([
                'account' => $account,
                'supplier_account_id' => $action->supplier_account_id,
                'supplier' => $supplierName,
                'action' => $meta['label'],
                'icon' => $meta['icon'],
                'color' => $meta['color'],
                'at' => $action->created_at,
                'note' => $action->note,
            ]);

            if ($action->action_type === 'viewed') {
                $suppliersWithViewedAction[$action->supplier_account_id] = true;
            }
        }

        // RfqOpportunityService normally logs a matching SupplierRfqAction
        // ('viewed') whenever it sets seen_at, but some rows predate that
        // (or were touched by another path) and have seen_at with no
        // action-log entry — fall back to seen_at for those only, so a
        // real view never goes missing without duplicating the common case.
        foreach (\App\Models\RfqSupplierQueue::where('rfq_id', $rfq->id)->whereNotNull('seen_at')->get() as $row) {
            if (isset($suppliersWithViewedAction[$row->supplier_account_id])) {
                continue;
            }

            $account = $supplierAccounts->get($row->supplier_account_id);
            $supplierName = $account?->supplierProfile?->display_name ?? ('Supplier #'.$row->supplier_account_id);

            $events->push([
                'account' => $account,
                'supplier_account_id' => $row->supplier_account_id,
                'supplier' => $supplierName,
                'action' => $actionMeta['viewed']['label'],
                'icon' => $actionMeta['viewed']['icon'],
                'color' => $actionMeta['viewed']['color'],
                'at' => $row->seen_at,
                'note' => null,
            ]);
        }

        $quotedSuppliers = \App\Models\SupplierRfqAction::where('rfq_id', $rfq->id)
            ->where('action_type', 'quoted')
            ->pluck('supplier_account_id')
            ->flip();

        foreach (\App\Models\Quotation::where('rfq_id', $rfq->id)->whereNotNull('submitted_at')->get() as $quotation) {
            if (isset($quotedSuppliers[$quotation->supplier_account_id])) {
                continue;
            }

            $account = $supplierAccounts->get($quotation->supplier_account_id);
            $supplierName = $account?->supplierProfile?->display_name ?? ('Supplier #'.$quotation->supplier_account_id);

            $events->push([
                'account' => $account,
                'supplier_account_id' => $quotation->supplier_account_id,
                'supplier' => $supplierName,
                'action' => 'submitted a quotation',
                'icon' => 'fa-sack-dollar',
                'color' => 'text-emerald-600 bg-emerald-100',
                'at' => $quotation->submitted_at,
                'note' => null,
            ]);
        }

        $sorted = $events->sortByDesc('at')->values();

        if ($limit !== null) {
            return $sorted->take($limit)->all();
        }

        return $sorted->all();
    }

    /**
     * Simple rule-based nudges when engagement looks weak — not machine
     * learning, just thresholds a buyer would reasonably act on. Only
     * computed once an RFQ has actually reached suppliers (draft/
     * pending_approval have nothing meaningful to recommend yet).
     */
    private function computeRecommendations(Rfq $rfq, array $statistics): array
    {
        if (in_array($rfq->status, ['draft', 'pending_approval'], true)) {
            return [];
        }

        $recommendations = [];
        $notified = $statistics['total_notified'];
        $daysRemaining = $statistics['days_remaining'];
        $isOpen = $rfq->status === 'open';

        if ($notified === 0) {
            $recommendations[] = [
                'title' => 'No suppliers reached yet',
                'description' => 'This RFQ hasn\'t matched or been sent to any suppliers. Widen its targeting or invite suppliers directly.',
                'icon' => 'fa-user-plus', 'action_label' => 'Edit RFQ', 'action_href' => route('buyer.rfqs.edit', $rfq),
            ];
        } elseif ($statistics['viewed'] === 0) {
            $recommendations[] = [
                'title' => 'No suppliers have viewed this RFQ yet',
                'description' => 'It can take suppliers a little time to open new opportunities — if this continues, consider inviting more suppliers.',
                'icon' => 'fa-eye-slash', 'action_label' => 'Edit RFQ', 'action_href' => route('buyer.rfqs.edit', $rfq),
            ];
        } elseif ($notified > 0 && ($statistics['viewed'] / $notified) < 0.4) {
            $recommendations[] = [
                'title' => 'Low view rate',
                'description' => 'Fewer than 4 in 10 notified suppliers have opened this RFQ. Inviting more suppliers can improve your odds of a good quotation.',
                'icon' => 'fa-chart-line', 'action_label' => 'Invite More Suppliers', 'action_href' => route('buyer.rfqs.edit', $rfq),
            ];
        }

        if ($statistics['quotations_received'] === 0 && $isOpen && $daysRemaining !== null && $daysRemaining <= 2) {
            $recommendations[] = [
                'title' => 'Deadline is close with no quotations yet',
                'description' => 'The quotation deadline is only '.$daysRemaining.' day(s) away and nobody has quoted. Extending it gives suppliers more time to respond.',
                'icon' => 'fa-clock', 'action_label' => 'Extend Deadline', 'action_modal' => 'extend-deadline',
            ];
        }

        if ($statistics['viewed'] > 0 && $statistics['interested'] === 0 && $statistics['quotations_received'] === 0) {
            $recommendations[] = [
                'title' => 'Viewed but no interest yet',
                'description' => 'Suppliers are opening this RFQ but not engaging further — double-check the item specs, quantities, and budget are clear and competitive.',
                'icon' => 'fa-pen-to-square', 'action_label' => 'Edit RFQ', 'action_href' => route('buyer.rfqs.edit', $rfq),
            ];
        }

        return $recommendations;
    }

    public function publish(Rfq $rfq, RfqService $service)
    {
        $this->authorize('publish', $rfq);

        return $this->doPublish($rfq, $service);
    }

    public function cancel(CancelRfqRequest $request, Rfq $rfq, RfqService $service)
    {
        $this->authorize('cancel', $rfq);

        try {
            $service->cancel($rfq, $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('buyer.rfqs.show', $rfq)->with('success', 'RFQ cancelled.');
    }

    public function extendDeadline(ExtendRfqDeadlineRequest $request, Rfq $rfq, RfqService $service)
    {
        $this->authorize('extendDeadline', $rfq);

        try {
            $service->extendDeadline(
                $rfq,
                $this->currentUser(),
                $request->string('deadline_type')->toString(),
                $request->string('new_deadline')->toString(),
                $request->input('reason')
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('buyer.rfqs.show', ['rfq' => $rfq, '_tab' => 'history'])->with('success', 'Deadline extended and suppliers notified.');
    }

    public function answerQuestion(Request $request, Rfq $rfq, RfqQuestion $question)
    {
        $this->authorize('view', $rfq);
        abort_unless($question->rfq_id === $rfq->id, 404);

        $request->validate(['answer' => ['required', 'string', 'max:2000']]);

        $question->update([
            'answer' => $request->string('answer'),
            'answered_by_user_id' => $this->currentUser()->id,
            'answered_at' => now(),
            'status' => 'answered',
        ]);

        return redirect()->route('buyer.rfqs.show', ['rfq' => $rfq, '_tab' => 'questions'])->with('success', 'Answer submitted.');
    }

    /**
     * A draft never went anywhere, so it's safe to delete outright —
     * RfqPolicy::delete() only allows this while status is still 'draft'.
     */
    public function destroy(Rfq $rfq)
    {
        $this->authorize('delete', $rfq);

        $rfq->delete();

        return redirect()->route('buyer.rfqs.index')->with('success', 'Draft RFQ deleted.');
    }

    /**
     * Clones an existing RFQ's items/details into a brand-new draft — a
     * quick way to start a similar procurement without rebuilding it from
     * scratch. Deadlines are deliberately NOT copied; the buyer sets fresh
     * ones in step 3 before publishing.
     */
    public function duplicate(Rfq $rfq, RfqService $service)
    {
        $this->authorize('duplicate', $rfq);

        $rfq->load(['items.attributeValues', 'targetFilters']);

        $copy = DB::transaction(function () use ($rfq, $service) {
            $newRfq = Rfq::create([
                'rfq_number' => $service->generateRfqNumber(),
                'buyer_account_id' => $rfq->buyer_account_id,
                'created_by_user_id' => $this->currentUser()->id,
                'visibility_type_id' => $rfq->visibility_type_id,
                'source_listing_id' => $rfq->source_listing_id,
                'title' => $rfq->title.' (Copy)',
                'description' => $rfq->description,
                'currency_code' => $rfq->currency_code,
                'budget_min' => $rfq->budget_min,
                'budget_max' => $rfq->budget_max,
                'delivery_country_id' => $rfq->delivery_country_id,
                'delivery_state_id' => $rfq->delivery_state_id,
                'delivery_city_id' => $rfq->delivery_city_id,
                'delivery_address' => $rfq->delivery_address,
                'delivery_latitude' => $rfq->delivery_latitude,
                'delivery_longitude' => $rfq->delivery_longitude,
                'allow_partial_quotation' => $rfq->allow_partial_quotation,
                'allow_alternative_products' => $rfq->allow_alternative_products,
                'status' => 'draft',
                'current_step' => 1,
                'max_completed_step' => 1,
            ]);

            foreach ($rfq->items as $item) {
                $newItem = $newRfq->items()->create([
                    'item_type' => $item->item_type,
                    'listing_id' => $item->listing_id,
                    'category_id' => $item->category_id,
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_id' => $item->unit_id,
                    'custom_unit' => $item->custom_unit,
                    'estimated_unit_price' => $item->estimated_unit_price,
                    'specs' => $item->specs,
                    'sort_order' => $item->sort_order,
                ]);

                foreach ($item->attributeValues as $value) {
                    $newItem->attributeValues()->create([
                        'attribute_id' => $value->attribute_id,
                        'attribute_value_id' => $value->attribute_value_id,
                        'value_text' => $value->value_text,
                        'value_number' => $value->value_number,
                        'value_boolean' => $value->value_boolean,
                        'value_date' => $value->value_date,
                        'value_json' => $value->value_json,
                        'custom_value' => $value->custom_value,
                    ]);
                }
            }

            $newRfq->update(['items_count' => $newRfq->items()->count()]);

            foreach ($rfq->targetFilters as $filter) {
                $newRfq->targetFilters()->create([
                    'category_id' => $filter->category_id,
                    'location_match_level' => $filter->location_match_level,
                    'country_id' => $filter->country_id,
                    'state_id' => $filter->state_id,
                    'city_id' => $filter->city_id,
                ]);
            }

            return $newRfq;
        });

        return redirect()->route('buyer.rfqs.edit', $copy)->with('success', 'RFQ duplicated as a new draft — review and publish when ready.');
    }

    /**
     * JSON summary for the RFQ list's "Statistics" modal — a lighter-weight
     * read than the RFQ show page's full Statistics section (same
     * computeStatistics() data source, so the two never drift apart).
     */
    public function statistics(Rfq $rfq)
    {
        $this->authorize('view', $rfq);

        return response()->json($this->computeStatistics($rfq));
    }

    private function computeStatistics(Rfq $rfq): array
    {
        $queueRows = \App\Models\RfqSupplierQueue::where('rfq_id', $rfq->id)->get();
        $actions = \App\Models\SupplierRfqAction::where('rfq_id', $rfq->id)->get();

        $daysRemaining = $rfq->quotation_deadline && now()->lt($rfq->quotation_deadline)
            ? (int) now()->diffInDays($rfq->quotation_deadline)
            : ($rfq->quotation_deadline ? 0 : null);

        $lastActivityAt = collect([
            $queueRows->max('seen_at'),
            $actions->max('created_at'),
            $rfq->quotations()->max('submitted_at'),
        ])->filter()->map(fn ($d) => \Illuminate\Support\Carbon::parse($d))->sort()->last();

        return [
            'rfq_id' => $rfq->id,
            'rfq_title' => $rfq->title,
            'total_notified' => $queueRows->count(),
            'viewed' => $queueRows->whereNotNull('seen_at')->count(),
            'interested' => $actions->where('action_type', 'interested')->pluck('supplier_account_id')->unique()->count(),
            'messaged' => $actions->where('action_type', 'messaged')->count(),
            'quotations_received' => $rfq->quotations_count,
            'days_remaining' => $daysRemaining,
            'last_activity_human' => $lastActivityAt?->diffForHumans(),
        ];
    }

    public function searchSuppliers(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $suppliers = Account::marketplace()
            ->whereHas('capabilities', fn ($q) => $q->where('status', 'active')->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'supplier')))
            ->whereHas('supplierProfile', fn ($q) => $q->where('display_name', 'like', '%'.$request->string('q').'%'))
            ->with('supplierProfile')
            ->limit(15)
            ->get(['id'])
            ->map(fn (Account $a) => [
                'id' => $a->id,
                'name' => $a->supplierProfile?->display_name,
            ]);

        return response()->json($suppliers);
    }

    private function doPublish(Rfq $rfq, RfqService $service)
    {
        try {
            $rfq = $service->publish($rfq);
        } catch (ValidationException $e) {
            return redirect()->route('buyer.rfqs.show', $rfq)->withErrors($e->errors());
        }

        return redirect()->route('buyer.rfqs.show', $rfq)->with('success', 'RFQ published — suppliers can now submit quotations.');
    }

    private function lookups(): array
    {
        return [
            'categories'      => Category::active()->approved()->orderBy('name')->get(['id', 'name', 'parent_id']),
            // Flat, hierarchy-path-aware list for the "Add Custom Product" item
            // category picker — same helper the supplier catalog listing wizard
            // uses for its searchable category tree (id/name/path/depth/attributes_count).
            'categoryNodes'   => Category::getTreeSelectOptions(['product', 'service', 'both']),
            'units'           => Unit::active()->orderBy('name')->get(['id', 'name', 'symbol']),
            'currencies'      => Currency::active()->orderBy('code')->get(['code', 'name', 'symbol']),
            'visibilityTypes' => \App\Models\VisibilityType::active()->ordered()->get(),
        ];
    }

    private function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
            'open' => 'Open',
            'closed' => 'Closed',
            'award_pending' => 'Award Pending',
            'awarded' => 'Awarded',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            'completed' => 'Completed',
        ];
    }
}
