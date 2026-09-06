<?php

namespace App\Http\Controllers\Backend\Admin\Achievement;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Certification;
use Illuminate\Http\Request;

class CertificationRequestController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.certifications.review');

        $status = $request->string('status')->toString();

        $certifications = Certification::query()
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('account', fn ($q2) => $q2->where('display_name', 'like', '%'.$request->string('search').'%'));
            })
            ->with(['account', 'approvedBy'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.certification-requests.index', [
            'certifications' => $certifications,
            'status' => $status,
            'search' => $request->string('search')->toString(),
            'counts' => [
                'pending' => Certification::where('status', 'pending')->count(),
                'approved' => Certification::where('status', 'approved')->count(),
                'rejected' => Certification::where('status', 'rejected')->count(),
                'all' => Certification::count(),
            ],
        ]);
    }

    public function approve(Certification $certification)
    {
        $this->authorize('platform.certifications.review');

        abort_unless($certification->status === 'pending', 422, 'Only a pending certification can be approved.');

        $certification->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'approved_by_user_id' => $this->admin()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Certification approved.');
    }

    public function reject(ReasonRequest $request, Certification $certification)
    {
        $this->authorize('platform.certifications.review');

        abort_unless($certification->status === 'pending', 422, 'Only a pending certification can be rejected.');

        $certification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->string('reason'),
            'approved_by_user_id' => $this->admin()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Certification rejected.');
    }

    /**
     * Revert an approved or rejected certification back to pending so admin
     * has full authority over the decision at any time, not just once.
     */
    public function undo(Certification $certification)
    {
        $this->authorize('platform.certifications.review');

        abort_unless(in_array($certification->status, ['approved', 'rejected'], true), 422, 'Only an approved or rejected certification can be undone.');

        $certification->update([
            'status' => 'pending',
            'rejection_reason' => null,
            'approved_by_user_id' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'Certification decision undone — back to pending.');
    }
}
