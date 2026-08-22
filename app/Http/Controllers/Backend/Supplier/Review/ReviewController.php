<?php

namespace App\Http\Controllers\Backend\Supplier\Review;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();

        $reviews = $account->receivedReviews()
            ->with(['buyerAccount.buyerProfile', 'rfq', 'purchaseOrder', 'reply'])
            ->latest()
            ->paginate(10);

        return view('backend.supplier.reviews.index', [
            'account' => $account,
            'user' => $this->currentUser(),
            'reviews' => $reviews,
        ]);
    }

    public function reply(Request $request, Review $review)
    {
        $account = $this->currentAccount();
        abort_if($review->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:2000'],
        ]);

        ReviewReply::updateOrCreate(
            [
                'review_id' => $review->id,
                'supplier_account_id' => $account->id,
            ],
            [
                'replied_by_user_id' => $this->currentUser()->id,
                'reply' => $validated['reply'],
                'status' => 'published',
            ]
        );

        return redirect()->route('supplier.reviews.index')->with('success', 'Your response to the review has been published.');
    }
}
