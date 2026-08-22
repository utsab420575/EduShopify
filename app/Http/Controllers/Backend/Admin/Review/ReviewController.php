<?php

namespace App\Http\Controllers\Backend\Admin\Review;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Review;
use App\Services\ReviewModerationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.reviews.moderate');

        $reviews = Review::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['buyerAccount', 'supplierAccount.supplierProfile'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.reviews.index', [
            'reviews' => $reviews,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Review $review)
    {
        $this->authorize('platform.reviews.moderate');

        $review->load(['buyerAccount', 'supplierAccount.supplierProfile', 'reply', 'reports.reportedByAccount']);

        return view('backend.admin.reviews.show', ['review' => $review]);
    }

    public function publish(Review $review, ReviewModerationService $service)
    {
        $this->authorize('platform.reviews.moderate');

        try {
            $service->publish($review, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Review published.');
    }

    public function hide(ReasonRequest $request, Review $review, ReviewModerationService $service)
    {
        $this->authorize('platform.reviews.moderate');

        $service->hide($review, $this->admin(), $request->string('reason'));

        return back()->with('success', 'Review hidden.');
    }

    public function reject(ReasonRequest $request, Review $review, ReviewModerationService $service)
    {
        $this->authorize('platform.reviews.moderate');

        try {
            $service->reject($review, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Review rejected.');
    }
}
