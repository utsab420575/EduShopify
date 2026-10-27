<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Backend\Admin\Concerns\LiveFilters;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use InteractsWithAdmin, LiveFilters;

    private const STATUSES = ['draft' => 'Draft', 'pending' => 'Pending', 'active' => 'Active', 'revision_required' => 'Revision Required', 'rejected' => 'Rejected', 'suspended' => 'Suspended'];

    private const DOCUMENT_STATUSES = ['pending' => 'Has pending', 'verified' => 'Has verified', 'rejected' => 'Has rejected', 'none' => 'No documents'];

    private const TYPES = ['individual' => 'Individual', 'organization' => 'Organization'];

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $search = trim($request->string('search')->toString());
        $selected = [
            'status' => $this->selectedFilter($request, 'status', self::STATUSES),
            // "document_status" keeps the admin dashboard's "pending documents" link working.
            'document_status' => $this->selectedFilter($request, 'document_status', self::DOCUMENT_STATUSES),
            'type' => $this->selectedFilter($request, 'type', self::TYPES),
        ];

        $supplierCapability = fn ($q) => $q->whereHas('capabilityType', fn ($q2) => $q2->where('code', 'supplier'));
        $hasStatus = fn ($q, $statuses) => $q->whereHas('capabilities', fn ($q2) => $supplierCapability($q2)->whereIn('status', (array) $statuses));
        $hasDocuments = fn ($q, string $status) => $status === 'none'
            ? $q->whereDoesntHave('supplierDocuments', fn ($q2) => $q2->current())
            : $q->whereHas('supplierDocuments', fn ($q2) => $q2->current()->where('status', $status));

        $query = fn (?string $except = null) => Account::where('is_system_account', false)
            ->whereHas('capabilities', $supplierCapability)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(fn ($q2) => $q2->where('display_name', 'like', "%{$search}%")->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhereHas('supplierProfile', fn ($q3) => $q3->where('legal_name', 'like', "%{$search}%")->orWhere('contact_email', 'like', "%{$search}%")));
            })
            ->when($except !== 'status' && $selected['status'], fn ($q) => $hasStatus($q, $selected['status']))
            ->when($except !== 'document_status' && $selected['document_status'], function ($q) use ($selected, $hasDocuments) {
                $q->where(fn ($q2) => collect($selected['document_status'])->each(fn ($status) => $q2->orWhere(fn ($q3) => $hasDocuments($q3, $status))));
            })
            ->when($except !== 'type' && $selected['type'], fn ($q) => $q->whereIn('account_type', $selected['type']));

        $counts = [
            'status' => $this->countEach($query('status'), self::STATUSES, $hasStatus),
            'document_status' => $this->countEach($query('document_status'), self::DOCUMENT_STATUSES, $hasDocuments),
            'type' => $this->countBy($query('type'), 'account_type'),
        ];

        $accounts = $query()
            ->with(['supplierProfile.country', 'activeSubscription.plan', 'primaryOwner', 'supplierDocuments' => fn ($q) => $q->current(), 'capabilities' => $supplierCapability])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->liveFilterResponse($request, 'backend.admin.suppliers.index', 'backend.admin.suppliers.partials._table', [
            'accounts' => $accounts,
            'search' => $search,
            'filters' => [
                'status' => ['label' => 'Capability Status', 'options' => self::STATUSES, 'selected' => $selected['status']],
                'document_status' => ['label' => 'Documents', 'options' => self::DOCUMENT_STATUSES, 'selected' => $selected['document_status']],
                'type' => ['label' => 'Type', 'options' => self::TYPES, 'selected' => $selected['type']],
            ],
        ], $counts);
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('platform.accounts.suspend');
        abort_unless($account->hasCapability('supplier'), 404);

        $validated = $request->validate([
            'display_name'  => ['required', 'string', 'max:200'],
            'legal_name'    => ['nullable', 'string', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address'       => ['nullable', 'string'],
        ]);

        $account->update(['display_name' => $validated['display_name']]);

        if ($account->supplierProfile) {
            $account->supplierProfile->update($validated);
        }

        activity('moderation')->causedBy($this->admin())->performedOn($account)
            ->withProperties($validated)
            ->log('Supplier profile updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Supplier profile updated successfully.']);
        }

        return back()->with('success', 'Supplier profile updated successfully.');
    }

    public function show(Account $account)
    {
        $this->authorize('platform.accounts.view');

        abort_unless($account->hasCapability('supplier'), 404);

        $account->load(['supplierProfile.country', 'supplierTypes', 'locations', 'activeSubscription.plan']);
        $supplierCapability = $account->capabilities()->whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))->with('applicationHistory')->first();

        return view('backend.admin.suppliers.show', [
            'account' => $account,
            'capability' => $supplierCapability,
            'documents' => $account->supplierDocuments()->with('documentType')->current()->orderByDesc('created_at')->get(),
            'listingCount' => $account->listings()->count(),
            'publishedListingCount' => $account->listings()->where('approval_status', 'approved')->count(),
            'quotationCount' => $account->quotations()->count(),
            'awardCount' => $account->supplierAwards()->count(),
            'poCount' => $account->supplierPurchaseOrders()->count(),
            'reviewCount' => $account->receivedReviews()->count(),
        ]);
    }
}
