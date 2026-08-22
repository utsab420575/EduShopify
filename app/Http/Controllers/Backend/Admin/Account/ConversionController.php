<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\AccountConversionRequest;
use App\Services\AccountConversionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConversionController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.conversions.review');

        $conversions = AccountConversionRequest::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('account', fn ($q2) => $q2->where('display_name', 'like', '%'.$request->string('search').'%'));
            })
            ->with('account', 'submittedBy')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.conversions.index', [
            'conversions' => $conversions,
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(AccountConversionRequest $conversion)
    {
        $this->authorize('platform.conversions.review');

        $conversion->load(['account.supplierProfile', 'submittedBy', 'reviewedBy']);

        return view('backend.admin.conversions.show', ['conversion' => $conversion]);
    }

    public function approve(AccountConversionRequest $conversion, AccountConversionService $service)
    {
        $this->authorize('platform.conversions.review');

        try {
            $service->approve($conversion, $this->admin());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($conversion)->log('Account conversion approved');

        return back()->with('success', 'Conversion approved — the account is now an organization.');
    }

    public function revision(ReasonRequest $request, AccountConversionRequest $conversion)
    {
        $this->authorize('platform.conversions.review');

        abort_unless($conversion->status === 'pending', 422, 'Only a pending request can be sent back for revision.');

        $conversion->update([
            'status' => 'revision_required',
            'review_comment' => $request->string('reason'),
            'reviewed_by_user_id' => $this->admin()->id,
            'reviewed_at' => now(),
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($conversion)
            ->withProperties(['reason' => $request->string('reason')])->log('Account conversion revision requested');

        return back()->with('success', 'Revision requested.');
    }

    public function reject(ReasonRequest $request, AccountConversionRequest $conversion, AccountConversionService $service)
    {
        $this->authorize('platform.conversions.review');

        try {
            $service->reject($conversion, $this->admin(), $request->string('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($conversion)
            ->withProperties(['reason' => $request->string('reason')])->log('Account conversion rejected');

        return back()->with('success', 'Conversion rejected.');
    }
}
