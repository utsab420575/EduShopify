<?php

namespace App\Http\Controllers\Backend\Buyer\Account;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Buyer\Account\SaveLocationRequest;
use App\Models\AccountLocation;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    use InteractsWithBuyerAccount;

    public function index()
    {
        $locations = $this->currentAccount()->locations()->with(['country', 'state', 'city'])->orderByDesc('is_primary')->get();

        return view('backend.buyer.locations.index', ['locations' => $locations]);
    }

    public function store(SaveLocationRequest $request)
    {
        $account = $this->currentAccount();

        DB::transaction(function () use ($request, $account) {
            if ($request->boolean('is_primary')) {
                $account->locations()->update(['is_primary' => false]);
            }

            $account->locations()->create($request->validated() + [
                'is_primary' => $request->boolean('is_primary') || $account->locations()->count() === 0,
                'is_active' => true,
                'created_by_user_id' => $this->currentUser()->id,
            ]);
        });

        return back()->with('success', 'Location added.');
    }

    public function update(SaveLocationRequest $request, AccountLocation $location)
    {
        $this->authorizeOwnership($location);

        DB::transaction(function () use ($request, $location) {
            if ($request->boolean('is_primary') && ! $location->is_primary) {
                $location->account->locations()->update(['is_primary' => false]);
            }

            $location->update($request->validated() + ['is_primary' => $request->boolean('is_primary')]);
        });

        return back()->with('success', 'Location updated.');
    }

    public function destroy(AccountLocation $location)
    {
        $this->authorizeOwnership($location);

        $location->delete();

        return back()->with('success', 'Location removed.');
    }

    public function makePrimary(AccountLocation $location)
    {
        $this->authorizeOwnership($location);

        DB::transaction(function () use ($location) {
            $location->account->locations()->update(['is_primary' => false]);
            $location->update(['is_primary' => true]);
        });

        return back()->with('success', 'Primary location updated.');
    }

    private function authorizeOwnership(AccountLocation $location): void
    {
        abort_unless($location->account_id === $this->currentAccount()->id, 403);
    }
}
