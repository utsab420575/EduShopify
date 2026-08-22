<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\AccountMember;
use Illuminate\Http\Request;

/**
 * Platform-wide read oversight of account membership (spec 3.3). Normal team
 * management belongs to Buyer/Supplier organization owners — Admin does not
 * routinely manage an account's employees on their behalf, so this
 * controller is intentionally read-only.
 */
class AccountMemberController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.accounts.view');

        $members = AccountMember::query()
            ->whereHas('account', fn ($q) => $q->where('is_system_account', false))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->whereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn ($q2) => $q2->where('display_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['user', 'account'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.account-members.index', [
            'members' => $members,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }
}
