<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\SupplierCategory;
use App\Models\SupplierProfile;
use App\Models\SupplierType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use InteractsWithSupplierAccount;

    public function edit()
    {
        $account = $this->currentAccount();
        $profile = $account->supplierProfile ?? new SupplierProfile(['account_id' => $account->id]);
        $supplierTypes = SupplierType::where('is_active', true)->get();
        $selectedTypeIds = $account->supplierTypes()->pluck('supplier_types.id');
        $primaryTypeId = $account->supplierTypes()->wherePivot('is_primary', true)->value('supplier_types.id');

        return view('backend.supplier.company.profile', [
            'account' => $account,
            'user' => $this->currentUser(),
            'profile' => $profile,
            'supplierTypes' => $supplierTypes,
            'selectedTypeIds' => $selectedTypeIds,
            'primaryTypeId' => $primaryTypeId,
            'categoryOptions' => Category::getTreeSelectOptions(['product', 'service', 'both']),
            'selectedCategoryIds' => $account->supplierCategories()->active()->pluck('category_id'),
        ]);
    }

    public function update(Request $request)
    {
        $account = $this->currentAccount();
        $profile = $account->supplierProfile ?? new SupplierProfile(['account_id' => $account->id]);

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'company_type' => ['nullable', 'string', 'max:100'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'address' => ['required', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 1)],
            'employees' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:5000'],
            'supplier_type_ids' => ['nullable', 'array'],
            'supplier_type_ids.*' => ['exists:supplier_types,id'],
            'primary_supplier_type_id' => ['nullable', 'exists:supplier_types,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($profile->logo && Storage::disk('public')->exists($profile->logo)) {
                Storage::disk('public')->delete($profile->logo);
            }
            $validated['logo'] = $request->file('logo')->store('supplier/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($profile->banner && Storage::disk('public')->exists($profile->banner)) {
                Storage::disk('public')->delete($profile->banner);
            }
            $validated['banner'] = $request->file('banner')->store('supplier/banners', 'public');
        }

        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo && Storage::disk('public')->exists($profile->profile_photo)) {
                Storage::disk('public')->delete($profile->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('supplier/photos', 'public');
        }

        $profile->fill($validated);
        $profile->account_id = $account->id;
        if (! $profile->profile_completed_at) {
            $profile->profile_completed_at = now();
        }
        $profile->save();

        // Update account display name if updated
        $account->update(['display_name' => $validated['display_name']]);

        // Sync supplier types with primary flag
        if ($request->has('supplier_type_ids')) {
            $typeIds = $request->input('supplier_type_ids', []);
            $primaryId = $request->input('primary_supplier_type_id');
            $syncData = [];
            foreach ($typeIds as $tId) {
                $syncData[$tId] = ['is_primary' => ($tId == $primaryId)];
            }
            $account->supplierTypes()->sync($syncData);
        }

        // supplier_categories isn't a pure pivot (it also drives
        // open_matching RFQ eligibility via is_active), so it's synced
        // manually rather than via a belongsToMany sync() call.
        $categoryIds = $request->input('category_ids', []);
        SupplierCategory::where('supplier_account_id', $account->id)
            ->whereNotIn('category_id', $categoryIds)
            ->delete();
        foreach ($categoryIds as $categoryId) {
            SupplierCategory::updateOrCreate(
                ['supplier_account_id' => $account->id, 'category_id' => $categoryId],
                ['is_active' => true]
            );
        }

        return redirect()->route('supplier.company.profile')->with('success', 'Supplier profile updated successfully.');
    }
}
