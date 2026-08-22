<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\ReasonRequest;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ClosureController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $accounts = Account::where('is_system_account', false)
            ->where('status', 'deletion_pending')
            ->with('primaryOwner')
            ->latest('deletion_requested_at')
            ->paginate(15);

        return view('backend.admin.closures.index', ['accounts' => $accounts]);
    }

    public function show(Account $account)
    {
        $this->authorize('platform.accounts.view');

        abort_unless($account->status === 'deletion_pending', 404);

        return view('backend.admin.closures.show', [
            'account' => $account,
            'blockers' => $this->dependencyReport($account),
        ]);
    }

    public function finalize(Account $account)
    {
        $this->authorize('platform.accounts.suspend');

        abort_unless($account->status === 'deletion_pending', 422, 'This account is not awaiting closure.');

        $blockers = $this->dependencyReport($account);
        abort_if($blockers->isNotEmpty(), 422, 'Cannot finalize — unresolved dependencies: '.$blockers->implode(', '));

        $account->update([
            'status' => 'deleted',
            'deleted_by_user_id' => $this->admin()->id,
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($account)->log('Account closure finalized');

        return redirect()->route('admin.closures.index')->with('success', 'Account closure finalized.');
    }

    public function hold(ReasonRequest $request, Account $account)
    {
        $this->authorize('platform.accounts.suspend');

        abort_unless($account->status === 'deletion_pending', 422, 'This account is not awaiting closure.');

        $account->update([
            'status' => 'active',
            'deletion_requested_at' => null,
        ]);

        activity('moderation')->causedBy($this->admin())->performedOn($account)
            ->withProperties(['reason' => $request->string('reason')])->log('Account closure held — reverted to active');

        return redirect()->route('admin.closures.index')->with('success', 'Closure request held; account reactivated.');
    }

    private function dependencyReport(Account $account): Collection
    {
        $blockers = collect();

        if ($account->rfqs()->whereIn('status', ['open', 'award_pending'])->exists()) {
            $blockers->push('open RFQs');
        }
        if ($account->buyerAwards()->where('status', 'pending_supplier_response')->exists()) {
            $blockers->push('pending buyer awards');
        }
        if ($account->buyerPurchaseOrders()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            $blockers->push('active buyer purchase orders');
        }
        if ($account->supplierPurchaseOrders()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            $blockers->push('active supplier purchase orders');
        }
        if ($account->activeSubscription) {
            $blockers->push('an active supplier subscription');
        }
        if ($account->tickets()->whereNotIn('status', ['resolved', 'closed'])->exists()) {
            $blockers->push('open support tickets');
        }

        return $blockers;
    }
}
