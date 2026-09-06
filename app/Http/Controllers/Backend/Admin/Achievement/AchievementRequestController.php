<?php

namespace App\Http\Controllers\Backend\Admin\Achievement;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\AccountAchievement;
use Illuminate\Http\Request;

class AchievementRequestController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.achievements.review');

        $status = $request->string('status')->toString();

        $requests = AccountAchievement::query()
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('account', fn ($q2) => $q2->where('display_name', 'like', '%'.$request->string('search').'%'));
            })
            ->with(['account', 'achievement', 'reviewedBy'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.achievement-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'search' => $request->string('search')->toString(),
            'counts' => [
                'pending' => AccountAchievement::where('status', 'pending')->count(),
                'approved' => AccountAchievement::where('status', 'approved')->count(),
                'rejected' => AccountAchievement::where('status', 'rejected')->count(),
                'all' => AccountAchievement::count(),
            ],
        ]);
    }

    public function approve(AccountAchievement $accountAchievement)
    {
        $this->authorize('platform.achievements.review');

        abort_unless($accountAchievement->status === 'pending', 422, 'Only a pending request can be approved.');

        $accountAchievement->update([
            'status' => 'approved',
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
            'earned_at' => now(),
        ]);

        return back()->with('success', 'Achievement claim approved.');
    }

    public function reject(AccountAchievement $accountAchievement)
    {
        $this->authorize('platform.achievements.review');

        abort_unless($accountAchievement->status === 'pending', 422, 'Only a pending request can be rejected.');

        $accountAchievement->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Achievement claim rejected.');
    }

    /**
     * Revert an approved or rejected claim back to pending so admin has
     * full authority over the decision at any time, not just once.
     */
    public function undo(AccountAchievement $accountAchievement)
    {
        $this->authorize('platform.achievements.review');

        abort_unless(in_array($accountAchievement->status, ['approved', 'rejected'], true), 422, 'Only an approved or rejected request can be undone.');

        $accountAchievement->update([
            'status' => 'pending',
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'earned_at' => null,
        ]);

        return back()->with('success', 'Achievement claim decision undone — back to pending.');
    }
}
