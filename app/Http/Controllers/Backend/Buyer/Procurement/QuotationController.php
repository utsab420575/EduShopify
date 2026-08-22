<?php

namespace App\Http\Controllers\Backend\Buyer\Procurement;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Procurement\AwardQuotationRequest;
use App\Http\Requests\Backend\Buyer\Procurement\RejectQuotationRequest;
use App\Http\Requests\Backend\Buyer\Procurement\RequestQuotationRevisionRequest;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\Rfq;
use App\Services\AwardService;
use App\Services\QuotationDecisionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Quotation::class);

        $account = $this->currentAccount();
        $rfqIds = $account->rfqs()->pluck('id');

        $quotations = Quotation::whereIn('rfq_id', $rfqIds)
            ->when($request->filled('rfq'), fn ($q) => $q->where('rfq_id', $request->integer('rfq')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['rfq', 'supplierAccount.supplierProfile'])
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('backend.buyer.procurement.quotations.index', [
            'quotations' => $quotations,
            'rfq' => $request->integer('rfq'),
            'status' => $request->string('status')->toString(),
            'rfqOptions' => $account->rfqs()->orderByDesc('id')->get(['id', 'title', 'rfq_number']),
            'statusOptions' => [
                'submitted' => 'Submitted', 'under_review' => 'Under Review', 'shortlisted' => 'Shortlisted',
                'revision_requested' => 'Revision Requested', 'revised' => 'Revised', 'rejected' => 'Rejected', 'awarded' => 'Awarded',
            ],
        ]);
    }

    public function compare(Rfq $rfq)
    {
        $this->authorize('compare', $rfq);

        $rfq->load([
            'quotations' => fn ($q) => $q->submitted()->with(['supplierAccount.supplierProfile', 'items', 'shortlists']),
            'items',
        ]);

        return view('backend.buyer.procurement.quotations.compare', ['rfq' => $rfq]);
    }

    public function show(Quotation $quotation, QuotationDecisionService $decisions)
    {
        $this->authorize('view', $quotation);

        $quotation->load([
            'rfq.items', 'supplierAccount.supplierProfile',
            'items.rfqItem', 'items.offeredListing', 'items.unit',
            'shortlists',
            'revisions' => fn ($q) => $q->orderByDesc('revision_no'),
            'revisions.items',
            'revisions.createdBy',
        ]);

        $decisions->markViewed($quotation);

        return view('backend.buyer.procurement.quotations.show', [
            'quotation' => $quotation,
            'isShortlisted' => $quotation->shortlists->isNotEmpty(),
            'canReview' => \Illuminate\Support\Facades\Gate::allows('createForQuotation', [Review::class, $quotation]),
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
}
