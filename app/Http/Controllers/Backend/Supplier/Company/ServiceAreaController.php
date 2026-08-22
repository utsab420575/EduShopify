<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\SupplierServiceArea;
use Illuminate\Http\Request;

class ServiceAreaController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index()
    {
        $account = $this->currentAccount();
        $serviceAreas = $account->serviceAreas()->with(['country', 'state', 'city'])->latest()->get();
        $countries = Country::where('is_active', true)->get();

        return view('backend.supplier.company.service-areas', [
            'account' => $account,
            'user' => $this->currentUser(),
            'serviceAreas' => $serviceAreas,
            'countries' => $countries,
        ]);
    }

    public function store(Request $request)
    {
        $account = $this->currentAccount();

        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'radius_km' => ['nullable', 'numeric', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_primary')) {
            $account->serviceAreas()->update(['is_primary' => false]);
        }

        SupplierServiceArea::create([
            'supplier_account_id' => $account->id,
            'country_id' => $validated['country_id'],
            'state_id' => $validated['state_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'radius_km' => $validated['radius_km'] ?? null,
            'is_primary' => $request->boolean('is_primary'),
            'is_active' => $request->boolean('is_active', true),
            'created_by_user_id' => $this->currentUser()->id,
        ]);

        return redirect()->route('supplier.company.service-areas')->with('success', 'Service area added successfully.');
    }

    public function update(Request $request, SupplierServiceArea $serviceArea)
    {
        $account = $this->currentAccount();
        abort_if($serviceArea->supplier_account_id !== $account->id, 403);

        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'radius_km' => ['nullable', 'numeric', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_primary')) {
            $account->serviceAreas()->where('id', '!=', $serviceArea->id)->update(['is_primary' => false]);
        }

        $serviceArea->update([
            'country_id' => $validated['country_id'],
            'state_id' => $validated['state_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
            'radius_km' => $validated['radius_km'] ?? null,
            'is_primary' => $request->boolean('is_primary'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('supplier.company.service-areas')->with('success', 'Service area updated successfully.');
    }

    public function destroy(SupplierServiceArea $serviceArea)
    {
        $account = $this->currentAccount();
        abort_if($serviceArea->supplier_account_id !== $account->id, 403);

        $serviceArea->delete();

        return redirect()->route('supplier.company.service-areas')->with('success', 'Service area removed.');
    }
}
