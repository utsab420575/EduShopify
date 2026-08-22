<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.users.view');

        $users = User::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with('accountMember.account')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.users.index', [
            'users' => $users,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function show(User $user)
    {
        $this->authorize('platform.users.view');

        $user->load(['accountMember.account.capabilities.capabilityType', 'roles', 'socialAccounts']);

        return view('backend.admin.users.show', ['targetUser' => $user]);
    }

    public function suspend(Request $request, User $user)
    {
        $this->authorize('platform.users.suspend');

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        abort_if($user->id === $this->admin()->id, 422, 'You cannot suspend your own account.');

        $user->update(['status' => 'suspended']);

        activity('moderation')->causedBy($this->admin())->performedOn($user)
            ->withProperties(['reason' => $request->string('reason')])
            ->log('User suspended');

        return back()->with('success', 'User suspended.');
    }

    public function reactivate(User $user)
    {
        $this->authorize('platform.users.suspend');

        $user->update(['status' => 'active']);

        activity('moderation')->causedBy($this->admin())->performedOn($user)->log('User reactivated');

        return back()->with('success', 'User reactivated.');
    }
}
