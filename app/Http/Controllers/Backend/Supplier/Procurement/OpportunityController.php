<?php

namespace App\Http\Controllers\Backend\Supplier\Procurement;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Models\RfqQuestion;
use App\Models\RfqSupplierQueue;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        // RFQs queued for this supplier
        $queueQuery = RfqSupplierQueue::where('supplier_account_id', $account->id)
            ->released()
            ->with(['rfq' => fn ($q) => $q->with(['buyerAccount.buyerProfile', 'items'])]);

        if ($request->get('filter') === 'invited') {
            $queueQuery->where('source', 'direct_invitation');
        }

        $opportunities = $queueQuery->latest()->paginate(10)->withQueryString();

        return view('backend.supplier.procurement.opportunities.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'opportunities' => $opportunities,
            'filter' => $request->get('filter'),
        ]);
    }

    public function show(Rfq $rfq)
    {
        $this->authorize('viewAsOpportunity', $rfq);

        $account = $this->currentAccount();

        $rfq->load(['buyerAccount.buyerProfile', 'items.unit', 'questions' => fn ($q) => $q->where('status', 'answered')->orWhere('asked_by_account_id', $account->id)->with('answers')]);

        $existingQuotation = $account->quotations()->where('rfq_id', $rfq->id)->with(['items', 'revisions'])->first();

        return view('backend.supplier.procurement.opportunities.show', [
            'account' => $account,
            'user' => $this->currentUser(),
            'rfq' => $rfq,
            'existingQuotation' => $existingQuotation,
        ]);
    }

    public function askQuestion(Request $request, Rfq $rfq)
    {
        $this->authorize('askQuestion', $rfq);

        $account = $this->currentAccount();

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        RfqQuestion::create([
            'rfq_id' => $rfq->id,
            'asked_by_account_id' => $account->id,
            'asked_by_user_id' => $this->currentUser()->id,
            'question' => $validated['question'],
            'status' => 'pending',
        ]);

        return redirect()->route('supplier.opportunities.show', $rfq)->with('success', 'Your question has been sent to the buyer.');
    }
}
