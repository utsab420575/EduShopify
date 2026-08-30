<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Procurement\SaveQuotationRequest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\Unit;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    use InteractsWithSupplierAccount;

    private const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'revision_requested' => 'Revision Requested',
        'revised' => 'Revised',
        'shortlisted' => 'Shortlisted',
        'awarded' => 'Awarded',
        'rejected' => 'Rejected',
        'withdrawn' => 'Withdrawn',
        'expired' => 'Expired',
    ];

    private function formLookups(): array
    {
        return [
            'units' => Unit::active()->orderBy('name')->get(['id', 'name', 'symbol']),
            'currencies' => Currency::active()->orderBy('code')->get(['code', 'name', 'symbol']),
        ];
    }

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $query = $account->quotations()->with(['rfq.buyerAccount.buyerProfile'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $quotations = $query->paginate(10)->withQueryString();

        return view('backend.supplier.procurement.quotations.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'quotations' => $quotations,
            'status' => $request->string('status')->toString(),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function create(Rfq $rfq)
    {
        $this->authorize('create', [Quotation::class, $rfq]);

        $account = $this->currentAccount();

        $existing = $account->quotations()->where('rfq_id', $rfq->id)->first();
        if ($existing) {
            return redirect()->route('supplier.quotations.show', $existing);
        }

        $rfq->load(['items.unit', 'items.category', 'items.attributeValues.attribute.unit', 'items.attributeValues.attributeValue', 'buyerAccount.buyerProfile']);

        return view('backend.supplier.procurement.quotations.create', [
            'account' => $account,
            'user' => $this->currentUser(),
            'rfq' => $rfq,
        ] + $this->formLookups());
    }

    public function store(SaveQuotationRequest $request, Rfq $rfq, QuotationService $service)
    {
        $this->authorize('create', [Quotation::class, $rfq]);

        $account = $this->currentAccount();

        try {
            $quotation = $service->saveDraft($rfq, $account, $this->currentUser(), $request->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation saved as draft.');
    }

    public function show(Quotation $quotation)
    {
        $this->authorize('view', $quotation);

        $quotation->load([
            'rfq.buyerAccount.buyerProfile',
            'rfq.items.attributeValues.attribute.unit',
            'rfq.items.attributeValues.attributeValue',
            'items.attributeValues.attribute.unit',
            'items.attributeValues.attributeValue',
            'items.offeredListing',
            'items.offeredVariant',
            'items.unit',
            'revisions.items',
            'revisionRequests' => fn ($q) => $q->latest(),
        ]);

        $rfq = $quotation->rfq;
        $versionChanged = $quotation->rfq_version_no !== $rfq->current_version_no;
        $changeLogs = $versionChanged
            ? $rfq->changeLogs()->where('to_version_no', '>', $quotation->rfq_version_no)->orderBy('to_version_no')->get()
            : collect();

        return view('backend.supplier.procurement.quotations.show', [
            'account' => $this->currentAccount(),
            'user' => $this->currentUser(),
            'quotation' => $quotation,
            'versionChanged' => $versionChanged,
            'changeLogs' => $changeLogs,
        ]);
    }

    public function edit(Quotation $quotation)
    {
        $this->authorize('editDraft', $quotation);

        $quotation->load([
            'rfq.items.unit', 'rfq.items.category', 'rfq.items.attributeValues.attribute.unit', 'rfq.items.attributeValues.attributeValue',
            'items.attributeValues',
        ]);

        return view('backend.supplier.procurement.quotations.edit', [
            'account' => $this->currentAccount(),
            'user' => $this->currentUser(),
            'quotation' => $quotation,
            'rfq' => $quotation->rfq,
        ] + $this->formLookups());
    }

    public function update(SaveQuotationRequest $request, Quotation $quotation, QuotationService $service)
    {
        $this->authorize('editDraft', $quotation);

        try {
            $service->saveDraft($quotation->rfq, $this->currentAccount(), $this->currentUser(), $request->validated(), $quotation);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Draft updated.');
    }

    public function submit(Request $request, Quotation $quotation, QuotationService $service)
    {
        $this->authorize('submitDraft', $quotation);

        try {
            $service->submitDraft($quotation, $request->boolean('acknowledge_version_change'));
        } catch (ValidationException $e) {
            return redirect()->route('supplier.quotations.show', $quotation)->withErrors($e->errors());
        }

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation submitted to the buyer.');
    }

    public function withdraw(Request $request, Quotation $quotation, QuotationService $service)
    {
        $this->authorize('withdraw', $quotation);

        $service->withdraw($quotation, $request->input('reason'));

        return redirect()->route('supplier.quotations.show', $quotation)->with('success', 'Quotation withdrawn.');
    }

    /**
     * GET supplier/quotations/categories/{category}/attributes — identical
     * contract to the buyer module's equivalent endpoint, so the offer form
     * renders the same attribute set the buyer's requirement was built from.
     */
    public function categoryAttributes(Category $category)
    {
        return response()->json($category->attributesGroupedForForm());
    }

    /**
     * GET supplier/quotations/listings/search?q=&category_id= — scoped to
     * the current supplier's own listings only, unlike the buyer module's
     * marketplace-wide search.
     */
    public function searchListings(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $listings = $this->currentAccount()->listings()
            ->where('approval_status', 'approved')
            ->where('name', 'like', '%'.$request->string('q').'%')
            ->when($request->filled('category_id'), fn ($q) => $q->where('main_category_id', $request->integer('category_id')))
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
     * GET supplier/quotations/listings/{listing}/prefill — item fields,
     * this listing's own attribute values (the supplier's starting offer,
     * freely overridable per item without touching the listing itself —
     * spec §20/§40), and its active variants for the optional variant picker.
     */
    public function listingPrefill(Listing $listing)
    {
        abort_unless($listing->supplier_account_id === $this->currentAccount()->id, 404);

        return response()->json([
            'item' => [
                'offered_listing_id' => $listing->id,
                'item_name' => $listing->name,
                'description' => $listing->short_description,
                'quantity' => (string) ($listing->min_order_quantity ?: 1),
                'unit_id' => $listing->unit_id,
                'unit_price' => $listing->base_price,
            ],
            'attribute_values' => $listing->attributeValues->mapWithKeys(fn ($v) => [
                $v->attribute_id => [
                    'attribute_value_id' => $v->attribute_value_id,
                    'custom_value' => $v->custom_value,
                    'value_text' => $v->value_text,
                    'value_number' => $v->value_number,
                    'value_boolean' => $v->value_boolean,
                    'value_date' => $v->value_date,
                    'value_json' => $v->value_json,
                ],
            ])->all(),
            'variants' => $listing->variants()->active()->get(['id', 'name', 'sku', 'price'])->map(fn ($v) => [
                'id' => $v->id,
                'label' => trim(($v->name ?: 'Variant').' — '.($v->sku ?: '')),
                'price' => $v->price,
            ]),
        ]);
    }
}
