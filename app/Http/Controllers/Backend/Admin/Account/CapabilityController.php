<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\AccountCapability;
use App\Services\CapabilityReviewService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CapabilityController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.capabilities.review');

        $capabilities = AccountCapability::query()
            ->whereHas('account', fn ($q) => $q->where('is_system_account', false))
            ->when($request->filled('type'), fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', $request->string('type'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('account', fn ($q2) => $q2->where('display_name', 'like', '%'.$request->string('search').'%'));
            })
            ->with(['account', 'capabilityType'])
            ->latest('applied_at')
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.capabilities.index', [
            'capabilities' => $capabilities,
            'type' => $request->string('type')->toString(),
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(AccountCapability $capability)
    {
        $this->authorize('platform.capabilities.review');

        return view('backend.admin.capabilities.show', $this->loadForReview($capability));
    }

    /** Same data as show(), rendered without the layout — fetched into the Approval Center's review modal. */
    public function panel(AccountCapability $capability)
    {
        $this->authorize('platform.capabilities.review');

        return view('backend.admin.capabilities._panel', $this->loadForReview($capability));
    }

    private function loadForReview(AccountCapability $capability): array
    {
        $capability->load([
            'account.buyerProfile', 'account.supplierProfile', 'capabilityType',
            'applicationHistory' => fn ($q) => $q->latest(),
            'appliedBy', 'reviewedBy',
        ]);

        $documents = $capability->capabilityType->code === 'supplier'
            ? $capability->account->supplierDocuments()->with('documentType')->current()->get()
            : collect();

        return ['capability' => $capability, 'documents' => $documents];
    }

    private function firstValidationMessage(ValidationException $e): string
    {
        return collect($e->errors())->flatten()->first() ?? $e->getMessage();
    }

    public function approve(Request $request, AccountCapability $capability, CapabilityReviewService $service)
    {
        $this->authorize('platform.capabilities.review');

        try {
            $service->approve($capability, $this->admin());
        } catch (\RuntimeException|ValidationException $e) {
            $message = $e instanceof ValidationException ? $this->firstValidationMessage($e) : $e->getMessage();

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        activity('moderation')->causedBy($this->admin())->performedOn($capability)->log('Capability approved');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Application approved.', 'resolved' => true]);
        }

        return back()->with('success', 'Application approved.');
    }

    public function undoApprove(Request $request, AccountCapability $capability, CapabilityReviewService $service)
    {
        $this->authorize('platform.capabilities.review');

        try {
            $service->undoApprove($capability, $this->admin());
        } catch (\RuntimeException|ValidationException $e) {
            $message = $e instanceof ValidationException ? $this->firstValidationMessage($e) : $e->getMessage();

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        activity('moderation')->causedBy($this->admin())->performedOn($capability)->log('Capability approval undone (reverted to pending)');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Capability approval reverted to pending.', 'resolved' => false]);
        }

        return back()->with('success', 'Capability approval reverted to pending.');
    }

    public function revision(ReasonRequest $request, AccountCapability $capability, CapabilityReviewService $service)
    {
        $this->authorize('platform.capabilities.review');

        try {
            $service->requestRevision($capability, $this->admin(), $request->string('reason'));
        } catch (\RuntimeException|ValidationException $e) {
            $message = $e instanceof ValidationException ? $this->firstValidationMessage($e) : $e->getMessage();

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        activity('moderation')->causedBy($this->admin())->performedOn($capability)
            ->withProperties(['reason' => $request->string('reason')])->log('Capability revision requested');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Revision requested.', 'resolved' => true]);
        }

        return back()->with('success', 'Revision requested.');
    }

    public function reject(ReasonRequest $request, AccountCapability $capability, CapabilityReviewService $service)
    {
        $this->authorize('platform.capabilities.review');

        try {
            $service->reject($capability, $this->admin(), $request->string('reason'));
        } catch (\RuntimeException|ValidationException $e) {
            $message = $e instanceof ValidationException ? $this->firstValidationMessage($e) : $e->getMessage();

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        activity('moderation')->causedBy($this->admin())->performedOn($capability)
            ->withProperties(['reason' => $request->string('reason')])->log('Capability rejected');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Application rejected.', 'resolved' => true]);
        }

        return back()->with('success', 'Application rejected.');
    }

    public function suspend(ReasonRequest $request, AccountCapability $capability)
    {
        $this->authorize('platform.capabilities.review');

        \Illuminate\Support\Facades\DB::transaction(function () use ($capability, $request) {
            $capability = AccountCapability::whereKey($capability->id)->lockForUpdate()->firstOrFail();

            if ($capability->status !== 'active') {
                throw ValidationException::withMessages(['status' => 'Only an active capability can be suspended.']);
            }

            $capability->update(['status' => 'suspended', 'suspension_reason' => $request->string('reason')]);
        });

        activity('moderation')->causedBy($this->admin())->performedOn($capability)
            ->withProperties(['reason' => $request->string('reason')])->log('Capability suspended');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Capability suspended.', 'resolved' => false]);
        }

        return back()->with('success', 'Capability suspended.');
    }

    public function reactivate(Request $request, AccountCapability $capability)
    {
        $this->authorize('platform.capabilities.review');

        \Illuminate\Support\Facades\DB::transaction(function () use ($capability) {
            $capability = AccountCapability::whereKey($capability->id)->lockForUpdate()->firstOrFail();

            if ($capability->status !== 'suspended') {
                throw ValidationException::withMessages(['status' => 'Only a suspended capability can be reactivated.']);
            }

            $capability->update(['status' => 'active', 'suspension_reason' => null]);
        });

        activity('moderation')->causedBy($this->admin())->performedOn($capability)->log('Capability reactivated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Capability reactivated.', 'resolved' => false]);
        }

        return back()->with('success', 'Capability reactivated.');
    }
}
