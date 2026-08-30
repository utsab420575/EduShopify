<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Procurement\DeclineOpportunityRequest;
use App\Models\Rfq;
use App\Models\RfqSupplierQueue;
use App\Services\RfqOpportunityService;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    use InteractsWithSupplierAccount;

    private const FILTER_OPTIONS = [
        'all' => 'All',
        'new' => 'New',
        'direct' => 'Direct',
        'invited' => 'Invited',
        'open_matching' => 'Open Matching',
        'broadcast' => 'Broadcast',
        'viewed' => 'Viewed',
        'declined' => 'Declined',
    ];

    public function index(Request $request)
    {
        $account = $this->currentAccount();
        $filter = $request->get('filter', 'all');

        $queueQuery = RfqSupplierQueue::where('supplier_account_id', $account->id)
            ->released()
            ->with(['rfq' => fn ($q) => $q->with(['buyerAccount.buyerProfile', 'items', 'visibilityType'])]);

        match ($filter) {
            'new' => $queueQuery->whereNull('seen_at'),
            'viewed' => $queueQuery->whereNotNull('seen_at'),
            'direct' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'direct')),
            'invited' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'invited')),
            'open_matching' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'open_matching')),
            'broadcast' => $queueQuery->whereHas('rfq.visibilityType', fn ($q) => $q->where('code', 'broadcast_all')),
            'declined' => $queueQuery->where('status', 'ignored'),
            default => $queueQuery->where('status', '!=', 'ignored'),
        };

        $opportunities = $queueQuery->latest()->paginate(10)->withQueryString();

        return view('backend.supplier.procurement.opportunities.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'opportunities' => $opportunities,
            'filter' => $filter,
            'filterOptions' => self::FILTER_OPTIONS,
        ]);
    }

    public function show(Rfq $rfq, RfqOpportunityService $service)
    {
        $this->authorize('viewAsOpportunity', $rfq);

        $account = $this->currentAccount();

        $rfq->load([
            'buyerAccount.buyerProfile',
            'items.unit', 'items.category', 'items.attributeValues.attribute.unit', 'items.attributeValues.attributeValue',
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

        return redirect()->route('supplier.opportunities.index')->with('success', 'You have declined this RFQ opportunity.');
    }
}
