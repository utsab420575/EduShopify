<?php

namespace App\Http\Controllers\Backend\Admin\Review;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\ReviewReport;
use Illuminate\Http\Request;

class ReviewReportController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.reviews.moderate');

        $reports = ReviewReport::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('reportedByAccount', fn ($q2) => $q2->where('display_name', 'like', '%'.$request->string('search').'%'));
            })
            ->with(['review.buyerAccount', 'review.supplierAccount.supplierProfile', 'reportedByAccount'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.reviews.reports.index', [
            'reports' => $reports,
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(ReviewReport $report)
    {
        $this->authorize('platform.reviews.moderate');

        $report->load(['review.buyerAccount', 'review.supplierAccount.supplierProfile', 'reportedByAccount', 'reportedBy']);

        return view('backend.admin.reviews.reports.show', ['report' => $report]);
    }

    public function dismiss(ReviewReport $report)
    {
        $this->authorize('platform.reviews.moderate');

        abort_unless($report->status === 'pending', 422, 'Only a pending report can be dismissed.');

        $report->update([
            'status' => 'dismissed',
            'review_action' => 'none',
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report dismissed.');
    }

    public function actionTaken(Request $request, ReviewReport $report)
    {
        $this->authorize('platform.reviews.moderate');

        abort_unless($report->status === 'pending', 422, 'Only a pending report can be actioned.');

        $request->validate(['review_action' => ['required', 'in:hidden,rejected,warned']]);

        $report->update([
            'status' => 'actioned',
            'review_action' => $request->string('review_action'),
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report actioned.');
    }
}
