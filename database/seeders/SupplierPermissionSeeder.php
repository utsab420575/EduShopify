<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class SupplierPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Exact V4.3 supplier permissions
        $supplierPermissions = [
            'supplier.profile.view',
            'supplier.profile.update',
            'supplier.documents.manage',
            'supplier.service_areas.manage',

            'subscription.view',
            'subscription.select',
            'subscription.cancel',

            'listing.view',
            'listing.create',
            'listing.update',
            'listing.publish',

            'quotation.view_own',
            'quotation.create',
            'quotation.submit',

            'award.view',
            'award.accept',
            'award.reject',

            'purchase_order.view_supplier',
            'purchase_order.update_supplier',

            'review.reply',
        ];

        // Admin platform permissions
        $adminPermissions = [
            'platform.supplier_documents.verify',
            'platform.capabilities.review',
        ];

        $allPermissions = array_merge($supplierPermissions, $adminPermissions);

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web']
            );
        }

        // Global primary_owner role receives all non-platform supplier permissions
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $primaryOwnerRole = Role::where('name', 'primary_owner')
            ->whereNull('account_id')
            ->first();

        if ($primaryOwnerRole) {
            $primaryOwnerRole->givePermissionTo($supplierPermissions);
        }

        echo "SupplierPermissionSeeder: supplier permissions seeded and assigned to primary_owner.\n";
    }
}
