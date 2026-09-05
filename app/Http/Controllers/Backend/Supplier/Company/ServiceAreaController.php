<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\StoreServiceAreaRequest;
use App\Models\SupplierServiceArea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceAreaController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreServiceAreaRequest $request)
    {
        $account = $this->currentAccount();
        $data = $request->validated();
        $isPrimary = (bool) ($data['is_primary'] ?? false);

        DB::transaction(function () use ($account, $data, $isPrimary) {
            if ($isPrimary) {
                $account->serviceAreas()->update(['is_primary' => false]);
            }

            SupplierServiceArea::create([
                'supplier_account_id' => $account->id,
                'area_level' => $this->resolveAreaLevel($data['radius_km'] ?? null, $data['city_id'] ?? null, $data['state_id'] ?? null),
                'country_id' => $data['country_id'],
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'radius_km' => $data['radius_km'] ?? null,
                'is_primary' => $isPrimary,
                'is_active' => true,
                'created_by_user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('supplier.company.profile', ['section' => 'locations'])
            ->with('success', 'Service area added.');
    }

    public function update(StoreServiceAreaRequest $request, SupplierServiceArea $serviceArea)
    {
        abort_unless($serviceArea->supplier_account_id === $this->currentAccount()->id, 404);

        $account = $this->currentAccount();
        $data = $request->validated();
        $isPrimary = (bool) ($data['is_primary'] ?? false);

        DB::transaction(function () use ($account, $data, $isPrimary, $serviceArea) {
            if ($isPrimary) {
                $account->serviceAreas()->where('id', '!=', $serviceArea->id)->update(['is_primary' => false]);
            }

            $serviceArea->update([
                'area_level' => $this->resolveAreaLevel($data['radius_km'] ?? null, $data['city_id'] ?? null, $data['state_id'] ?? null),
                'country_id' => $data['country_id'],
                'state_id' => $data['state_id'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'radius_km' => $data['radius_km'] ?? null,
                'is_primary' => $isPrimary,
            ]);
        });

        return redirect()->route('supplier.company.profile', ['section' => 'locations'])
            ->with('success', 'Service area updated.');
    }

    public function destroy(SupplierServiceArea $serviceArea)
    {
        abort_unless($serviceArea->supplier_account_id === $this->currentAccount()->id, 404);

        $serviceArea->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'locations'])
            ->with('success', 'Service area removed.');
    }

    public function makePrimary(SupplierServiceArea $serviceArea)
    {
        abort_unless($serviceArea->supplier_account_id === $this->currentAccount()->id, 404);

        DB::transaction(function () use ($serviceArea) {
            $this->currentAccount()->serviceAreas()->update(['is_primary' => false]);
            $serviceArea->update(['is_primary' => true]);
        });

        return redirect()->route('supplier.company.profile', ['section' => 'locations'])
            ->with('success', 'Primary service area updated.');
    }

    /**
     * supplier_service_areas.area_level is a required enum with no DB
     * default — derive it from which geographic fields were actually set,
     * matching the display precedence used for the row's "Scope" text
     * (radius > city > state > nationwide).
     */
    private function resolveAreaLevel($radiusKm, $cityId, $stateId): string
    {
        if ($radiusKm) {
            return 'radius';
        }
        if ($cityId) {
            return 'city';
        }
        if ($stateId) {
            return 'state';
        }

        return 'country';
    }
}
