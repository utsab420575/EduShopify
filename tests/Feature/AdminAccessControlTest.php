<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The permission-assignment UI (roles/_form.blade.php, roles-in-permission
 * create/edit) was rebuilt around a shared <x-backend.permission-matrix>
 * component to replace three near-duplicated hand-rolled checkbox grids.
 * These tests prove the new markup still emits the same permissions[]
 * payload shape RoleController/RoleInPermissionController already expect —
 * the component changed presentation only, not the request contract.
 */
class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeAdmin(string $email): User
    {
        $systemAccount = Account::where('account_number', 'SYSTEM')->firstOrFail();

        $admin = User::create([
            'name' => 'Test Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
            'password' => bcrypt('Password123!'), 'email_verified_at' => now(), 'status' => 'active',
        ]);

        AccountMember::create([
            'account_id' => $systemAccount->id, 'user_id' => $admin->id, 'member_type' => 'owner',
            'is_primary_owner' => true, 'status' => 'active', 'joined_at' => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $admin->assignRole('admin');
        $admin->unsetRelation('roles')->unsetRelation('permissions');

        return $admin->fresh();
    }

    public function test_admin_can_view_roles_create_and_edit_pages(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('ac-admin1@example.com');

        $this->actingAs($admin)->get(route('admin.access-control.roles.create'))->assertOk()
            ->assertSee('Assign Default Permissions');

        $role = Role::where('name', 'moderator')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.access-control.roles.edit', $role))->assertOk()
            ->assertSee('Assign Default Permissions');
    }

    public function test_creating_a_role_via_the_new_permission_matrix_syncs_exactly_those_permissions(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('ac-admin2@example.com');

        $perms = Permission::where('capability_scope', 'supplier')->limit(3)->pluck('name')->all();
        $this->assertCount(3, $perms);

        $response = $this->actingAs($admin)->post(route('admin.access-control.roles.store'), [
            'name' => 'matrix_test_role',
            'display_name' => 'Matrix Test Role',
            'capability_scope' => 'supplier',
            'description' => 'Created via the new permission matrix component.',
            'permissions' => $perms,
        ]);

        $response->assertRedirect();
        $role = Role::where('name', 'matrix_test_role')->firstOrFail();
        $this->assertEqualsCanonicalizing($perms, $role->permissions->pluck('name')->all());
    }

    public function test_roles_in_permission_create_page_renders_and_syncs_permissions(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('ac-admin3@example.com');
        $role = Role::where('name', 'moderator')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.access-control.roles-in-permission.create'))
            ->assertOk()->assertSee('Permissions Matrix');

        $perms = Permission::where('capability_scope', 'buyer')->limit(2)->pluck('name')->all();

        $this->actingAs($admin)->post(route('admin.access-control.roles-in-permission.store'), [
            'role_id' => $role->id,
            'permissions' => $perms,
        ])->assertRedirect(route('admin.access-control.roles-in-permission.index'));

        $this->assertEqualsCanonicalizing($perms, $role->fresh()->permissions->pluck('name')->all());
    }

    public function test_roles_in_permission_index_shows_a_count_pill_not_every_permission_name(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('ac-admin4@example.com');
        $role = Role::where('name', 'moderator')->firstOrFail();
        $permCount = $role->permissions()->count();

        $response = $this->actingAs($admin)->get(route('admin.access-control.roles-in-permission.index'));

        $response->assertOk();
        $response->assertSee($permCount . ' permissions');
    }

    /**
     * Regression: RoleRequestController::approve()/reject() logged to
     * log_name 'access_control', but RbacAuditLogController only ever
     * queries log_name 'rbac' — approvals/rejections never appeared in the
     * Audit Logs page. Both must now share the same log name.
     */
    public function test_role_request_approval_is_logged_under_rbac_and_appears_in_audit_logs(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('ac-admin5@example.com');

        $owner = app(\App\Services\AccountRegistrationService::class)->register([
            'account_type' => 'organization', 'capability' => 'supplier',
            'name' => 'Role Request Owner', 'email' => 'ac-owner5@example.com',
            'phone' => '+15550005555', 'password' => 'Password123!',
            'organization_display_name' => 'Role Request Owner Co',
        ]);
        $owner->markEmailAsVerified();
        $owner->update(['status' => 'active']);
        $account = $owner->account;
        $account->update(['status' => 'active']);

        $requestedPerms = Permission::where('capability_scope', 'supplier')->limit(2)->pluck('name')->all();
        $roleRequest = RoleRequest::create([
            'account_id' => $account->id, 'requested_by_user_id' => $owner->id,
            'role_name' => 'audit_test_role', 'display_name' => 'Audit Test Role',
            'capability_scope' => 'supplier',
            'requested_permissions' => $requestedPerms,
            'description' => 'Test role request.', 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.access-control.role-requests.approve', $roleRequest))
            ->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'rbac',
            'subject_type' => RoleRequest::class,
            'subject_id' => $roleRequest->id,
        ]);

        $auditResponse = $this->actingAs($admin)->get(route('admin.access-control.audit-logs.index'));
        $auditResponse->assertOk();
        $auditResponse->assertSee('Role request approved');
    }
}
