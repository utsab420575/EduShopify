<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Procurement\CancelRfqRequest;
use App\Http\Requests\Backend\Buyer\Procurement\ExtendRfqDeadlineRequest;
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
use Illuminate\Validation\ValidationException;

class RfqController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $rfqs = $account->rfqs()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('title', 'like', "%{$search}%")->orWhere('rfq_number', 'like', "%{$search}%"));
            })
            ->withCount('quotations')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.buyer.procurement.rfqs.index', [
            'rfqs' => $rfqs,
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Rfq::class);

        $preselectedSupplier = null;
        $itemAttributeValues = [];

        if ($request->filled('supplier')) {
            $preselectedSupplier = Account::with('supplierProfile')->find($request->integer('supplier'));
        }

        $items = collect();

        if ($request->filled('listing')) {
            $listing = Listing::published()->find($request->integer('listing'));

            if ($listing) {
                $items = collect([(object) [
                    'id' => null,
                    'item_type' => $listing->listing_type,
                    'listing_id' => $listing->id,
                    'category_id' => $listing->main_category_id,
                    'item_name' => $listing->name,
                    'description' => $listing->short_description,
                    'quantity' => (string) ($listing->min_order_quantity ?: 1),
                    'unit_id' => $listing->unit_id,
                    'custom_unit' => null,
                    'estimated_unit_price' => $listing->base_price,
                ]]);

                $itemAttributeValues[0] = $this->listingAttributeValuesForPrefill($listing);

                $preselectedSupplier ??= $listing->supplierAccount()->with('supplierProfile')->first();
            }
        }

        // A product-page "Request Quotation" starts the RFQ locked to that
        // one supplier by default (spec §11) — the buyer can still switch
        // to Selected Suppliers or Open to Eligible Suppliers from the form.
        $defaultVisibility = $preselectedSupplier
            ? \App\Models\VisibilityType::where('code', 'direct')->first()
            : \App\Models\VisibilityType::where('code', 'open_matching')->first();

        return view('backend.buyer.procurement.rfqs.create', [
            'rfq' => new Rfq([
                'visibility_type_id' => $defaultVisibility?->id,
                'allow_partial_quotation' => true,
                'allow_alternative_products' => true,
            ]),
            'items' => $items,
            'itemAttributeValues' => $itemAttributeValues,
            'targetFilter' => null,
            'invitedSuppliers' => $preselectedSupplier ? collect([$preselectedSupplier]) : collect(),
        ] + $this->lookups());
    }

    /**
     * GET buyer/rfqs/categories/{category}/attributes — same JSON shape the
     * supplier listing wizard's equivalent endpoint returns, so the item
     * attribute form can be a near-direct reuse of that Alpine component.
     */
    public function categoryAttributes(Category $category)
    {
        return response()->json($category->attributesGroupedForForm());
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

        $categoryAttributes = $listing->main_category_id
            ? $listing->mainCategory?->attributesGroupedForForm()
            : null;

        return response()->json([
            'item' => [
                'item_type' => $listing->listing_type,
                'listing_id' => $listing->id,
                'category_id' => $listing->main_category_id,
                'item_name' => $listing->name,
                'description' => $listing->short_description,
                'quantity' => (string) ($listing->min_order_quantity ?: 1),
                'unit_id' => $listing->unit_id,
                'estimated_unit_price' => $listing->base_price,
            ],
            'category_attributes' => $categoryAttributes,
            'attribute_values' => $this->listingAttributeValuesForPrefill($listing),
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

        try {
            $rfq = $service->saveDraft($account, $this->currentUser(), $request->validated());
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

        $rfq->load(['items.attributeValues', 'invitedSupplierAccounts.supplierProfile', 'targetFilters']);

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

    public function show(Rfq $rfq)
    {
        $this->authorize('view', $rfq);

        $rfq->load([
            'items.category', 'items.unit', 'items.attributeValues.attribute.unit', 'items.attributeValues.attributeValue',
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

        return view('backend.buyer.procurement.rfqs.show', ['rfq' => $rfq]);
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
