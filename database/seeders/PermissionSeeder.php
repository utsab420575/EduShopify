<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the global permission catalogue from spec section 4.1.
 *
 * Permissions are global abilities; account scoping happens on the role via
 * roles.account_id, never here. Business checks must use these names rather
 * than role names (spec section 30.2).
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $sort = 0;

        foreach (self::catalogue() as $definition) {
            [$name, $group, $scope, $label] = $definition;
            $flags = $definition[4] ?? [];

            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                [
                    'display_name'     => $label,
                    'group_name'       => $group,
                    'capability_scope' => $scope,
                    'is_sensitive'     => in_array('sensitive', $flags, true),
                    'is_owner_only'    => in_array('owner_only', $flags, true),
                    'is_assignable'    => ! in_array('not_assignable', $flags, true),
                    'is_active'        => true,
                    'sort_order'       => $sort += 10,
                ]
            );
        }

        $this->pruneRetiredPermissions();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('PermissionSeeder: ' . count(self::catalogue()) . ' permissions seeded.');
    }

    /**
     * Remove permissions left over from earlier, non-spec catalogues.
     *
     * Only unreferenced rows are deleted; anything still attached to a role or
     * granted directly to a user is deactivated instead, so no grant silently
     * disappears. Deactivated permissions fall out of Permission::delegatable().
     */
    private function pruneRetiredPermissions(): void
    {
        $current = array_column(self::catalogue(), 0);

        $retired = Permission::whereNotIn('name', $current)->get();

        if ($retired->isEmpty()) {
            return;
        }

        $deleted = $deactivated = 0;

        foreach ($retired as $permission) {
            $inUse = $permission->roles()->exists()
                || $permission->users()->exists();

            if ($inUse) {
                $permission->update(['is_active' => false, 'is_assignable' => false]);
                $deactivated++;

                continue;
            }

            $permission->delete();
            $deleted++;
        }

        $this->command?->warn(sprintf(
            'PermissionSeeder: retired %d unused permission(s); deactivated %d still in use.',
            $deleted,
            $deactivated
        ));
    }

    /**
     * All permission names by capability scope.
     *
     * @return list<array{0:string,1:string,2:string,3:string,4?:list<string>}>
     */
    public static function catalogue(): array
    {
        return array_merge(
            self::platform(),
            self::common(),
            self::buyer(),
            self::supplier(),
        );
    }

    /** @return list<string> */
    public static function namesForScope(string $scope): array
    {
        return array_values(array_map(
            fn (array $p) => $p[0],
            array_filter(self::catalogue(), fn (array $p) => $p[2] === $scope)
        ));
    }

    /** @return list<array{0:string,1:string,2:string,3:string,4?:list<string>}> */
    private static function platform(): array
    {
        return [
            ['platform.users.view', 'Platform · Users', 'platform', 'View users'],
            ['platform.users.suspend', 'Platform · Users', 'platform', 'Suspend users', ['sensitive']],
            ['platform.accounts.view', 'Platform · Accounts', 'platform', 'View accounts'],
            ['platform.accounts.approve', 'Platform · Accounts', 'platform', 'Approve accounts', ['sensitive']],
            ['platform.accounts.suspend', 'Platform · Accounts', 'platform', 'Suspend accounts', ['sensitive']],
            ['platform.capabilities.review', 'Platform · Capabilities', 'platform', 'Review Buyer/Supplier applications', ['sensitive']],
            ['platform.conversions.review', 'Platform · Capabilities', 'platform', 'Review account conversions', ['sensitive']],
            ['platform.supplier_documents.verify', 'Platform · Capabilities', 'platform', 'Verify supplier documents', ['sensitive']],
            ['platform.listings.moderate', 'Platform · Catalogue', 'platform', 'Moderate listings'],
            ['platform.categories.manage', 'Platform · Catalogue', 'platform', 'Manage categories'],
            ['platform.brands.manage', 'Platform · Catalogue', 'platform', 'Manage brands'],
            ['platform.attributes.manage', 'Platform · Catalogue', 'platform', 'Manage attributes and units'],
            ['platform.subscriptions.manage', 'Platform · Billing', 'platform', 'Manage subscription plans', ['sensitive']],
            ['platform.rfqs.moderate', 'Platform · Trade', 'platform', 'Moderate RFQs'],
            ['platform.reviews.moderate', 'Platform · Trade', 'platform', 'Moderate reviews'],
            ['platform.tickets.manage', 'Platform · Support', 'platform', 'Manage support tickets'],
            ['platform.communication.manage', 'Platform · Support', 'platform', 'Manage conversations and contact inquiries'],
            ['platform.access_control.manage', 'Platform · Access Control', 'platform', 'Manage platform roles, permissions and account role requests', ['sensitive']],
            ['platform.settings.manage', 'Platform · System', 'platform', 'Manage platform settings', ['sensitive']],
            ['platform.activity_logs.view', 'Platform · System', 'platform', 'View activity logs'],
        ];
    }

    /** @return list<array{0:string,1:string,2:string,3:string,4?:list<string>}> */
    private static function common(): array
    {
        return [
            ['account.view', 'Account', 'common', 'View account'],
            ['account.update', 'Account', 'common', 'Update account'],
            ['account.close', 'Account', 'common', 'Close account', ['sensitive', 'owner_only']],
            ['ownership.transfer', 'Account', 'common', 'Transfer ownership', ['sensitive', 'owner_only']],
            ['members.view', 'Members', 'common', 'View members'],
            ['members.invite', 'Members', 'common', 'Invite members'],
            ['members.update', 'Members', 'common', 'Update members'],
            ['members.remove', 'Members', 'common', 'Remove members', ['sensitive']],
            ['roles.view', 'Roles', 'common', 'View roles'],
            ['roles.create', 'Roles', 'common', 'Create roles', ['sensitive']],
            ['roles.update', 'Roles', 'common', 'Update roles', ['sensitive']],
            ['roles.delete', 'Roles', 'common', 'Delete roles', ['sensitive']],
            ['roles.assign', 'Roles', 'common', 'Assign roles', ['sensitive']],
            ['locations.view', 'Locations', 'common', 'View locations'],
            ['locations.manage', 'Locations', 'common', 'Manage locations'],
            ['messages.view', 'Messaging', 'common', 'View messages'],
            ['messages.send', 'Messaging', 'common', 'Send messages'],
            ['tickets.view', 'Support', 'common', 'View tickets'],
            ['tickets.create', 'Support', 'common', 'Create tickets'],
            ['tickets.reply', 'Support', 'common', 'Reply to tickets'],
        ];
    }

    /** @return list<array{0:string,1:string,2:string,3:string,4?:list<string>}> */
    private static function buyer(): array
    {
        return [
            ['buyer.profile.view', 'Buyer · Profile', 'buyer', 'View buyer profile'],
            ['buyer.profile.update', 'Buyer · Profile', 'buyer', 'Update buyer profile'],
            ['rfq.view', 'Buyer · RFQ', 'buyer', 'View RFQs'],
            ['rfq.create', 'Buyer · RFQ', 'buyer', 'Create RFQs'],
            ['rfq.update_draft', 'Buyer · RFQ', 'buyer', 'Edit draft RFQs'],
            ['rfq.publish', 'Buyer · RFQ', 'buyer', 'Publish RFQs'],
            ['rfq.update_open', 'Buyer · RFQ', 'buyer', 'Edit open RFQs'],
            ['rfq.cancel', 'Buyer · RFQ', 'buyer', 'Cancel RFQs'],
            ['rfq.extend_deadline', 'Buyer · RFQ', 'buyer', 'Extend RFQ deadlines'],
            ['rfq.invite_suppliers', 'Buyer · RFQ', 'buyer', 'Invite suppliers to an RFQ'],
            ['rfq.answer_questions', 'Buyer · RFQ', 'buyer', 'Answer supplier questions'],
            ['quotation.view_received', 'Buyer · Quotations', 'buyer', 'View received quotations'],
            ['quotation.compare', 'Buyer · Quotations', 'buyer', 'Compare quotations'],
            ['quotation.request_revision', 'Buyer · Quotations', 'buyer', 'Request a quotation revision'],
            ['quotation.shortlist', 'Buyer · Quotations', 'buyer', 'Shortlist quotations'],
            ['quotation.reject', 'Buyer · Quotations', 'buyer', 'Reject quotations'],
            ['quotation.award', 'Buyer · Quotations', 'buyer', 'Award a quotation', ['sensitive']],
            ['purchase_order.view_buyer', 'Buyer · Purchase Orders', 'buyer', 'View purchase orders'],
            ['purchase_order.update_buyer', 'Buyer · Purchase Orders', 'buyer', 'Update purchase orders'],
            ['purchase_order.complete', 'Buyer · Purchase Orders', 'buyer', 'Complete a purchase order'],
            ['purchase_order.cancel', 'Buyer · Purchase Orders', 'buyer', 'Cancel a purchase order', ['sensitive']],
            ['supplier.review', 'Buyer · Reviews', 'buyer', 'Review a supplier'],
        ];
    }

    /** @return list<array{0:string,1:string,2:string,3:string,4?:list<string>}> */
    private static function supplier(): array
    {
        return [
            ['supplier.profile.view', 'Supplier · Profile', 'supplier', 'View supplier profile'],
            ['supplier.profile.update', 'Supplier · Profile', 'supplier', 'Update supplier profile'],
            ['supplier.documents.manage', 'Supplier · Profile', 'supplier', 'Manage supplier documents'],
            ['supplier.service_areas.manage', 'Supplier · Profile', 'supplier', 'Manage service areas'],
            ['subscription.view', 'Supplier · Subscription', 'supplier', 'View subscription'],
            ['subscription.select', 'Supplier · Subscription', 'supplier', 'Select a plan', ['sensitive']],
            ['subscription.cancel', 'Supplier · Subscription', 'supplier', 'Cancel a subscription', ['sensitive']],
            ['listing.view', 'Supplier · Listings', 'supplier', 'View listings'],
            ['listing.create', 'Supplier · Listings', 'supplier', 'Create listings'],
            ['listing.update', 'Supplier · Listings', 'supplier', 'Update listings'],
            ['listing.publish', 'Supplier · Listings', 'supplier', 'Publish listings'],
            ['listing.deactivate', 'Supplier · Listings', 'supplier', 'Deactivate listings'],
            ['listing.delete', 'Supplier · Listings', 'supplier', 'Delete listings'],
            ['listing.media.manage', 'Supplier · Listings', 'supplier', 'Manage listing media'],
            ['inventory.manage', 'Supplier · Listings', 'supplier', 'Manage inventory'],
            ['tier_pricing.manage', 'Supplier · Listings', 'supplier', 'Manage tier pricing'],
            ['rfq_opportunity.view', 'Supplier · RFQ', 'supplier', 'View RFQ opportunities'],
            ['rfq_question.create', 'Supplier · RFQ', 'supplier', 'Ask a question on an RFQ'],
            ['quotation.view_own', 'Supplier · Quotations', 'supplier', 'View own quotations'],
            ['quotation.create', 'Supplier · Quotations', 'supplier', 'Create quotations'],
            ['quotation.submit', 'Supplier · Quotations', 'supplier', 'Submit quotations'],
            ['quotation.revise', 'Supplier · Quotations', 'supplier', 'Revise quotations'],
            ['quotation.withdraw', 'Supplier · Quotations', 'supplier', 'Withdraw quotations'],
            ['award.view', 'Supplier · Awards', 'supplier', 'View awards'],
            ['award.accept', 'Supplier · Awards', 'supplier', 'Accept an award', ['sensitive']],
            ['award.reject', 'Supplier · Awards', 'supplier', 'Reject an award', ['sensitive']],
            ['purchase_order.view_supplier', 'Supplier · Purchase Orders', 'supplier', 'View purchase orders'],
            ['purchase_order.update_supplier', 'Supplier · Purchase Orders', 'supplier', 'Update purchase orders'],
            ['review.reply', 'Supplier · Reviews', 'supplier', 'Reply to a review'],
        ];
    }
}
