<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Procurement\AwardQuotationRequest;
use App\Http\Requests\Backend\Buyer\Procurement\CompareQuotationsDataRequest;
use App\Http\Requests\Backend\Buyer\Procurement\RejectQuotationRequest;
use App\Http\Requests\Backend\Buyer\Procurement\RequestQuotationRevisionRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationItemOffer;
use App\Models\Review;
use App\Models\Rfq;
use App\Services\AwardService;
use App\Services\QuotationComparisonService;
use App\Services\QuotationDecisionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    use InteractsWithBuyerAccount;

    private function statusOptions(): array
    {
        return [
            'submitted' => 'Submitted', 'under_review' => 'Under Review', 'shortlisted' => 'Shortlisted',
            'revision_requested' => 'Revision Requested', 'revised' => 'Revised', 'rejected' => 'Rejected', 'awarded' => 'Awarded',
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Quotation::class);

        $account = $this->currentAccount();
        $rfqIds = $account->rfqs()->pluck('id');

        $sort = in_array($request->string('sort')->toString(), ['quotation_number', 'grand_total', 'submitted_at'], true)
            ? $request->string('sort')->toString()
            : null;
        $direction = $request->string('direction') === 'asc' ? 'asc' : 'desc';
        $search = trim((string) $request->get('search', ''));

        // Default landing view is "needs my attention" (submitted, awaiting
        // a decision) rather than every status ever received — an explicit
        // ?status[]=... (including none checked, i.e. "All Statuses") always
        // wins over that default.
        $selectedStatuses = $request->has('status')
            ? array_intersect((array) $request->query('status', []), array_keys($this->statusOptions()))
            : ['submitted'];

        $quotationsQuery = Quotation::whereIn('rfq_id', $rfqIds)
            ->where('status', '!=', 'draft')
            ->when($request->filled('rfq'), fn ($q) => $q->where('rfq_id', $request->integer('rfq')))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('quotation_number', 'like', "%{$search}%")
                        ->orWhereHas('supplierAccount.supplierProfile', function ($q3) use ($search) {
                            $q3->where('display_name', 'like', "%{$search}%")
                                ->orWhere('legal_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('supplierAccount.users', function ($q3) use ($search) {
                            $q3->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('submittedBy', function ($q3) use ($search) {
                            $q3->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('rfq', function ($q4) use ($search) {
                            $q4->where('title', 'like', "%{$search}%")
                                ->orWhere('rfq_number', 'like', "%{$search}%");
                        });
                });
            });

        // Counts reflect the search only, not the status
        // checkboxes themselves — ticking a box must never change the
        // numbers next to the OTHER boxes in the same dropdown.
        $statusCounts = (clone $quotationsQuery)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $quotations = (clone $quotationsQuery)
            ->when(! empty($selectedStatuses), fn ($q) => $q->whereIn('status', $selectedStatuses))
            ->with(['rfq', 'supplierAccount.supplierProfile', 'award', 'shortlists'])
            ->when($sort, fn ($q) => $q->orderBy($sort, $direction), fn ($q) => $q->latest('submitted_at'))
            ->paginate(10)
            ->withQueryString();

        $tableData = [
            'quotations' => $quotations,
            'compareEligibleStatuses' => config('quotation_comparison.eligible_statuses', []),
            'maxCompareItems' => (int) config('quotation_comparison.max_items', 5),
            'compareRfqId' => $request->integer('rfq') ?: null,
        ];

        // Live filtering/search/sort/pagination all hit this same GET route
        // via fetch() — only the table (not the whole page) needs to come
        // back, plus the dropdown counts so they never go stale client-side.
        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('backend.buyer.procurement.quotations.partials._table', $tableData)->render(),
                'status_counts' => $statusCounts,
            ]);
        }

        return view('backend.buyer.procurement.quotations.index', $tableData + [
            'rfq' => $request->integer('rfq'),
            'search' => $search,
            'statusOptions' => $this->statusOptions(),
            'selectedStatuses' => $selectedStatuses,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * "Compare" landing page (Procurement sidebar) — RFQ comparison only
     * makes sense within one RFQ at a time (items must line up), so this
     * just surfaces which of the buyer's RFQs are worth comparing rather
     * than attempting a cross-RFQ comparison. Active localStorage selections
     * are picked up client-side by compareTrayGlobal in _compare-store.
     */
    public function compareIndex()
    {
        $this->authorize('viewAny', Quotation::class);

        $account = $this->currentAccount();
        $eligibleStatuses = config('quotation_comparison.eligible_statuses', []);

        $rfqs = $account->rfqs()
            ->withCount(['quotations as eligible_quotations_count' => fn ($q) => $q->whereIn('status', $eligibleStatuses)])
            ->having('eligible_quotations_count', '>=', 2)
            ->orderByDesc('id')
            ->get(['id', 'title', 'rfq_number']);

        return view('backend.buyer.procurement.quotations.compare-index', [
            'rfqs' => $rfqs,
            'maxItems' => (int) config('quotation_comparison.max_items', 5),
        ]);
    }

    public function compare(Rfq $rfq)
    {
        $this->authorize('compare', $rfq);

        $rfq->loadCount('items');

        $eligibleQuotations = $rfq->quotations()
            ->whereIn('status', config('quotation_comparison.eligible_statuses', []))
            ->with(['supplierAccount.supplierProfile'])
            ->orderByDesc('submitted_at')
            ->take((int) config('quotation_comparison.max_items', 5))
            ->get();

        return view('backend.buyer.procurement.quotations.compare', [
            'rfq' => $rfq,
            'maxItems' => (int) config('quotation_comparison.max_items', 5),
            'defaultQuotationIds' => $eligibleQuotations->pluck('id')->values()->all(),
            'eligibleQuotations' => $eligibleQuotations,
        ]);
    }

    public function compareData(Rfq $rfq, CompareQuotationsDataRequest $request, QuotationComparisonService $service)
    {
        $this->authorize('compare', $rfq);

        $rfq->load([
            'items.attributeValues.attribute.attributeGroup',
            'items.attributeValues.attribute.unit',
            'items.attributeValues.attributeValue',
            'items.unit',
            'items.media',
            'items.category',
            'items.listing',
        ]);

        ['quotations' => $quotations, 'removed_ids' => $removedIds] = $service->resolve($rfq, $request->input('quotation_ids', []));

        return response()->json([
            'removed_ids' => $removedIds,
            'rfq' => [
                'id' => $rfq->id,
                'title' => $rfq->title,
                'rfq_number' => $rfq->rfq_number,
                'current_version_no' => $rfq->current_version_no,
                'allow_partial_quotation' => (bool) $rfq->allow_partial_quotation,
                'allow_alternative_products' => (bool) $rfq->allow_alternative_products,
            ],
            'summary' => $service->buildSummary($rfq, $quotations),
            'commercial' => $service->buildCommercial($quotations),
            'items' => $service->buildItemComparison($rfq, $quotations),
            'partial' => $service->buildPartialSummary($rfq, $quotations),
        ]);
    }

    public function show(Quotation $quotation, QuotationDecisionService $decisions)
    {
        $this->authorize('view', $quotation);

        $decisions->markViewed($quotation);

        $quotation->load([
            'rfq.items.unit', 'supplierAccount.supplierProfile.country',
            'items.rfqItem', 'items.offeredListing', 'items.unit', 'items.media',
            'items.offers.marketplaceProduct', 'items.offers.media',
            'media',
            'shortlists',
            'deliveryAddresses',
            'revisions' => fn ($q) => $q->orderByDesc('revision_no'),
            'revisions.items.offers',
            'revisions.createdBy',
            'activities' => fn ($q) => $q->with('user')->latest(),
        ]);

        return view('backend.buyer.procurement.quotations.show', [
            'quotation' => $quotation,
            'isShortlisted' => $quotation->shortlists->isNotEmpty(),
            'canReview' => \Illuminate\Support\Facades\Gate::allows('createForQuotation', [Review::class, $quotation]),
            'supplierActivity' => $quotation->activities->where('actor_role', 'supplier')->values(),
        ]);
    }

    public function shortlist(Request $request, Quotation $quotation, QuotationDecisionService $decisions)
    {
        $this->authorize('shortlist', $quotation);

        $decisions->shortlist($quotation, $this->currentAccount(), $this->currentUser(), $request->input('notes'));

        return back()->with('success', 'Quotation shortlisted.');
    }

    public function unshortlist(Quotation $quotation, QuotationDecisionService $decisions)
    {
        $this->authorize('shortlist', $quotation);

        $decisions->removeFromShortlist($quotation);

        return back()->with('success', 'Removed from shortlist.');
    }

    public function requestRevision(RequestQuotationRevisionRequest $request, Quotation $quotation, QuotationDecisionService $decisions)
    {
        $this->authorize('requestRevision', $quotation);

        $decisions->requestRevision($quotation, $this->currentAccount(), $this->currentUser(), $request->string('requested_changes'));

        return back()->with('success', 'Revision requested from the supplier.');
    }

    public function reject(RejectQuotationRequest $request, Quotation $quotation, QuotationDecisionService $decisions)
    {
        $this->authorize('reject', $quotation);

        $decisions->reject($quotation, $request->string('reason'));

        return back()->with('success', 'Quotation rejected.');
    }

    /**
     * POST buyer/quotations/{quotation}/items/{item}/offers/{offer}/select
     * — picks which offer within one Product Response should be used if
     * this quotation is awarded. $item/$offer ownership is verified inline
     * (not implicit route-model-scoping) since both are nested resources.
     */
    public function selectOffer(Quotation $quotation, QuotationItem $item, QuotationItemOffer $offer, QuotationDecisionService $decisions)
    {
        $this->authorize('selectOffer', $quotation);

        abort_unless($item->quotation_id === $quotation->id, 404);
        abort_unless($offer->quotation_item_id === $item->id, 404);

        $decisions->selectOffer($item, $offer);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'           => true,
                'message'           => 'Offer selected for this product.',
                'selected_offer_id' => $offer->id,
                'quotation_item_id' => $item->id,
                'quotation_id'      => $quotation->id,
            ]);
        }

        return back()->with('success', 'Offer selected for this product.');
    }

    public function award(AwardQuotationRequest $request, Quotation $quotation, AwardService $awards)
    {
        $this->authorize('award', $quotation);

        try {
            $award = $awards->create($quotation, $this->currentUser(), $request->input('award_note'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('buyer.awards.show', $award)->with('success', 'Award created — waiting for the supplier to respond.');
    }

    /**
     * "Undo Award" — DELETE buyer/quotations/{quotation}/award. Takes the
     * quotation (not the award id directly) so the button lives alongside
     * "Award This Quotation" with the same ergonomics; resolves the (at
     * most one, per awards.quotation_id's unique index) pending award via
     * Quotation::award() internally.
     */
    public function cancelAward(Quotation $quotation, AwardService $awards)
    {
        $award = $quotation->award;
        abort_if(! $award, 404);

        $this->authorize('cancel', $award);

        try {
            $awards->cancel($award, request()->input('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('buyer.quotations.show', $quotation)->with('success', 'Award cancelled.');
    }

    /**
     * GET buyer/quotations/{quotation}/statistics — mirrors
     * Supplier\Procurement\QuotationController::statistics(): powers a
     * shared "Statistics" modal on the index page and the show page's
     * Statistics tab, both sourced from quotation_activities.
     */
    public function statistics(Quotation $quotation)
    {
        $this->authorize('view', $quotation);

        $quotation->loadMissing('activities');

        $activities = $quotation->activities()->latest()->get();
        $messagesCount = $activities->whereIn('activity_type', ['buyer_messaged', 'supplier_replied'])->count();
        $lastActivity = $activities->first();

        return response()->json([
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'status' => $quotation->status,
            'submitted_at_human' => $quotation->submitted_at?->diffForHumans(),
            'messages_count' => $messagesCount,
            'revision_no' => $quotation->current_revision_no,
            'last_activity_label' => $lastActivity?->label(),
            'last_activity_human' => $lastActivity?->created_at->diffForHumans(),
            'recent_activities' => $activities->take(5)->map(fn ($a) => [
                'label' => $a->label(),
                'message' => $a->message,
                'icon' => $a->icon(),
                'color_class' => $a->colorClass(),
                'created_at_human' => $a->created_at->diffForHumans(),
            ])->values(),
        ]);
    }
}
