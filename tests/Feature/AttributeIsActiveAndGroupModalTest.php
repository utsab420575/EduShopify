<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\User;
use Database\Seeders\InputTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Two fixes for the Category Builder's Attributes/Attribute Groups tabs:
 *
 * 1. Unchecking "Active in Catalog" on an attribute (or attribute group)
 *    and saving never persisted is_active=0. Root cause:
 *    $request->boolean('is_active', true) — Laravel's boolean() calls
 *    input($key, $default) first, so when the checkbox is unchecked (and
 *    therefore absent from the POST body, standard HTML checkbox
 *    behavior), it falls back to the literal default `true` and returns
 *    true regardless of what was actually submitted. Every other checkbox
 *    on the same form (is_filterable, is_variant, is_required) already used
 *    $request->boolean('is_filterable') with no override and worked
 *    correctly — the fix is to drop the `true` default here too.
 *
 * 2. The Attribute Groups builder's "N attributes" count had no way to see
 *    which attributes were in the group or toggle them active/inactive
 *    without leaving the tab. Added a modal (triggered by clicking the
 *    count) listing the group's attributes with a quick toggle and an edit
 *    link.
 */
class AttributeIsActiveAndGroupModalTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
        $this->seed(InputTypeSeeder::class);
    }

    private function makeAdmin(string $email): User
    {
        $systemAccount = Account::where('account_number', 'SYSTEM')->firstOrFail();

        $admin = User::create([
            'name' => 'Attribute Active Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
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

    /* ── is_active persistence ────────────────────────────────────────── */

    public function test_unchecking_active_on_an_attribute_update_actually_persists_inactive(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-uncheck-active@example.com');
        $attribute = Attribute::create(['name' => 'Voltage', 'slug' => 'voltage-uncheck', 'input_type' => 'number', 'is_active' => true]);

        // Omitting is_active from the payload is exactly what a real browser
        // sends when the checkbox is unchecked — it never appears in the POST body.
        $response = $this->actingAs($admin)->put(route('admin.catalog.attributes.update', $attribute), [
            'name' => 'Voltage',
            'input_type_id' => \App\Models\InputType::where('code', 'number')->value('id'),
        ]);

        $response->assertRedirect();
        $this->assertFalse($attribute->fresh()->is_active, 'Unchecking the checkbox must persist is_active = 0, not silently stay true.');
    }

    public function test_creating_an_attribute_without_checking_active_creates_it_inactive(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-create-inactive@example.com');

        $response = $this->actingAs($admin)->post(route('admin.catalog.attributes.store'), [
            'name' => 'Weight',
            'input_type_id' => \App\Models\InputType::where('code', 'number')->value('id'),
        ]);

        $response->assertRedirect();
        $this->assertFalse(Attribute::where('name', 'Weight')->value('is_active'));
    }

    public function test_unchecking_active_on_an_attribute_group_update_actually_persists_inactive(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('group-uncheck-active@example.com');
        $group = AttributeGroup::create(['name' => 'Technical Specification', 'slug' => 'technical-specification-uncheck', 'is_active' => true]);

        $response = $this->actingAs($admin)->put(route('admin.catalog.attribute-groups.update', $group), [
            'name' => 'Technical Specification',
        ]);

        $response->assertRedirect();
        $this->assertFalse($group->fresh()->is_active);
    }

    /* ── Attribute toggle-active endpoint ─────────────────────────────── */

    public function test_toggle_active_flips_an_attributes_status(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-toggle@example.com');
        $attribute = Attribute::create(['name' => 'Voltage', 'slug' => 'voltage-toggle', 'input_type' => 'number', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.catalog.attributes.toggle-active', $attribute))->assertRedirect();
        $this->assertFalse($attribute->fresh()->is_active);

        $this->actingAs($admin)->post(route('admin.catalog.attributes.toggle-active', $attribute))->assertRedirect();
        $this->assertTrue($attribute->fresh()->is_active);
    }

    public function test_toggle_active_with_reopen_group_id_flashes_the_group_modal_open_flag(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-toggle-reopen@example.com');
        $group = AttributeGroup::create(['name' => 'Technical Specification', 'slug' => 'technical-specification-reopen', 'is_active' => true]);
        $attribute = Attribute::create(['name' => 'Voltage', 'slug' => 'voltage-reopen', 'input_type' => 'number', 'attribute_group_id' => $group->id, 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.catalog.attributes.toggle-active', $attribute), [
            'redirect_to' => route('admin.catalog.builder.attribute-groups'),
            'reopen_group_id' => $group->id,
        ]);

        $response->assertRedirect(route('admin.catalog.builder.attribute-groups'));
        $response->assertSessionHas('open_group_attributes_id', $group->id);
    }

    /* ── Group attributes modal ───────────────────────────────────────── */

    public function test_the_attribute_groups_page_lists_each_groups_attributes_with_toggle_and_edit_actions(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('group-modal-list@example.com');
        $group = AttributeGroup::create(['name' => 'Technical Specification', 'slug' => 'technical-specification-modal', 'is_active' => true]);
        $active = Attribute::create(['name' => 'Voltage', 'slug' => 'voltage-modal', 'input_type' => 'number', 'attribute_group_id' => $group->id, 'is_active' => true]);
        $inactive = Attribute::create(['name' => 'Amperage', 'slug' => 'amperage-modal', 'input_type' => 'number', 'attribute_group_id' => $group->id, 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.attribute-groups'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('Attributes in "Technical Specification"', $html);
        $this->assertStringContainsString('Voltage', $html);
        $this->assertStringContainsString('Amperage', $html);
        $this->assertStringContainsString(route('admin.catalog.attributes.toggle-active', $active), $html);
        $this->assertStringContainsString(route('admin.catalog.attributes.toggle-active', $inactive), $html);
        $this->assertStringContainsString(route('admin.catalog.attributes.edit', $active), $html);
    }

    public function test_a_group_with_no_attributes_shows_an_empty_state_in_its_modal(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('group-modal-empty@example.com');
        AttributeGroup::create(['name' => 'Empty Group', 'slug' => 'empty-group-modal', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.attribute-groups'));

        $response->assertOk();
        $this->assertStringContainsString('No attributes in this group yet.', $response->getContent());
    }
}
