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

                $preselectedSupplier ??= $listing->supplierAccount()->with('supplierProfile')->first();
            }
        }

        return view('backend.buyer.procurement.rfqs.create', [
            'rfq' => new Rfq(['visibility_type' => 'global', 'allow_partial_quotation' => true, 'allow_alternative_products' => true]),
            'items' => $items,
            'invitedSuppliers' => $preselectedSupplier ? collect([$preselectedSupplier]) : collect(),
        ] + $this->lookups());
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

        $rfq->load(['items', 'invitedSupplierAccounts.supplierProfile']);

        return view('backend.buyer.procurement.rfqs.edit', [
            'rfq' => $rfq,
            'items' => $rfq->items,
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
            'items.category', 'items.unit',
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
            'categories' => Category::active()->approved()->orderBy('name')->get(['id', 'name', 'parent_id']),
            'units' => Unit::active()->orderBy('name')->get(['id', 'name', 'symbol']),
            'currencies' => Currency::active()->orderBy('code')->get(['code', 'name', 'symbol']),
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
