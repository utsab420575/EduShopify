<?php

namespace App\Http\Controllers\Backend\Buyer\Review;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Review\ReviewRequest;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        $account = $this->currentAccount();

        $reviews = $account->writtenReviews()
            ->with(['supplierAccount.supplierProfile', 'rfq', 'reply'])
            ->latest()
            ->paginate(10);

        return view('backend.buyer.reviews.index', ['reviews' => $reviews]);
    }

    public function storeForQuotation(ReviewRequest $request, Quotation $quotation, ReviewService $service)
    {
        $this->authorize('createForQuotation', [Review::class, $quotation]);

        try {
            $service->reviewForQuotation(
                $this->currentAccount(), $this->currentUser(), $quotation,
                (int) $request->input('rating'), $request->input('title'), $request->input('comment')
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Thank you — your review has been submitted for moderation.');
    }

    public function storeForPurchaseOrder(ReviewRequest $request, PurchaseOrder $purchaseOrder, ReviewService $service)
    {
        $this->authorize('createForPurchaseOrder', [Review::class, $purchaseOrder]);

        try {
            $service->reviewForPurchaseOrder(
                $this->currentAccount(), $this->currentUser(), $purchaseOrder,
                (int) $request->input('rating'), $request->input('title'), $request->input('comment')
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Thank you — your review has been submitted for moderation.');
    }
}
