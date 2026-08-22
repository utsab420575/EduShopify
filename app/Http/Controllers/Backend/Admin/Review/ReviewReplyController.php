<?php

namespace App\Http\Controllers\Backend\Admin\Review;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\ReviewReply;
use Illuminate\Http\Request;

class ReviewReplyController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.reviews.moderate');

        $replies = ReviewReply::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['review.buyerAccount', 'supplierAccount.supplierProfile'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.reviews.replies.index', [
            'replies' => $replies,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function hide(ReasonRequest $request, ReviewReply $reply)
    {
        $this->authorize('platform.reviews.moderate');

        $reply->update([
            'status' => 'hidden',
            'moderated_by_user_id' => $this->admin()->id,
            'moderation_reason' => $request->string('reason'),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($reply)
            ->withProperties(['reason' => $request->string('reason')])->log('Review reply hidden');

        return back()->with('success', 'Reply hidden.');
    }

    public function publish(ReviewReply $reply)
    {
        $this->authorize('platform.reviews.moderate');

        $reply->update([
            'status' => 'published',
            'moderated_by_user_id' => $this->admin()->id,
            'moderation_reason' => null,
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($reply)->log('Review reply published');

        return back()->with('success', 'Reply published.');
    }
}
