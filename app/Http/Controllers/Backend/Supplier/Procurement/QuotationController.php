<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Procurement\QuotationAutosaveRequest;
use App\Http\Requests\Backend\Supplier\Procurement\SaveQuotationRequest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\Unit;
use App\Services\QuotationService;
use App\Services\SupplierRfqActionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    use InteractsWithSupplierAccount;

    private const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'shortlisted' => 'Shortlisted',
        'awarded' => 'Awarded',
        'rejected' => 'Rejected',
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

    public function create(Request $request, Rfq $rfq, SupplierRfqActionService $actions, QuotationService $service)
    {
        $account = $this->currentAccount();

        // Checked before authorize() — QuotationPolicy::create() also denies
        // once a quotation exists (one live quotation per supplier per RFQ),
        // which would throw a 403 here instead of letting a supplier who
        // re-clicks "Submit Quote" land back on their existing quotation.
        $existing = $account->quotations()->where('rfq_id', $rfq->id)->first();
        if ($existing) {
            if ($existing->status === 'draft') {
                if ($request->filled('clone_from')) {
                    $cloneSource = $account->quotations()
                        ->where('id', $request->integer('clone_from'))
                        ->where('rfq_id', '!=', $rfq->id)
                        ->first();

                    if ($cloneSource) {
                        $cloneMatches = $service->matchQuotationToRfq($cloneSource, $rfq);
                        $itemsData = [];
                        foreach ($rfq->items as $item) {
                            $clone = $cloneMatches[$item->id] ?? null;
                            $itemsData[] = [
                                'rfq_item_id' => $item->id,
                                'item_name' => $item->item_name,
                                'description' => $clone['description'] ?? null,
                                'quantity' => $item->quantity,
                                'unit_id' => $item->unit_id,
                                'custom_unit' => $item->custom_unit,
                                'unit_price' => $clone['unit_price'] ?? null,
                                'tax_rate' => $clone['tax_rate'] ?? null,
                                'discount_amount' => $clone['discount_amount'] ?? null,
                                'lead_time_days' => $clone['lead_time_days'] ?? null,
                                'attribute_values' => $clone['attribute_values'] ?? [],
                            ];
                        }
                        $service->saveDraft($rfq, $account, $this->currentUser(), [
                            'currency_code' => $cloneSource->currency_code ?? $rfq->currency_code,
                            'items' => $itemsData,
                        ], $existing);
                    }
                }

                return redirect()->route('supplier.quotations.edit', $existing);
            }

            return redirect()->route('supplier.quotations.show', $existing);
        }

        $this->authorize('create', [Quotation::class, $rfq]);

        $actions->record($rfq, $account, 'preparing_quote');

        $rfq->load([
            'items.unit',
            'items.category',
            'items.listing.attributeValues.attribute.attributeGroup',
            'items.listing.media',
            'items.media',
            'items.attributeValues.attribute.attributeGroup',
            'items.attributeValues.attribute.unit',
            'items.attributeValues.attributeValue',
            'buyerAccount.buyerProfile'
        ]);

        // "Start from a previous quotation" — ownership scoped inline since
        // clone_from is a raw, user-suppliable query param; never trust it
        // belongs to this supplier without this where() clause.
        $cloneSource = null;
        $cloneMatches = [];
        if ($request->filled('clone_from')) {
            $cloneSource = $account->quotations()
                ->where('id', $request->integer('clone_from'))
                ->where('rfq_id', '!=', $rfq->id)
                ->first();

            if ($cloneSource) {
                $cloneMatches = $service->matchQuotationToRfq($cloneSource, $rfq);
            }
        }

        $itemsData = [];
        if ($cloneSource && !empty($cloneMatches)) {
            foreach ($rfq->items as $item) {
                if (isset($cloneMatches[$item->id])) {
                    $clone = $cloneMatches[$item->id];
                    $itemsData[] = [
                        'rfq_item_id' => $item->id,
                        'item_name' => $item->item_name,
                        'description' => $clone['description'] ?? null,
                        'quantity' => $item->quantity,
                        'unit_id' => $item->unit_id,
                        'custom_unit' => $item->custom_unit,
                        'unit_price' => $clone['unit_price'] ?? null,
                        'tax_rate' => $clone['tax_rate'] ?? null,
                        'discount_amount' => $clone['discount_amount'] ?? null,
                        'lead_time_days' => $clone['lead_time_days'] ?? null,
                        'attribute_values' => $clone['attribute_values'] ?? [],
                    ];
                }
            }
        }

        $quotation = $service->saveDraft($rfq, $account, $this->currentUser(), [
            'currency_code' => $cloneSource?->currency_code ?? $rfq->currency_code,
            'current_step' => 1,
            'max_completed_step' => 1,
            'items' => $itemsData,
        ]);

        return redirect()->route('supplier.quotations.edit', $quotation);
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

    /**
     * POST supplier/quotations/create/{rfq}/autosave — fired on step
     * transitions (never on keystroke) while filling out a brand-new
     * quotation, before it has an id yet.
     */
    public function autosaveCreate(QuotationAutosaveRequest $request, Rfq $rfq, QuotationService $service)
    {
        $this->authorize('create', [Quotation::class, $rfq]);

        try {
            $quotation = $service->saveDraft($rfq, $this->currentAccount(), $this->currentUser(), $request->validated());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return response()->json([
            'id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'items' => $service->getLastClientRefItemIds(),
            'offers' => $service->getLastClientRefOfferIds(),
        ]);
    }

    /**
     * GET supplier/quotations/create/{rfq}/auto-match — one bulk lookup
     * (instead of N per-item searchListings() calls) suggesting, for each
     * RFQ item, the supplier's own best-matching approved listing by
     * category + name similarity. A suggestion only, never applied without
     * the supplier clicking "Use it" client-side — quantities/pricing on
     * this RFQ may legitimately differ from the listing's own defaults.
     */
    public function autoMatchListings(Rfq $rfq)
    {
        $this->authorize('selectProducts', [Quotation::class, $rfq]);

        $account = $this->currentAccount();
        $ownListings = $account ? $account->listings()
            ->where('approval_status', 'approved')
            ->get(['id', 'name', 'main_category_id']) : collect();

        $listings = $ownListings->isNotEmpty()
            ? $ownListings
            : Listing::where('approval_status', 'approved')
                ->where('is_active', true)
                ->get(['id', 'name', 'main_category_id']);

        $matches = [];

        foreach ($rfq->items as $item) {
            $best = null;
            $bestScore = 0.0;

            foreach ($listings as $listing) {
                $score = 0.0;
                if ($item->category_id && $listing->main_category_id === $item->category_id) {
                    $score += 0.6;
                }
                similar_text(strtolower($item->item_name ?? ''), strtolower($listing->name ?? ''), $pct);
                $score += 0.4 * ($pct / 100);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $listing;
                }
            }

            if ($best && $bestScore >= 0.35) {
                $matches[$item->id] = [
                    'listing_id' => $best->id,
                    'name' => $best->name,
                    'score' => round($bestScore, 2),
                ];
            }
        }

        return response()->json($matches);
    }

    public function show(Quotation $quotation)
    {
        $this->authorize('view', $quotation);

        $quotation->load([
            'rfq.buyerAccount.buyerProfile',
            'rfq.items.unit',
            'rfq.items.category',
            'rfq.items.listing.attributeValues.attribute.attributeGroup',
            'rfq.items.listing.attributeValues.attributeValue',
            'rfq.items.listing.media',
            'rfq.items.media',
            'rfq.items.attributeValues.attribute.attributeGroup',
            'rfq.items.attributeValues.attribute.unit',
            'rfq.items.attributeValues.attributeValue',
            'items.attributeValues.attribute.unit',
            'items.attributeValues.attributeValue',
            'items.offers.marketplaceProduct',
            'items.offers.offeredVariant',
            'items.offers.unit',
            'items.offers.media',
            'items.offers.attributeValues.attribute.attributeGroup',
            'items.offers.attributeValues.attribute.unit',
            'items.offers.attributeValues.attributeValue',
            'items.offeredListing',
            'items.offeredVariant',
            'items.unit',
            'revisions.items',
            'revisionRequests' => fn($q) => $q->latest(),
            'activities' => fn($q) => $q->with('user')->latest(),
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
            'stats' => $this->computeQuotationStatistics($quotation),
            // Events the BUYER initiated — filtered cleanly by actor_role === 'buyer'
            'buyerActivity' => $quotation->activities->where('actor_role', 'buyer')->values(),
        ]);
    }

    /**
     * GET supplier/quotations/{quotation}/statistics — powers the shared
     * Statistics modal on the index page, mirroring RfqController::statistics().
     */
    public function statistics(Quotation $quotation)
    {
        $this->authorize('view', $quotation);

        $quotation->loadMissing('activities');

        return response()->json($this->computeQuotationStatistics($quotation));
    }

    private function computeQuotationStatistics(Quotation $quotation): array
    {
        $activities = $quotation->activities()->latest()->get();

        $viewedAt = $activities->firstWhere('activity_type', 'viewed_by_buyer')?->created_at;
        $messagesCount = $activities->whereIn('activity_type', ['buyer_messaged', 'supplier_replied'])->count();
        $revisionRequests = $activities->where('activity_type', 'buyer_requested_revision')->count();
        $lastActivity = $activities->first();

        return [
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'status' => $quotation->status,
            'submitted_at_human' => $quotation->submitted_at?->diffForHumans(),
            'viewed_by_buyer' => $viewedAt !== null,
            'viewed_at_human' => $viewedAt?->diffForHumans(),
            'messages_count' => $messagesCount,
            'revision_requests_count' => $revisionRequests,
            'revision_no' => $quotation->current_revision_no,
            'last_activity_label' => $lastActivity?->label(),
            'last_activity_human' => $lastActivity?->created_at->diffForHumans(),
            'recent_activities' => $activities->take(5)->map(fn($a) => [
                'label' => $a->label(),
                'message' => $a->message,
                'icon' => $a->icon(),
                'color_class' => $a->colorClass(),
                'created_at_human' => $a->created_at->diffForHumans(),
            ])->values(),
        ];
    }

    public function edit(Quotation $quotation)
    {
        $this->authorize('editDraft', $quotation);

        $quotation->load([
            'rfq.items.unit',
            'rfq.items.category',
            'rfq.items.listing.attributeValues.attribute.attributeGroup',
            'rfq.items.listing.media',
            'rfq.items.media',
            'rfq.items.attributeValues.attribute.attributeGroup',
            'rfq.items.attributeValues.attribute.unit',
            'rfq.items.attributeValues.attributeValue',
            'items.offers.attributeValues.attribute.unit',
            'items.offers.attributeValues.attributeValue',
            'items.offers.marketplaceProduct.primaryImage',
            'items.offers.marketplaceProduct.mainCategory',
            'items.offers.marketplaceProduct.brand',
            'items.offers.unit',
            'deliveryAddresses',
        ]);

        $rfq = $quotation->rfq;
        $rfq?->loadMissing('buyerAccount.buyerProfile');

        $account = $this->currentAccount();
        $previousQuotations = $account->quotations()
            ->where('rfq_id', '!=', $quotation->rfq_id)
            ->whereIn('status', ['draft', 'submitted', 'under_review', 'revised', 'shortlisted', 'awarded', 'rejected'])
            ->with('rfq')
            ->latest()
            ->limit(20)
            ->get();

        return view('backend.supplier.procurement.quotations.edit', [
            'account' => $account,
            'user' => $this->currentUser(),
            'quotation' => $quotation,
            'rfq' => $rfq,
            'previousQuotations' => $previousQuotations,
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

    /**
     * POST supplier/quotations/{quotation}/autosave — fired on step
     * transitions once the draft already has an id (client switches over
     * to this endpoint the moment autosaveCreate() returns one).
     */
    public function autosaveUpdate(QuotationAutosaveRequest $request, Quotation $quotation, QuotationService $service)
    {
        $this->authorize('editDraft', $quotation);

        try {
            $updated = $service->saveDraft($quotation->rfq, $this->currentAccount(), $this->currentUser(), $request->validated(), $quotation);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return response()->json([
            'success' => true,
            'items' => $service->getLastClientRefItemIds(),
            'offers' => $service->getLastClientRefOfferIds(),
        ]);
    }

    public function submit(Request $request, Quotation $quotation, QuotationService $service)
    {
        $this->authorize('submitDraft', $quotation);

        try {
            $service->submitDraft($quotation, $request->boolean('acknowledge_version_change'), $this->currentUser());
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $redirect = redirect()->route('supplier.quotations.show', $quotation)->withErrors($errors);

            // rfq_version already gets its own persistent banner with change
            // details on the show page — avoid a redundant toast for it.
            if (!isset($errors['rfq_version'])) {
                $redirect->with('error', collect($errors)->flatten()->first());
            }

            return $redirect;
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
     * Pulls a submitted quotation back to draft — the "Undo Submit" action.
     * Redirects to Edit since the natural next step is making a change and
     * resubmitting.
     */
    public function undoSubmit(Quotation $quotation, QuotationService $service)
    {
        $this->authorize('undoSubmit', $quotation);

        $service->undoSubmit($quotation, $this->currentUser());

        return redirect()->route('supplier.quotations.edit', $quotation)->with('success', 'Quotation moved back to draft — make your changes and submit again when ready.');
    }

    /**
     * POST supplier/quotations/{quotation}/combined-document — a single,
     * supplementary attachment for the whole quotation (Step 3), distinct
     * from a per-item "Upload Quotation Document" response method. Mirrors
     * QuotationItemDocumentController::store()'s exact validation/upload
     * pattern, one file per request.
     */
    public function uploadCombinedDocument(Request $request, Quotation $quotation)
    {
        $this->authorize('editDraft', $quotation);

        $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip,csv,txt'],
        ]);

        $file = $request->file('document');
        $fileName = \App\Support\Media\QuotationDocumentPathGenerator::supplierFileName($quotation->supplier_account_id, $file->getClientOriginalName());
        $media = $quotation->addMedia($file)
            ->usingFileName($fileName)
            ->toMediaCollection('combined_document');

        return response()->json([
            'id' => $media->id,
            'name' => $media->file_name,
            'size' => $media->human_readable_size,
            'url' => $media->getUrl(),
            'is_image' => str_starts_with($media->mime_type ?? '', 'image/'),
        ]);
    }

    /**
     * DELETE supplier/quotations/{quotation}/combined-document/{media}
     */
    public function deleteCombinedDocument(Quotation $quotation, int $media)
    {
        $this->authorize('editDraft', $quotation);

        $quotation->getMedia('combined_document')->where('id', $media)->first()?->delete();

        return response()->json(['success' => true]);
    }

    /**
     * GET supplier/quotations/categories/{category}/attributes — identical
     * contract to the buyer module's equivalent endpoint, so the offer form
     * renders the same attribute set the buyer's requirement was built from.
     *
     * keep_attribute_ids: comma-separated attribute ids the offer item
     * already has a saved value for (editing/revising an existing
     * quotation) — so a since-deactivated attribute still shows instead of
     * silently disappearing.
     */
    public function categoryAttributes(Request $request, Category $category)
    {
        $keepIds = collect(explode(',', (string) $request->query('keep_attribute_ids', '')))
            ->map(fn($id) => (int) trim($id))
            ->filter()
            ->values()
            ->all();

        return response()->json($category->attributesGroupedForForm($keepIds));
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

        $account = $this->currentAccount();
        $listings = Listing::query()
            ->where(function (Builder $query) use ($account) {
                $query->where(function (Builder $q) {
                    $q->published()
                        ->orWhere(function (Builder $p) {
                            $p->where('approval_status', 'approved')
                                ->where('is_active', true);
                        });
                });
                if ($account) {
                    $query->orWhere(function (Builder $q) use ($account) {
                        $q->where('supplier_account_id', $account->id)
                            ->where('approval_status', 'approved');
                    });
                }
            })
            ->where('name', 'like', '%' . $request->string('q') . '%')
            ->when($request->filled('category_id'), fn($q) => $q->where('main_category_id', $request->integer('category_id')))
            ->with('mainCategory', 'unit')
            ->limit(15)
            ->get()
            ->map(fn(Listing $l) => [
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
        abort_unless(
            $listing->isPublished()
            || $listing->approval_status === 'approved'
            || ($this->currentAccount() && $listing->supplier_account_id === $this->currentAccount()->id),
            404
        );

        $listing->load(['mainCategory', 'primaryImage', 'brand', 'attributeValues.attribute.unit', 'attributeValues.attributeValue']);

        $imageUrl = $listing->primaryImage?->getUrl()
            ?? ($listing->relationLoaded('media') && $listing->media->isNotEmpty() ? $listing->media->first()?->getUrl() : null)
            ?? $listing->getFirstMediaUrl('gallery')
            ?: null;

        return response()->json([
            'item' => [
                'offered_listing_id' => $listing->id,
                'marketplace_product_id' => $listing->id,
                'item_name' => $listing->name,
                'product_name' => $listing->name,
                'description' => $listing->short_description ?: $listing->description,
                'quantity' => (string) ($listing->min_order_quantity ?: 1),
                'unit_id' => $listing->unit_id,
                'unit_price' => $listing->base_price,
                'category_id' => $listing->main_category_id,
                'category_name' => $listing->mainCategory?->name,
                'brand_name' => $listing->brand?->name,
                'image_url' => $imageUrl,
                'slug' => $listing->slug,
            ],
            'category_attributes' => $listing->mainCategory ? $listing->mainCategory->attributesGroupedForForm() : null,
            'attribute_values' => $listing->attributeValues->mapWithKeys(fn($v) => [
                $v->attribute_id => [
                    'attribute_name' => $v->attribute?->name,
                    'attribute_value_id' => $v->attribute_value_id,
                    'custom_value' => $v->custom_value,
                    'value_text' => $v->value_text,
                    'value_number' => $v->value_number,
                    'value_boolean' => $v->value_boolean,
                    'value_date' => $v->value_date,
                    'value_json' => $v->value_json,
                ],
            ])->all(),
            'variants' => $listing->variants()->active()->get(['id', 'name', 'sku', 'price'])->map(fn($v) => [
                'id' => $v->id,
                'label' => trim(($v->name ?: 'Variant') . ' — ' . ($v->sku ?: '')),
                'price' => $v->price,
            ]),
        ]);
    }
}
