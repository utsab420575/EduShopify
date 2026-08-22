<?php

namespace App\Http\Controllers\Backend\Admin\Procurement;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Award;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.rfqs.moderate');

        $awards = Award::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['rfq', 'buyerAccount', 'supplierAccount.supplierProfile'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.procurement.awards.index', [
            'awards' => $awards,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(Award $award)
    {
        $this->authorize('platform.rfqs.moderate');

        $award->load(['rfq', 'quotation', 'buyerAccount', 'supplierAccount.supplierProfile', 'purchaseOrder']);

        return view('backend.admin.procurement.awards.show', ['award' => $award]);
    }

    public function cancel(ReasonRequest $request, Award $award)
    {
        $this->authorize('platform.rfqs.moderate');

        abort_if(in_array($award->status, ['accepted', 'rejected', 'cancelled', 'expired'], true), 422, 'This award can no longer be cancelled.');

        $award->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($award)
            ->withProperties(['reason' => $request->string('reason')])->log('Award cancelled by admin');

        return back()->with('success', 'Award cancelled.');
    }
}
