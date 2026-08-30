<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountCapability;
use App\Models\AccountDashboardPreference;
use App\Models\AccountMember;
use App\Models\BuyerProfile;
use App\Models\BuyerType;
use App\Models\CapabilityType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SupplierProfile;
use App\Models\SupplierType;
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
        $supplierType = SupplierType::first();
        $growthPlan = SubscriptionPlan::where('slug', 'growth-pro')->first() ?? SubscriptionPlan::first();
        $enterprisePlan = SubscriptionPlan::where('slug', 'enterprise-unlimited')->first() ?? SubscriptionPlan::first();
        $starterPlan = SubscriptionPlan::where('slug', 'starter-launch')->first() ?? SubscriptionPlan::first();
        $permissionRegistrar = app(PermissionRegistrar::class);

        $buyerCapType = CapabilityType::firstOrCreate(['code' => 'buyer'], ['name' => 'Buyer', 'is_active' => true]);
        $supplierCapType = CapabilityType::firstOrCreate(['code' => 'supplier'], ['name' => 'Supplier', 'is_active' => true]);

        // ══════════════════════════════════════════════════════════════════════
        // 1. BUYER ACCOUNTS
        // ══════════════════════════════════════════════════════════════════════

        // Buyer 1: Greenwood Academy
        $this->createBuyerAccount(
            email: 'buyer@school.edu',
            name: 'John Buyer',
            phone: '+18005550101',
            accountNumber: 'ACC-BUY-001',
            orgName: 'Greenwood Academy',
            slug: 'greenwood-academy',
            contactPerson: 'John Buyer',
            position: 'Procurement Director',
            address: '100 School Lane, Boston, MA',
            buyerTypeId: $buyerType?->id,
            passwordHash: $passwordHash,
            buyerCapTypeId: $buyerCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // Buyer 2: Metro State University
        $this->createBuyerAccount(
            email: 'buyer2@university.edu',
            name: 'Dr. Sarah Jenkins',
            phone: '+18005550102',
            accountNumber: 'ACC-BUY-002',
            orgName: 'Metro State University',
            slug: 'metro-state-university',
            contactPerson: 'Dr. Sarah Jenkins',
            position: 'Dean of Sciences & Lab Facilities',
            address: '500 University Ave, Chicago, IL',
            buyerTypeId: $buyerType?->id,
            passwordHash: $passwordHash,
            buyerCapTypeId: $buyerCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // Buyer 3: Oakridge STEM District
        $this->createBuyerAccount(
            email: 'buyer3@oakridge.edu',
            name: 'Robert Vance',
            phone: '+18005550103',
            accountNumber: 'ACC-BUY-003',
            orgName: 'Oakridge STEM District',
            slug: 'oakridge-stem-district',
            contactPerson: 'Robert Vance',
            position: 'District Operations Manager',
            address: '750 Innovation Blvd, Austin, TX',
            buyerTypeId: $buyerType?->id,
            passwordHash: $passwordHash,
            buyerCapTypeId: $buyerCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // ══════════════════════════════════════════════════════════════════════
        // 2. SUPPLIER ACCOUNTS
        // ══════════════════════════════════════════════════════════════════════

        // Supplier 1: Apex EdTech Solutions Inc.
        $this->createSupplierAccount(
            email: 'supplier@edtech.com',
            name: 'Alex Supplier',
            phone: '+18005550201',
            accountNumber: 'ACC-SUP-001',
            orgName: 'Apex EdTech Solutions Inc.',
            slug: 'apex-edtech-solutions',
            contactPerson: 'Alex Supplier',
            description: 'Leading provider of interactive classroom displays, laptops, and STEM learning kits.',
            plan: $growthPlan,
            passwordHash: $passwordHash,
            supplierCapTypeId: $supplierCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // Supplier 2: Global School Furniture Corp
        $this->createSupplierAccount(
            email: 'supplier2@furniture.com',
            name: 'David Craft',
            phone: '+18005550202',
            accountNumber: 'ACC-SUP-002',
            orgName: 'Global School Furniture Corp',
            slug: 'global-school-furniture',
            contactPerson: 'David Craft',
            description: 'Ergonomic classroom desks, chairs, science lab benches, and library shelving.',
            plan: $enterprisePlan,
            passwordHash: $passwordHash,
            supplierCapTypeId: $supplierCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // Supplier 3: BioScience & Lab Supplies LLC
        $this->createSupplierAccount(
            email: 'supplier3@bioscience.com',
            name: 'Elena Rostova',
            phone: '+18005550203',
            accountNumber: 'ACC-SUP-003',
            orgName: 'BioScience & Lab Supplies LLC',
            slug: 'bioscience-lab-supplies',
            contactPerson: 'Elena Rostova',
            description: 'High-precision microscopes, lab glassware, chemical apparatus, and biology anatomy models.',
            plan: $growthPlan,
            passwordHash: $passwordHash,
            supplierCapTypeId: $supplierCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // Supplier 4: Horizon Educational Publishing
        $this->createSupplierAccount(
            email: 'supplier4@horizonbooks.com',
            name: 'Marcus Sterling',
            phone: '+18005550204',
            accountNumber: 'ACC-SUP-004',
            orgName: 'Horizon Educational Publishing',
            slug: 'horizon-educational-publishing',
            contactPerson: 'Marcus Sterling',
            description: 'Comprehensive K-12 STEM coursebooks, guided reading libraries, and teacher pedagogical guides.',
            plan: $starterPlan,
            passwordHash: $passwordHash,
            supplierCapTypeId: $supplierCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // Supplier 5: Champion School Sports & Athletics
        $this->createSupplierAccount(
            email: 'supplier5@championsports.com',
            name: 'Coach Thomas Miller',
            phone: '+18005550205',
            accountNumber: 'ACC-SUP-005',
            orgName: 'Champion School Sports & Athletics',
            slug: 'champion-school-sports',
            contactPerson: 'Thomas Miller',
            description: 'Commercial gymnasium mats, institutional basketball/volleyball gear, and agility equipment.',
            plan: $starterPlan,
            passwordHash: $passwordHash,
            supplierCapTypeId: $supplierCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // Supplier 6: Artisan School Crafts & Uniforms
        $this->createSupplierAccount(
            email: 'supplier6@artisanapparel.com',
            name: 'Clara Bennett',
            phone: '+18005550206',
            accountNumber: 'ACC-SUP-006',
            orgName: 'Artisan School Crafts & Uniforms',
            slug: 'artisan-school-crafts-uniforms',
            contactPerson: 'Clara Bennett',
            description: 'Durable cotton-blend school uniform polos, safety lab coats, and classroom art classpacks.',
            plan: $growthPlan,
            passwordHash: $passwordHash,
            supplierCapTypeId: $supplierCapType->id,
            permissionRegistrar: $permissionRegistrar
        );

        // ══════════════════════════════════════════════════════════════════════
        // 3. DUAL CAPABILITY ACCOUNT
        // ══════════════════════════════════════════════════════════════════════
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
            ['user_id' => $dualUser->id, 'account_id' => $dualAccount->id],
            [
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

    private function createBuyerAccount(
        string $email,
        string $name,
        string $phone,
        string $accountNumber,
        string $orgName,
        string $slug,
        string $contactPerson,
        string $position,
        string $address,
        ?int $buyerTypeId,
        string $passwordHash,
        int $buyerCapTypeId,
        PermissionRegistrar $permissionRegistrar
    ): void {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'phone'             => $phone,
                'password'          => $passwordHash,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $account = Account::firstOrCreate(
            ['account_number' => $accountNumber],
            [
                'account_type'          => 'organization',
                'display_name'          => $orgName,
                'slug'                  => $slug,
                'status'                => 'active',
                'primary_owner_user_id' => $user->id,
                'approved_at'           => now(),
            ]
        );

        AccountMember::firstOrCreate(
            ['user_id' => $user->id, 'account_id' => $account->id],
            [
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        $permissionRegistrar->setPermissionsTeamId($account->id);
        $user->assignRole('primary_owner');

        AccountCapability::firstOrCreate(
            ['account_id' => $account->id, 'capability_type_id' => $buyerCapTypeId],
            ['status' => 'active', 'activated_at' => now()]
        );

        BuyerProfile::firstOrCreate(
            ['account_id' => $account->id],
            [
                'buyer_type_id'        => $buyerTypeId,
                'display_name'         => "{$orgName} Procurement",
                'organization_name'    => $orgName,
                'contact_person'       => $contactPerson,
                'position'             => $position,
                'email'                => $email,
                'phone'                => $phone,
                'address'              => $address,
                'profile_completed_at' => now(),
            ]
        );

        AccountDashboardPreference::firstOrCreate(
            ['account_id' => $account->id],
            ['default_mode' => 'buyer']
        );
    }

    private function createSupplierAccount(
        string $email,
        string $name,
        string $phone,
        string $accountNumber,
        string $orgName,
        string $slug,
        string $contactPerson,
        string $description,
        ?SubscriptionPlan $plan,
        string $passwordHash,
        int $supplierCapTypeId,
        PermissionRegistrar $permissionRegistrar
    ): void {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'phone'             => $phone,
                'password'          => $passwordHash,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status'            => 'active',
            ]
        );

        $account = Account::firstOrCreate(
            ['account_number' => $accountNumber],
            [
                'account_type'          => 'organization',
                'display_name'          => $orgName,
                'slug'                  => $slug,
                'status'                => 'active',
                'primary_owner_user_id' => $user->id,
                'approved_at'           => now(),
            ]
        );

        AccountMember::firstOrCreate(
            ['user_id' => $user->id, 'account_id' => $account->id],
            [
                'member_type'      => 'owner',
                'is_primary_owner' => true,
                'status'           => 'active',
                'joined_at'        => now(),
            ]
        );

        $permissionRegistrar->setPermissionsTeamId($account->id);
        $user->assignRole('primary_owner');

        AccountCapability::firstOrCreate(
            ['account_id' => $account->id, 'capability_type_id' => $supplierCapTypeId],
            ['status' => 'active', 'activated_at' => now()]
        );

        SupplierProfile::firstOrCreate(
            ['account_id' => $account->id],
            [
                'display_name'         => $orgName,
                'legal_name'           => $orgName,
                'company_type'         => 'LLC',
                'contact_person'       => $contactPerson,
                'contact_email'        => $email,
                'contact_phone'        => $phone,
                'description'          => $description,
                'rating'               => 4.85,
                'reviews_count'        => 32,
                'profile_completed_at' => now(),
            ]
        );

        if ($plan) {
            Subscription::firstOrCreate(
                ['supplier_account_id' => $account->id],
                [
                    'plan_id'             => $plan->id,
                    'selected_by_user_id' => $user->id,
                    'provider'            => 'manual',
                    'plan_name_snapshot'  => $plan->name,
                    'price_snapshot'      => $plan->price,
                    'status'              => 'active',
                    'starts_at'           => now(),
                    'expires_at'          => now()->addYear(),
                ]
            );
        }

        AccountDashboardPreference::firstOrCreate(
            ['account_id' => $account->id],
            ['default_mode' => 'supplier']
        );
    }
}
