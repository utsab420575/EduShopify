<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\UpdateCompanyRequest;
use App\Models\SupplierCategory;
use App\Services\SupplierProfileService;

class CompanyController extends Controller
{
    use InteractsWithSupplierAccount;

    public function update(UpdateCompanyRequest $request, SupplierProfileService $service)
    {
        $account = $this->currentAccount();
        $data = $request->validated();

        $service->saveDraft($account, [
            'display_name' => $data['display_name'],
            'legal_name' => $data['legal_name'] ?? null,
            'legal_entity_type' => $data['company_type'] ?? null,
            'founded_year' => $data['founded_year'] ?? null,
            'employees' => $data['employees'] ?? null,
            'description' => $data['description'] ?? null,
            'supplier_type_ids' => $data['supplier_type_ids'] ?? [],
        ]);

        $account->update(['display_name' => $data['display_name']]);

        // supplier_categories isn't a pure pivot (also drives open_matching
        // RFQ eligibility via is_active), so it's synced manually rather
        // than via a belongsToMany sync() call.
        $categoryIds = $data['category_ids'] ?? [];
        SupplierCategory::where('supplier_account_id', $account->id)
            ->whereNotIn('category_id', $categoryIds)
            ->delete();
        foreach ($categoryIds as $categoryId) {
            SupplierCategory::updateOrCreate(
                ['supplier_account_id' => $account->id, 'category_id' => $categoryId],
                ['is_active' => true]
            );
        }

        return redirect()->route('supplier.company.profile', ['section' => 'company'])
            ->with('success', 'Company information saved.');
    }
}
