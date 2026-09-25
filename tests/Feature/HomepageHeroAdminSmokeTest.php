<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class HomepageHeroAdminSmokeTest extends TestCase
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
     * is_primary_owner must be false: AppServiceProvider's Gate::before grants
     * every ability unconditionally to the system account's primary owner,
     * which would bypass the permission check this test is verifying.
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

    public function test_admin_can_view_hero_section_edit_page(): void
    {
        $this->seedBase();
        $admin = $this->makeStaffUser('hero-view@example.com', 'super_admin');

        $response = $this->actingAs($admin)->get(route('admin.system.hero-section.edit'));

        $response->assertOk();
        $response->assertSee('Hero Section');
        $response->assertSee('Find Suppliers');
    }

    public function test_admin_can_update_hero_section_text_and_image(): void
    {
        $this->seedBase();
        $admin = $this->makeStaffUser('hero-update@example.com', 'super_admin');
        Storage::fake('public');

        $response = $this->actingAs($admin)->put(route('admin.system.hero-section.update'), [
            'heading' => "New Heading\nSecond Line",
            'subheading' => 'New subheading text',
            'primary_button_text' => 'Browse Now',
            'primary_button_url' => 'https://example.com/browse',
            'secondary_button_text' => 'Ask Us',
            'secondary_button_url' => 'https://example.com/ask',
            'image' => UploadedFile::fake()->image('hero.jpg', 1920, 700),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame("New Heading\nSecond Line", Setting::get('homepage_hero', 'heading'));
        $this->assertSame('Browse Now', Setting::get('homepage_hero', 'primary_button_text'));
        $imagePath = Setting::get('homepage_hero', 'image_path');
        Storage::disk('public')->assertExists($imagePath);

        // Round-trip: the public homepage hero partial should now render the
        // new content instead of its hardcoded defaults.
        $home = $this->get('/');
        $home->assertOk();
        $home->assertSee('New Heading', false);
        $home->assertSee('Browse Now', false);
    }

    public function test_non_admin_cannot_access_hero_section_edit_page(): void
    {
        $this->seedBase();
        $user = $this->makeStaffUser('hero-denied@example.com', 'admin_staff');

        $response = $this->actingAs($user)->get(route('admin.system.hero-section.edit'));

        $response->assertForbidden();
    }
}
