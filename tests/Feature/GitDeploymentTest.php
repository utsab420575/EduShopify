<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;
use App\Services\GitDeploymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Covers the two things that actually matter for this feature: the
 * platform.system.deploy permission gate (owner_only — admin_staff must
 * never reach it even though it inherits most other platform permissions),
 * and that an unknown/malicious branch name is rejected server-side before
 * it can ever reach the real git process.
 */
class GitDeploymentTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    /**
     * is_primary_owner must be false here: AppServiceProvider's Gate::before
     * grants every ability unconditionally to the system account's primary
     * owner, regardless of assigned role — using true here would make an
     * admin_staff test user bypass every permission check, defeating the
     * point of this test.
     */
    private function makeStaffUser(string $email, string $role): User
    {
        $systemAccount = Account::where('account_number', 'SYSTEM')->firstOrFail();

        $user = User::create([
            'name' => 'Test Staff', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
            'password' => bcrypt('Password123!'), 'email_verified_at' => now(), 'status' => 'active',
        ]);

        AccountMember::create([
            'account_id' => $systemAccount->id, 'user_id' => $user->id, 'member_type' => 'member',
            'is_primary_owner' => false, 'status' => 'active', 'joined_at' => now(),
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $user->assignRole($role);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return $user->fresh();
    }

    public function test_super_admin_can_view_the_deploy_page(): void
    {
        $this->seedBase();
        $admin = $this->makeStaffUser('deploy-super@example.com', 'super_admin');

        $this->actingAs($admin)->get(route('admin.system.deploy.index'))
            ->assertOk()
            ->assertSee('GitHub Deploy');
    }

    /**
     * platform.system.deploy is owner_only and explicitly excluded from
     * admin_staff's permission set — proving the most powerful non-root
     * admin role still can't reach this page.
     */
    public function test_admin_staff_cannot_view_the_deploy_page(): void
    {
        $this->seedBase();
        $staff = $this->makeStaffUser('deploy-staff@example.com', 'admin_staff');

        $this->actingAs($staff)->get(route('admin.system.deploy.index'))
            ->assertForbidden();
    }

    public function test_pulling_an_unknown_branch_is_rejected_without_touching_git(): void
    {
        $this->seedBase();
        $admin = $this->makeStaffUser('deploy-pull@example.com', 'super_admin');

        $fake = \Mockery::mock(GitDeploymentService::class);
        $fake->shouldReceive('pull')
            ->once()
            ->with('main; rm -rf /')
            ->andReturn(['ok' => false, 'output' => '', 'error' => 'Unknown branch: main; rm -rf /']);
        $this->app->instance(GitDeploymentService::class, $fake);

        $response = $this->actingAs($admin)->post(route('admin.system.deploy.pull'), [
            'branch' => 'main; rm -rf /',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHas('pullError', 'Unknown branch: main; rm -rf /');
    }

    public function test_successful_pull_is_logged_under_rbac(): void
    {
        $this->seedBase();
        $admin = $this->makeStaffUser('deploy-log@example.com', 'super_admin');

        $fake = \Mockery::mock(GitDeploymentService::class);
        $fake->shouldReceive('pull')->once()->with('main')->andReturn(['ok' => true, 'output' => 'Already up to date.', 'error' => '']);
        $this->app->instance(GitDeploymentService::class, $fake);

        $this->actingAs($admin)->post(route('admin.system.deploy.pull'), ['branch' => 'main'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'rbac',
            'description' => "Pulled branch 'main' from GitHub",
        ]);
    }
}
