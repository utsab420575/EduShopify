<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\SaveLocationRequest;
use App\Models\AccountLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(SaveLocationRequest $request)
    {
        $account = $this->currentAccount();
        $isPrimary = $request->boolean('is_primary') || $account->locations()->count() === 0;

        DB::transaction(function () use ($request, $account, $isPrimary) {
            if ($isPrimary) {
                $account->locations()->update(['is_primary' => false]);
            }

            $account->locations()->create($request->validated() + [
                'is_primary' => $isPrimary,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
                'created_by_user_id' => Auth::id(),
            ]);
        });

        return redirect()->supplierRoute('company.profile', ['section' => 'locations'])
            ->with('success', 'Physical location added.');
    }

    public function update(SaveLocationRequest $request, AccountLocation $location)
    {
        $this->authorizeOwnership($location);

        $account = $this->currentAccount();
        $isPrimary = $request->boolean('is_primary');

        DB::transaction(function () use ($request, $account, $location, $isPrimary) {
            if ($isPrimary && ! $location->is_primary) {
                $account->locations()->update(['is_primary' => false]);
            }

            $location->update($request->validated() + [
                'is_primary' => $isPrimary,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $location->is_active,
            ]);
        });

        return redirect()->supplierRoute('company.profile', ['section' => 'locations'])
            ->with('success', 'Physical location updated.');
    }

    public function destroy(AccountLocation $location)
    {
        $this->authorizeOwnership($location);

        DB::transaction(function () use ($location) {
            $wasPrimary = $location->is_primary;
            $location->delete();

            if ($wasPrimary) {
                $next = $this->currentAccount()->locations()->first();
                if ($next) {
                    $next->update(['is_primary' => true]);
                }
            }
        });

        return redirect()->supplierRoute('company.profile', ['section' => 'locations'])
            ->with('success', 'Physical location removed.');
    }

    public function makePrimary(AccountLocation $location)
    {
        $this->authorizeOwnership($location);

        DB::transaction(function () use ($location) {
            $this->currentAccount()->locations()->update(['is_primary' => false]);
            $location->update(['is_primary' => true]);
        });

        return redirect()->supplierRoute('company.profile', ['section' => 'locations'])
            ->with('success', 'Primary location updated.');
    }

    private function authorizeOwnership(AccountLocation $location): void
    {
        abort_unless($location->account_id === $this->currentAccount()->id, 403);
    }
}
