<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database in strict dependency order.
     */
    public function run(): void
    {
        // 1. Foundation Lookups & Geography
        $this->call([
            LanguageSeeder::class,
            CurrencySeeder::class,
            UnitSeeder::class,
            GeographySeeder::class,
        ]);

        // 2. Taxonomy & Types
        $this->call([
            BuyerTypeSeeder::class,
            SupplierTypeSeeder::class,
            ExhibitionSeeder::class,
            DocumentTypeSeeder::class,
            CapabilityTypeSeeder::class,
            DocumentTypeEnableSeeder::class,
            InputTypeSeeder::class,
            PricingTypeSeeder::class,
            SalesModeSeeder::class,
            ListingTypeSeeder::class,
            VisibilityTypeSeeder::class,
        ]);

        // 2a. Capability Settings
        $this->call([
            CapabilitySettingSeeder::class,
        ]);

        // 3. Authorization — the permission catalogue first, then the roles
        //    that consume it. Buyer/Supplier are capabilities, not roles.
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SupplierPermissionSeeder::class,
        ]);

        // 4. Core System & Subscriptions & Categories
        $this->call([
            SystemAccountSeeder::class,
            SubscriptionPlanSeeder::class,
            CategorySeeder::class,
        ]);

        // 6. Users, Accounts, Capabilities, and Profiles (Pass: 11111111)
        $this->call([
            AdminUserSeeder::class,
            DemoUserAndAccountSeeder::class,
        ]);

        // 7. Demo Listings, RFQs, Quotations, Awards, & Purchase Orders
        $this->call([
            DemoListingAndRfqSeeder::class,
        ]);

        // 8. Spatie caches permissions aggressively; clear it after seeding.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
