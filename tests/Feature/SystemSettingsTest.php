<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\Media\ProductMediaLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Admin › System Settings: list / add / edit / delete rows of the settings
 * table. Settings the code reads keep their group, name and type; the
 * product video limits feed ProductMediaLimits.
 */
class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);

        $systemAccount = \App\Models\Account::where('account_number', 'SYSTEM')->firstOrFail();
        $admin = User::create([
            'name' => 'Settings Admin', 'email' => 'settings-admin@example.com', 'phone' => '+10000000009',
            'password' => bcrypt('Password123!'), 'email_verified_at' => now(), 'status' => 'active',
        ]);
        \App\Models\AccountMember::create([
            'account_id' => $systemAccount->id, 'user_id' => $admin->id, 'member_type' => 'owner',
            'is_primary_owner' => true, 'status' => 'active', 'joined_at' => now(),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($systemAccount->id);
        $admin->assignRole('admin');
        $this->admin = $admin->fresh();
    }

    private function videoSetting(string $name): Setting
    {
        return Setting::where('group_name', 'product_media')->where('name', $name)->firstOrFail();
    }

    public function test_list_shows_the_seeded_video_settings(): void
    {
        $this->actingAs($this->admin)->get(route('admin.system.settings.index'))
            ->assertOk()
            ->assertSee('Product video max size (KB)')
            ->assertSee('video_extensions');

        $this->assertSame(2048, ProductMediaLimits::videoMaxKb());
        $this->assertSame(['mp4', 'webm'], ProductMediaLimits::videoExtensions());
        $this->assertSame([15, 5], [ProductMediaLimits::maxImages(), ProductMediaLimits::maxVideos()]);
    }

    public function test_photo_and_video_counts_are_editable_with_bounds(): void
    {
        $images = $this->videoSetting('max_images');
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $images), ['value' => '8'])->assertSessionHasNoErrors();
        $this->assertSame(8, ProductMediaLimits::maxImages());
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $images), ['value' => '0'])->assertSessionHasErrors('value');

        $videos = $this->videoSetting('max_videos');
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $videos), ['value' => '0'])->assertSessionHasNoErrors();
        $this->assertSame(0, ProductMediaLimits::maxVideos());
    }

    public function test_editing_video_settings_changes_the_limits_and_keeps_the_key(): void
    {
        $size = $this->videoSetting('video_max_kb');
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $size), [
            'group_name' => 'hacked', 'name' => 'renamed', 'type' => 'text', 'value' => '10240', 'label' => 'Video size',
        ])->assertRedirect()->assertSessionHas('success');

        $size->refresh();
        $this->assertSame(['product_media', 'video_max_kb', 'number'], [$size->group_name, $size->name, $size->type]);
        $this->assertSame(10240, ProductMediaLimits::videoMaxKb());
        $this->assertSame('10 MB', ProductMediaLimits::videoMaxLabel());

        $ext = $this->videoSetting('video_extensions');
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $ext), ['value' => 'MP4, .mov'])->assertSessionHasNoErrors();
        $this->assertSame(['mp4', 'mov'], ProductMediaLimits::videoExtensions());
        $this->assertContains('video/quicktime', ProductMediaLimits::videoMimeTypes());

        // Not a video type / empty / not a number → rejected.
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $ext), ['value' => 'exe'])->assertSessionHasErrors('value');
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $ext), ['value' => ''])->assertSessionHasErrors('value');
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $size), ['value' => 'abc'])->assertSessionHasErrors('value');
    }

    public function test_add_and_delete_a_custom_setting_and_locked_ones_are_protected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.system.settings.store'), [
            'group_name' => 'catalog', 'name' => 'max_tags', 'label' => 'Max tags', 'type' => 'number', 'value' => '12',
        ])->assertSessionHasNoErrors();
        $this->assertSame(12, Setting::get('catalog', 'max_tags'));

        // Same key twice, bad key format, managed group → rejected.
        $this->actingAs($this->admin)->post(route('admin.system.settings.store'), ['group_name' => 'catalog', 'name' => 'max_tags', 'type' => 'number', 'value' => '1'])
            ->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->post(route('admin.system.settings.store'), ['group_name' => 'Catalog Group', 'name' => 'x', 'type' => 'text'])
            ->assertSessionHasErrors('group_name');
        $this->actingAs($this->admin)->post(route('admin.system.settings.store'), ['group_name' => 'theme', 'name' => 'x', 'type' => 'text'])
            ->assertSessionHasErrors('group_name');

        $custom = Setting::where('name', 'max_tags')->firstOrFail();
        $this->actingAs($this->admin)->delete(route('admin.system.settings.destroy', $custom))->assertSessionHas('success');
        $this->assertNull(Setting::get('catalog', 'max_tags'));

        $locked = Setting::create(['group_name' => 'billing', 'name' => 'gateway', 'type' => 'text', 'locked' => true, 'payload' => ['value' => 'x']]);
        $this->actingAs($this->admin)->put(route('admin.system.settings.update', $locked), ['type' => 'text', 'value' => 'y'])->assertSessionHas('error');
        $this->actingAs($this->admin)->delete(route('admin.system.settings.destroy', $locked))->assertSessionHas('error');
        $this->assertSame('x', $locked->fresh()->value());
    }
}
