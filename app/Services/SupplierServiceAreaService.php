<?php

namespace App\Services;

use App\Models\Account;
use App\Models\SupplierServiceArea;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Rule: one primary service area per supplier; additional areas are allowed.
 */
class SupplierServiceAreaService
{
    public function add(Account $supplierAccount, User $user, array $data): SupplierServiceArea
    {
        return DB::transaction(function () use ($supplierAccount, $user, $data) {
            $isPrimary = (bool) ($data['is_primary'] ?? false);

            if ($isPrimary) {
                SupplierServiceArea::where('supplier_account_id', $supplierAccount->id)
                    ->update(['is_primary' => false]);
            }

            $hasAny = SupplierServiceArea::where('supplier_account_id', $supplierAccount->id)->exists();

            return SupplierServiceArea::create([
                'supplier_account_id' => $supplierAccount->id,
                'area_level'          => $data['area_level'],
                'country_id'          => $data['country_id'] ?? null,
                'state_id'            => $data['state_id'] ?? null,
                'city_id'             => $data['city_id'] ?? null,
                'is_primary'          => $isPrimary || ! $hasAny,
                'is_active'           => true,
                'created_by_user_id'  => $user->id,
            ]);
        });
    }

    public function remove(SupplierServiceArea $area): void
    {
        $area->delete();
    }
}
