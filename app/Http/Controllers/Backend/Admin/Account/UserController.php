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
            ->with(['accountMember.account', 'roles', 'socialAccounts'])
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

    public function update(Request $request, User $user)
    {
        $this->authorize('platform.users.suspend');

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone'  => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:pending_verification,active,inactive,suspended,deleted'],
        ]);

        $user->update($validated);

        activity('moderation')->causedBy($this->admin())->performedOn($user)
            ->withProperties($validated)
            ->log('User details updated');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully.']);
        }

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorize('platform.users.suspend');

        abort_if($user->id === $this->admin()->id, 422, 'You cannot delete your own account.');

        $user->update(['status' => 'deleted']);
        $user->delete();

        activity('moderation')->causedBy($this->admin())->performedOn($user)->log('User deleted');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return back()->with('success', 'User deleted successfully.');
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
