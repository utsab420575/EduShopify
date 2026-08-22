<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the global role set from spec section 4.2 (roles.account_id = NULL).
 *
 * Buyer and Supplier are deliberately NOT roles here — they are account
 * capabilities (spec rule 22). A user may hold every supplier permission and
 * still be blocked until account_capabilities says the capability is active.
 *
 * Accounts customise a global role by cloning it into an account-specific role;
 * global roles are never edited in place.
 */
class RoleSeeder extends Seeder
{
    /**
     * Legacy roles from the pre-V4.3 model. Their holders are migrated onto
     * primary_owner, then the roles are removed.
     */
    private const LEGACY_ROLES = ['supplier', 'buyer'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->definitions() as $name => $definition) {
            $role = Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web', 'account_id' => null],
                [
                    'display_name'     => $definition['display_name'],
                    'capability_scope' => $definition['scope'],
                    'description'      => $definition['description'],
                    'is_system'        => true,
                    'is_owner_role'    => $definition['owner'] ?? false,
                    'is_active'        => true,
                ]
            );

            $role->syncPermissions($definition['permissions']);
        }

        $this->retireLegacyRoles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('RoleSeeder: ' . count($this->definitions()) . ' global roles seeded.');
    }

    /**
     * Move anyone still holding 'buyer' or 'supplier' onto primary_owner in the
     * same team, then delete those roles. Buyer/Supplier access now comes from
     * account_capabilities, not from a role.
     */
    private function retireLegacyRoles(): void
    {
        $legacy = Role::whereIn('name', self::LEGACY_ROLES)->get();

        if ($legacy->isEmpty()) {
            return;
        }

        $primaryOwner = Role::where('name', 'primary_owner')->whereNull('account_id')->first();

        if (! $primaryOwner) {
            return;
        }

        $migrated = 0;

        foreach ($legacy as $role) {
            $assignments = DB::table('model_has_roles')->where('role_id', $role->id)->get();

            foreach ($assignments as $assignment) {
                $alreadyHeld = DB::table('model_has_roles')->where([
                    ['role_id', '=', $primaryOwner->id],
                    ['model_id', '=', $assignment->model_id],
                    ['model_type', '=', $assignment->model_type],
                    ['account_id', '=', $assignment->account_id],
                ])->exists();

                if (! $alreadyHeld) {
                    DB::table('model_has_roles')->insert([
                        'role_id'    => $primaryOwner->id,
                        'model_id'   => $assignment->model_id,
                        'model_type' => $assignment->model_type,
                        'account_id' => $assignment->account_id,
                    ]);
                    $migrated++;
                }
            }

            // Cascades clean up model_has_roles and role_has_permissions.
            $role->delete();
        }

        $this->command?->warn(sprintf(
            'RoleSeeder: retired legacy roles [%s]; %d assignment(s) moved to primary_owner.',
            implode(', ', self::LEGACY_ROLES),
            $migrated
        ));
    }

    /**
     * @return array<string, array{display_name:string, scope:string, description:string, permissions:list<string>, owner?:bool}>
     */
    private function definitions(): array
    {
        $platform = PermissionSeeder::namesForScope('platform');
        $common   = PermissionSeeder::namesForScope('common');
        $buyer    = PermissionSeeder::namesForScope('buyer');
        $supplier = PermissionSeeder::namesForScope('supplier');

        $ownerOnly   = ['account.close', 'ownership.transfer'];
        $commonNoOwn = array_values(array_diff($common, $ownerOnly));
        $viewOnly    = array_values(array_filter(
            array_merge($common, $buyer, $supplier),
            fn (string $p) => str_contains($p, '.view') || str_ends_with($p, '.view_own')
                || str_ends_with($p, '.view_received') || str_ends_with($p, '.view_buyer')
                || str_ends_with($p, '.view_supplier')
        ));

        return [
            /* ── Platform staff (assigned inside the reserved system account) ── */

            'super_admin' => [
                'display_name' => 'Super Admin',
                'scope'        => 'platform',
                'description'  => 'Unrestricted platform administration.',
                'permissions'  => $platform,
            ],
            'admin' => [
                'display_name' => 'Administrator',
                'scope'        => 'platform',
                'description'  => 'Full platform administration.',
                'permissions'  => $platform,
            ],
            'admin_staff' => [
                'display_name' => 'Admin Staff',
                'scope'        => 'platform',
                'description'  => 'Day-to-day platform operations, excluding billing and system settings.',
                'permissions'  => array_values(array_diff($platform, [
                    'platform.settings.manage',
                    'platform.subscriptions.manage',
                    'platform.users.suspend',
                    'platform.access_control.manage',
                ])),
            ],
            'moderator' => [
                'display_name' => 'Moderator',
                'scope'        => 'platform',
                'description'  => 'Content moderation across listings, RFQs, reviews and the catalogue.',
                'permissions'  => [
                    'platform.listings.moderate', 'platform.rfqs.moderate', 'platform.reviews.moderate',
                    'platform.categories.manage', 'platform.brands.manage', 'platform.attributes.manage',
                    'platform.accounts.view',
                ],
            ],
            'support_agent' => [
                'display_name' => 'Support Agent',
                'scope'        => 'platform',
                'description'  => 'Handles support tickets and reads account context.',
                'permissions'  => ['platform.tickets.manage', 'platform.communication.manage', 'platform.users.view', 'platform.accounts.view'],
            ],
            'finance_staff' => [
                'display_name' => 'Finance Staff',
                'scope'        => 'platform',
                'description'  => 'Manages subscription plans and billing records.',
                'permissions'  => ['platform.subscriptions.manage', 'platform.accounts.view'],
            ],

            /* ── Account roles ─────────────────────────────────────────────── */

            'primary_owner' => [
                'display_name' => 'Primary Owner',
                'scope'        => 'both',
                'description'  => 'Full account access. Buyer/Supplier actions still require an active capability.',
                'permissions'  => array_merge($common, $buyer, $supplier),
                'owner'        => true,
            ],
            'co_owner' => [
                'display_name' => 'Co-Owner',
                'scope'        => 'both',
                'description'  => 'Owner-level access except closing the account, which only the primary owner may do.',
                'permissions'  => array_merge(
                    array_values(array_diff($common, ['account.close'])),
                    $buyer,
                    $supplier
                ),
                'owner'        => true,
            ],
            'account_admin' => [
                'display_name' => 'Account Admin',
                'scope'        => 'common',
                'description'  => 'Manages members, roles and account settings. No ownership actions.',
                'permissions'  => $commonNoOwn,
            ],
            'buyer_manager' => [
                'display_name' => 'Buyer Manager',
                'scope'        => 'buyer',
                'description'  => 'Runs the full buyer workflow from RFQ through award.',
                'permissions'  => array_merge(
                    ['account.view', 'members.view', 'locations.view', 'messages.view', 'messages.send',
                        'tickets.view', 'tickets.create', 'tickets.reply'],
                    $buyer
                ),
            ],
            'procurement_manager' => [
                'display_name' => 'Procurement Manager',
                'scope'        => 'buyer',
                'description'  => 'Creates and runs RFQs but cannot award or cancel purchase orders.',
                'permissions'  => array_merge(
                    ['account.view', 'messages.view', 'messages.send', 'tickets.view', 'tickets.create'],
                    array_values(array_diff($buyer, [
                        'quotation.award', 'purchase_order.cancel', 'purchase_order.complete',
                    ]))
                ),
            ],
            'supplier_manager' => [
                'display_name' => 'Supplier Manager',
                'scope'        => 'supplier',
                'description'  => 'Runs the full supplier workflow from listing through award response.',
                'permissions'  => array_merge(
                    ['account.view', 'members.view', 'locations.view', 'messages.view', 'messages.send',
                        'tickets.view', 'tickets.create', 'tickets.reply'],
                    $supplier
                ),
            ],
            'product_manager' => [
                'display_name' => 'Product Manager',
                'scope'        => 'supplier',
                'description'  => 'Manages the catalogue: listings, media, inventory and tier pricing.',
                'permissions'  => [
                    'account.view', 'messages.view',
                    'listing.view', 'listing.create', 'listing.update', 'listing.publish',
                    'listing.deactivate', 'listing.media.manage',
                    'inventory.manage', 'tier_pricing.manage',
                ],
            ],
            'quotation_manager' => [
                'display_name' => 'Quotation Manager',
                'scope'        => 'supplier',
                'description'  => 'Answers RFQ opportunities and manages quotations and award responses.',
                'permissions'  => [
                    'account.view', 'messages.view', 'messages.send',
                    'listing.view',
                    'rfq_opportunity.view', 'rfq_question.create',
                    'quotation.view_own', 'quotation.create', 'quotation.submit',
                    'quotation.revise', 'quotation.withdraw',
                    'award.view', 'award.accept', 'award.reject',
                    'purchase_order.view_supplier',
                ],
            ],
            'finance_manager' => [
                'display_name' => 'Finance Manager',
                'scope'        => 'both',
                'description'  => 'Manages the subscription and reads purchase orders on both sides.',
                'permissions'  => [
                    'account.view',
                    'subscription.view', 'subscription.select', 'subscription.cancel',
                    'purchase_order.view_buyer', 'purchase_order.view_supplier',
                ],
            ],
            'viewer' => [
                'display_name' => 'Viewer',
                'scope'        => 'both',
                'description'  => 'Read-only access across the account.',
                'permissions'  => $viewOnly,
            ],
        ];
    }
}
