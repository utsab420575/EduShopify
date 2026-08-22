<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountCapability;
use App\Models\AccountMember;
use App\Models\BuyerProfile;
use App\Models\Role;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class AccountRegistrationService
{
    /**
     * Register a new user + account in a single atomic transaction.
     * Email verification notification is sent AFTER commit.
     */
    public function register(array $data): User
    {
        $user = null;

        DB::transaction(function () use ($data, &$user) {

            // 9.1 — Create user (pending verification)
            $user = User::create([
                'name'             => trim($data['name']),
                'email'            => strtolower(trim($data['email'])),
                'phone'            => $data['phone'] ?? null,
                'password'         => Hash::make($data['password']),
                'status'           => 'pending_verification',
                'email_verified_at' => null,
            ]);

            // 9.2 — Create account
            $displayName = $data['account_type'] === 'organization'
                ? trim($data['organization_display_name'])
                : trim($data['name']);

            $account = Account::create([
                'account_number'       => $this->generateAccountNumber(),
                'account_type'         => $data['account_type'],
                'display_name'         => $displayName,
                'slug'                 => $this->generateSlug($displayName),
                'status'               => 'draft',
                'is_system_account'    => false,
                'primary_owner_user_id' => $user->id,
            ]);

            // 9.3 — Owner membership (active immediately — owner self-registered)
            AccountMember::create([
                'account_id'      => $account->id,
                'user_id'         => $user->id,
                'member_type'     => 'owner',
                'is_primary_owner' => true,
                'status'          => 'active',
                'joined_at'       => now(),
            ]);

            $capabilities = $this->resolveCapabilities($data['capability']);

            // 9.4 — Create capability rows + profile skeletons
            foreach ($capabilities as $capCode) {
                $capType = \App\Models\CapabilityType::where('code', $capCode)->firstOrFail();
                AccountCapability::create([
                    'account_id'          => $account->id,
                    'capability_type_id'  => $capType->id,
                    'status'              => 'draft',
                    'application_attempts' => 0,
                    'applied_by_user_id'  => null,
                    'applied_at'          => null,
                    'reviewed_by_user_id' => null,
                    'reviewed_at'         => null,
                    'activated_at'        => null,
                ]);
            }

            // 10 — Buyer profile skeleton
            if (in_array('buyer', $capabilities)) {
                BuyerProfile::create([
                    'account_id'      => $account->id,
                    'display_name'    => $displayName,
                    'organization_name' => $data['account_type'] === 'organization' ? $displayName : null,
                    'contact_person'  => trim($data['name']),
                    'email'           => strtolower(trim($data['email'])),
                    'phone'           => $data['phone'] ?: null,
                    'profile_completed_at' => null,
                ]);
            }

            // 11 — Supplier profile skeleton (for "both" capability)
            if (in_array('supplier', $capabilities)) {
                SupplierProfile::create([
                    'account_id'      => $account->id,
                    'display_name'    => $displayName,
                    'contact_person'  => trim($data['name']),
                    'contact_email'   => strtolower(trim($data['email'])),
                    'contact_phone'   => $data['phone'] ?: null,
                    'profile_completed_at' => null,
                ]);
            }

            // 11a — Spec 31.1 step 6: set the Spatie team context to the new
            // account and assign the global primary_owner role. Without this the
            // owner registers with no permissions at all.
            $this->assignPrimaryOwnerRole($user, $account);
        });

        // 12 — After commit: fire Registered event (triggers verification email)
        event(new Registered($user));

        return $user;
    }

    /**
     * Assign the global primary_owner role inside the new account's team
     * context. Buyer/Supplier are capabilities and are never assigned as roles.
     */
    private function assignPrimaryOwnerRole(User $user, Account $account): void
    {
        $role = Role::where('name', 'primary_owner')
            ->whereNull('account_id')
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            Log::warning('AccountRegistrationService: primary_owner role missing; run PermissionSeeder and RoleSeeder.', [
                'account_id' => $account->id,
            ]);

            return;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);

        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->assignRole($role);
    }

    /**
     * Resolve capability array from selection.
     *
     * @return string[]
     */
    private function resolveCapabilities(string $selection): array
    {
        return match ($selection) {
            'buyer'    => ['buyer'],
            'supplier' => ['supplier'],
            'both'     => ['buyer', 'supplier'],
            default    => ['buyer'],
        };
    }

    /**
     * Generate a unique account number like ACC-2026-000001.
     */
    private function generateAccountNumber(): string
    {
        $year   = date('Y');
        $latest = Account::where('account_number', 'like', "ACC-{$year}-%")->count();
        $seq    = str_pad($latest + 1, 6, '0', STR_PAD_LEFT);

        return "ACC-{$year}-{$seq}";
    }

    /**
     * Generate a unique slug from display name.
     */
    private function generateSlug(string $displayName): string
    {
        $base = Str::slug($displayName);
        $slug = $base;
        $i    = 2;

        while (Account::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
