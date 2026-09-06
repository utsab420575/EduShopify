<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The Assign tab's category tree (builder/assign.blade.php) used to filter
 * by name alone, same bug as the Category tab before it was fixed: searching
 * a leaf category hid its parent chain and its children, so an admin
 * couldn't tell which of several same-named categories they were looking at.
 * Fixed the same way — ship parent_id per node and walk ancestors/
 * descendants client-side. These tests cover the server-side prerequisites
 * that fix depends on, plus the x-data corruption regression class that has
 * bitten this exact page shape twice already (categories.blade.php,
 * attribute-groups.blade.php).
 */
class CategoryAssignSearchTest extends TestCase
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
            'name' => 'Assign Search Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
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

    public function test_the_tree_payload_sent_to_the_browser_includes_parent_id_for_search_context(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('assign-search-context@example.com');

        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics-assign-ctx', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 0]);
        $computer = Category::create(['name' => 'Computer', 'slug' => 'computer-assign-ctx', 'parent_id' => $electronics->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 1]);
        $laptop = Category::create(['name' => 'Laptop', 'slug' => 'laptop-assign-ctx', 'parent_id' => $computer->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 2]);
        Category::create(['name' => 'MiniLaptop', 'slug' => 'minilaptop-assign-ctx', 'parent_id' => $laptop->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 3]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.assign'));
        $response->assertOk();
        $html = $response->getContent();

        preg_match("/allNodes:\s*JSON\.parse\('(.*?)'\)/s", $html, $matches);
        $this->assertNotEmpty($matches, 'Could not locate the allNodes JSON payload in the rendered page.');

        $jsonString = str_replace(
            ["\\u0022", "\\u0027", "\\u0026", "\\u003C", "\\u003E"],
            ['"', "'", '&', '<', '>'],
            $matches[1]
        );
        $nodes = json_decode($jsonString, true);
        $this->assertIsArray($nodes);

        $byName = collect($nodes)->keyBy('name');
        $this->assertArrayHasKey('parent_id', $byName['Computer'], 'Every node must carry parent_id so the client can walk ancestors/descendants when searching.');
        $this->assertSame($electronics->id, $byName['Computer']['parent_id']);
        $this->assertSame($computer->id, $byName['Laptop']['parent_id']);
        $this->assertSame($laptop->id, $byName['MiniLaptop']['parent_id']);
        $this->assertNull($byName['Electronics']['parent_id']);
    }

    /**
     * Regression: the tree list's x-data="{...}" attribute is double-quoted
     * HTML, same shape as the Category tab and Attribute Groups tab — both
     * broke once when a JS comment used a literal double quote and
     * prematurely closed the attribute, spilling the rest of the Alpine
     * component (including the attribute-assignment checklist logic) out as
     * raw visible page text.
     */
    public function test_the_assign_tree_x_data_attribute_is_not_corrupted(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('assign-attr-corruption@example.com');
        Category::create(['name' => 'Electronics', 'slug' => 'electronics-assign-corruption', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.assign'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/x-data="[^"]*toggleGroup[^"]*"/', $html,
            'The assign tree\'s x-data attribute closed early — a stray unescaped " inside it broke the HTML.');

        $this->assertStringContainsString('Categories', $html);
        $this->assertStringContainsString('Pick a category', $html);
    }
}
