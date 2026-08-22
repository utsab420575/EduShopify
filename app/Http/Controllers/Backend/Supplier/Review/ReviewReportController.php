<?php

namespace App\Http\Controllers\Backend\Supplier\Review;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\ReviewReply;
use Illuminate\Http\Request;

class ReviewReportController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(Request $request, Review $review)
    {
        $account = $this->currentAccount();

        // Only the targeted supplier may report a review directed at them
        abort_if($review->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'reason'      => ['required', 'string', 'in:spam,fake,inappropriate,irrelevant,other'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // One active report per supplier per review
        ReviewReport::firstOrCreate(
            ['review_id' => $review->id, 'reporter_account_id' => $account->id],
            [
                'reporter_user_id' => $this->currentUser()->id,
                'reason'           => $validated['reason'],
                'description'      => $validated['description'] ?? null,
                'status'           => 'pending',
            ]
        );

        return redirect()
            ->route('supplier.reviews.index')
            ->with('success', 'Review has been reported to our moderation team for review.');
    }
}
