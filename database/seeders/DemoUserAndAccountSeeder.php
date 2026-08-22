<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountCapability;
use App\Models\AccountDashboardPreference;
use App\Models\AccountMember;
use App\Models\BuyerProfile;
use App\Models\BuyerType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DemoUserAndAccountSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = Hash::make('11111111');
        $buyerType = BuyerType::first();
        $growthPlan = SubscriptionPlan::where('slug', 'growth-pro')->first();
        $enterprisePlan = SubscriptionPlan::where('slug', 'enterprise-unlimited')->first();
        $permissionRegistrar = app(PermissionRegistrar::class);

        $buyerCapType = \App\Models\CapabilityType::where('code', 'buyer')->first();
        $supplierCapType = \App\Models\CapabilityType::where('code', 'supplier')->first();

        // ── 1. Buyer 1 (Greenwood Academy) ──
        $buyerUser = User::firstOrCreate(
            ['email' => 'buyer@school.edu'],
            [
                'name'              => 'John Buyer',
                'phone'             => '+18005550101',
                'password'          => $passwordHash,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $buyerAccount = Account::firstOrCreate(
            ['account_number' => 'ACC-BUY-001'],
            [
                'account_type'          => 'organization',
                'display_name'          => 'Greenwood Academy',
                'slug'                  => 'greenwood-academy',
                'status'                => 'active',
                'primary_owner_user_id' => $buyerUser->id,
                'approved_at'           => now(),
            ]
        );

        AccountMember::firstOrCreate(
            ['user_id' => $buyerUser->id],
            [
                'account_id'       => $buyerAccount->id,
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        $permissionRegistrar->setPermissionsTeamId($buyerAccount->id);
        $buyerUser->assignRole('primary_owner');

        AccountCapability::firstOrCreate(
            ['account_id' => $buyerAccount->id, 'capability_type_id' => $buyerCapType->id],
            ['status' => 'active', 'activated_at' => now()]
        );

        BuyerProfile::firstOrCreate(
            ['account_id' => $buyerAccount->id],
            [
                'buyer_type_id'        => $buyerType?->id,
                'display_name'         => 'Greenwood Academy Procurement',
                'organization_name'    => 'Greenwood Academy',
                'contact_person'       => 'John Buyer',
                'position'             => 'Procurement Director',
                'email'                => 'buyer@school.edu',
                'phone'                => '+18005550101',
                'website'              => 'https://greenwood.edu',
                'address'              => '100 School Lane, Boston, MA',
                'profile_completed_at' => now(),
            ]
        );

        AccountDashboardPreference::firstOrCreate(
            ['account_id' => $buyerAccount->id],
            ['default_mode' => 'buyer']
        );


        // ── 2. Buyer 2 (Metro State University) ──
        $buyer2User = User::firstOrCreate(
            ['email' => 'buyer2@university.edu'],
            [
                'name'              => 'Sarah Procurement',
                'phone'             => '+18005550102',
                'password'          => $passwordHash,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $buyer2Account = Account::firstOrCreate(
            ['account_number' => 'ACC-BUY-002'],
            [
                'account_type'          => 'organization',
                'display_name'          => 'Metro State University',
                'slug'                  => 'metro-state-university',
                'status'                => 'active',
                'primary_owner_user_id' => $buyer2User->id,
                'approved_at'           => now(),
            ]
        );

        AccountMember::firstOrCreate(
            ['user_id' => $buyer2User->id],
            [
                'account_id'       => $buyer2Account->id,
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        $permissionRegistrar->setPermissionsTeamId($buyer2Account->id);
        $buyer2User->assignRole('primary_owner');

        AccountCapability::firstOrCreate(
            ['account_id' => $buyer2Account->id, 'capability_type_id' => $buyerCapType->id],
            ['status' => 'active', 'activated_at' => now()]
        );

        BuyerProfile::firstOrCreate(
            ['account_id' => $buyer2Account->id],
            [
                'buyer_type_id'        => $buyerType?->id,
                'display_name'         => 'Metro State University Purchasing',
                'organization_name'    => 'Metro State University',
                'contact_person'       => 'Sarah Procurement',
                'position'             => 'Chief Purchasing Officer',
                'email'                => 'buyer2@university.edu',
                'phone'                => '+18005550102',
                'website'              => 'https://metro.edu',
                'address'              => '500 University Ave, Chicago, IL',
                'profile_completed_at' => now(),
            ]
        );

        AccountDashboardPreference::firstOrCreate(
            ['account_id' => $buyer2Account->id],
            ['default_mode' => 'buyer']
        );


        // ── 3. Supplier 1 (Apex EdTech Solutions) ──
        $supplierUser = User::firstOrCreate(
            ['email' => 'supplier@edtech.com'],
            [
                'name'              => 'Alex Supplier',
                'phone'             => '+18005550201',
                'password'          => $passwordHash,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $supplierAccount = Account::firstOrCreate(
            ['account_number' => 'ACC-SUP-001'],
            [
                'account_type'          => 'organization',
                'display_name'          => 'Apex EdTech Solutions',
                'slug'                  => 'apex-edtech-solutions',
                'status'                => 'active',
                'primary_owner_user_id' => $supplierUser->id,
                'approved_at'           => now(),
            ]
        );

        AccountMember::firstOrCreate(
            ['user_id' => $supplierUser->id],
            [
                'account_id'       => $supplierAccount->id,
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        $permissionRegistrar->setPermissionsTeamId($supplierAccount->id);
        $supplierUser->assignRole('primary_owner');

        AccountCapability::firstOrCreate(
            ['account_id' => $supplierAccount->id, 'capability_type_id' => $supplierCapType->id],
            ['status' => 'active', 'activated_at' => now()]
        );

        SupplierProfile::firstOrCreate(
            ['account_id' => $supplierAccount->id],
            [
                'display_name'         => 'Apex EdTech Solutions Inc.',
                'legal_name'           => 'Apex EdTech Solutions Incorporated',
                'company_type'         => 'LLC',
                'contact_person'       => 'Alex Supplier',
                'contact_email'        => 'supplier@edtech.com',
                'contact_phone'        => '+18005550201',
                'website'              => 'https://apexedtech.com',
                'founded_year'         => 2018,
                'employees'            => 45,
                'description'          => 'Leading provider of interactive classroom displays, laptops, and STEM learning kits.',
                'rating'               => 4.85,
                'reviews_count'        => 24,
                'profile_completed_at' => now(),
            ]
        );

        if ($growthPlan) {
            Subscription::firstOrCreate(
                ['supplier_account_id' => $supplierAccount->id],
                [
                    'plan_id'             => $growthPlan->id,
                    'selected_by_user_id' => $supplierUser->id,
                    'provider'            => 'manual',
                    'plan_name_snapshot'  => $growthPlan->name,
                    'price_snapshot'      => $growthPlan->price,
                    'status'              => 'active',
                    'starts_at'           => now(),
                    'expires_at'          => now()->addYear(),
                ]
            );
        }

        AccountDashboardPreference::firstOrCreate(
            ['account_id' => $supplierAccount->id],
            ['default_mode' => 'supplier']
        );


        // ── 4. Supplier 2 (Global School Furniture) ──
        $supplier2User = User::firstOrCreate(
            ['email' => 'supplier2@furniture.com'],
            [
                'name'              => 'David Craft',
                'phone'             => '+18005550202',
                'password'          => $passwordHash,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $supplier2Account = Account::firstOrCreate(
            ['account_number' => 'ACC-SUP-002'],
            [
                'account_type'          => 'organization',
                'display_name'          => 'Global School Furniture',
                'slug'                  => 'global-school-furniture',
                'status'                => 'active',
                'primary_owner_user_id' => $supplier2User->id,
                'approved_at'           => now(),
            ]
        );

        AccountMember::firstOrCreate(
            ['user_id' => $supplier2User->id],
            [
                'account_id'       => $supplier2Account->id,
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        $permissionRegistrar->setPermissionsTeamId($supplier2Account->id);
        $supplier2User->assignRole('primary_owner');

        AccountCapability::firstOrCreate(
            ['account_id' => $supplier2Account->id, 'capability_type_id' => $supplierCapType->id],
            ['status' => 'active', 'activated_at' => now()]
        );

        SupplierProfile::firstOrCreate(
            ['account_id' => $supplier2Account->id],
            [
                'display_name'         => 'Global School Furniture Corp',
                'legal_name'           => 'Global School Furniture Corporation',
                'company_type'         => 'Corporation',
                'contact_person'       => 'David Craft',
                'contact_email'        => 'supplier2@furniture.com',
                'contact_phone'        => '+18005550202',
                'website'              => 'https://globalschoolfurniture.com',
                'founded_year'         => 2012,
                'employees'            => 120,
                'description'          => 'Ergonomic classroom desks, chairs, science lab benches, and library shelving.',
                'rating'               => 4.90,
                'reviews_count'        => 58,
                'profile_completed_at' => now(),
            ]
        );

        if ($enterprisePlan) {
            Subscription::firstOrCreate(
                ['supplier_account_id' => $supplier2Account->id],
                [
                    'plan_id'             => $enterprisePlan->id,
                    'selected_by_user_id' => $supplier2User->id,
                    'provider'            => 'manual',
                    'plan_name_snapshot'  => $enterprisePlan->name,
                    'price_snapshot'      => $enterprisePlan->price,
                    'status'              => 'active',
                    'starts_at'           => now(),
                    'expires_at'          => now()->addYear(),
                ]
            );
        }

        AccountDashboardPreference::firstOrCreate(
            ['account_id' => $supplier2Account->id],
            ['default_mode' => 'supplier']
        );


        // ── 5. Dual Account (EduPartners Ltd) ──
        $dualUser = User::firstOrCreate(
            ['email' => 'dual@edupartners.com'],
            [
                'name'              => 'Morgan Dual',
                'phone'             => '+18005550300',
                'password'          => $passwordHash,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $dualAccount = Account::firstOrCreate(
            ['account_number' => 'ACC-DUAL-001'],
            [
                'account_type'          => 'organization',
                'display_name'          => 'EduPartners Ltd',
                'slug'                  => 'edupartners-ltd',
                'status'                => 'active',
                'primary_owner_user_id' => $dualUser->id,
                'approved_at'           => now(),
            ]
        );

        AccountMember::firstOrCreate(
            ['user_id' => $dualUser->id],
            [
                'account_id'       => $dualAccount->id,
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        $permissionRegistrar->setPermissionsTeamId($dualAccount->id);
        $dualUser->assignRole('primary_owner');

        AccountCapability::firstOrCreate(
            ['account_id' => $dualAccount->id, 'capability_type_id' => $buyerCapType->id],
            ['status' => 'active', 'activated_at' => now()]
        );
        AccountCapability::firstOrCreate(
            ['account_id' => $dualAccount->id, 'capability_type_id' => $supplierCapType->id],
            ['status' => 'active', 'activated_at' => now()]
        );

        BuyerProfile::firstOrCreate(
            ['account_id' => $dualAccount->id],
            [
                'buyer_type_id'        => $buyerType?->id,
                'display_name'         => 'EduPartners Procurement',
                'organization_name'    => 'EduPartners Ltd',
                'contact_person'       => 'Morgan Dual',
                'email'                => 'dual@edupartners.com',
                'profile_completed_at' => now(),
            ]
        );

        SupplierProfile::firstOrCreate(
            ['account_id' => $dualAccount->id],
            [
                'display_name'         => 'EduPartners Supply Solutions',
                'contact_person'       => 'Morgan Dual',
                'contact_email'        => 'dual@edupartners.com',
                'profile_completed_at' => now(),
            ]
        );

        AccountDashboardPreference::firstOrCreate(
            ['account_id' => $dualAccount->id],
            ['default_mode' => 'buyer']
        );
    }
}
