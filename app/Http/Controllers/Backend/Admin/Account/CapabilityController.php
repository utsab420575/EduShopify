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

        $capability->load([
            'account.buyerProfile', 'account.supplierProfile', 'capabilityType',
            'applicationHistory' => fn ($q) => $q->latest(),
            'appliedBy', 'reviewedBy',
        ]);

        $documents = $capability->capabilityType->code === 'supplier'
            ? $capability->account->supplierDocuments()->with('documentType')->current()->get()
            : collect();

        return view('backend.admin.capabilities.show', ['capability' => $capability, 'documents' => $documents]);
    }

    public function approve(AccountCapability $capability, CapabilityReviewService $service)
    {
        $this->authorize('platform.capabilities.review');

        try {
            $service->approve($capability, $this->admin());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($capability)->log('Capability approved');

        return back()->with('success', 'Application approved.');
    }

    public function revision(ReasonRequest $request, AccountCapability $capability, CapabilityReviewService $service)
    {
        $this->authorize('platform.capabilities.review');

        try {
            $service->requestRevision($capability, $this->admin(), $request->string('reason'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($capability)
            ->withProperties(['reason' => $request->string('reason')])->log('Capability revision requested');

        return back()->with('success', 'Revision requested.');
    }

    public function reject(ReasonRequest $request, AccountCapability $capability, CapabilityReviewService $service)
    {
        $this->authorize('platform.capabilities.review');

        try {
            $service->reject($capability, $this->admin(), $request->string('reason'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        activity('moderation')->causedBy($this->admin())->performedOn($capability)
            ->withProperties(['reason' => $request->string('reason')])->log('Capability rejected');

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

        return back()->with('success', 'Capability suspended.');
    }

    public function reactivate(AccountCapability $capability)
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

        return back()->with('success', 'Capability reactivated.');
    }
}
