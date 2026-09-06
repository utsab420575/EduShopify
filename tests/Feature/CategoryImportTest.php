<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Category;
use App\Models\User;
use App\Services\CategoryImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Path-based CSV category import ("Electronics > Computer > Laptop" per
 * row) — the replacement for hand-picking a parent from a flat dropdown
 * for every single category. Covers both the service's tree-building logic
 * directly and the full upload -> preview -> confirm controller flow.
 */
class CategoryImportTest extends TestCase
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
            'name' => 'Category Import Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
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

    private function csv(string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'cat_import_') . '.csv';
        file_put_contents($path, $content);

        return new UploadedFile($path, 'categories.csv', 'text/csv', null, true);
    }

    public function test_service_creates_a_multi_level_tree_from_a_single_path(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-tree@example.com');
        $service = app(CategoryImportService::class);

        $rows = [
            ['line' => 2, 'path' => 'Electronics > Computer > Laptop', 'type' => 'product', 'description' => null],
        ];

        $summary = $service->import($rows, $admin->id);

        $this->assertSame(3, $summary['created_count']);
        $this->assertSame(0, $summary['existing_count']);

        $electronics = Category::whereNull('parent_id')->where('name', 'Electronics')->firstOrFail();
        $computer = Category::where('parent_id', $electronics->id)->where('name', 'Computer')->firstOrFail();
        $laptop = Category::where('parent_id', $computer->id)->where('name', 'Laptop')->firstOrFail();

        $this->assertSame('product', $laptop->type);
        $this->assertSame('both', $computer->type, 'Non-leaf segments default to "both", not the row\'s leaf type.');

        $this->assertSame(0, $electronics->level);
        $this->assertSame(1, $computer->level);
        $this->assertSame(2, $laptop->level);
    }

    public function test_overlapping_paths_across_rows_share_the_same_ancestor_not_duplicates(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-overlap@example.com');
        $service = app(CategoryImportService::class);

        $rows = [
            ['line' => 2, 'path' => 'Electronics > Computer', 'type' => 'both', 'description' => null],
            ['line' => 3, 'path' => 'Electronics > Mobile', 'type' => 'both', 'description' => null],
        ];

        $summary = $service->import($rows, $admin->id);

        $this->assertSame(3, $summary['created_count'], 'Electronics, Computer, Mobile — Electronics created once, not twice.');
        $this->assertSame(1, Category::where('name', 'Electronics')->count());
    }

    public function test_reimporting_the_same_file_is_idempotent(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-reimport@example.com');
        $service = app(CategoryImportService::class);

        $rows = [['line' => 2, 'path' => 'Electronics > Computer > Laptop', 'type' => 'product', 'description' => null]];

        $service->import($rows, $admin->id);
        $secondRun = $service->import($rows, $admin->id);

        $this->assertSame(0, $secondRun['created_count']);
        $this->assertSame(3, $secondRun['existing_count']);
        $this->assertSame(3, Category::count());
    }

    public function test_preview_does_not_write_to_the_database(): void
    {
        $this->seedBase();
        $service = app(CategoryImportService::class);

        $rows = [['line' => 2, 'path' => 'Electronics > Computer', 'type' => 'both', 'description' => null]];
        $summary = $service->preview($rows);

        $this->assertSame(0, Category::count());
        $this->assertSame(2, $summary['created_count']);
        $this->assertSame('would_create', $summary['rows'][0]['segments'][0]['status']);
    }

    public function test_a_leaf_name_that_already_exists_under_a_different_parent_is_still_created(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-samename@example.com');
        $service = app(CategoryImportService::class);

        $electronicsA = Category::create(['name' => 'Accessories', 'slug' => 'accessories-electronics', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true]);

        $rows = [['line' => 2, 'path' => 'Furniture > Accessories', 'type' => 'both', 'description' => null]];
        $summary = $service->import($rows, $admin->id);

        // "Accessories" under "Furniture" is a distinct category from the
        // root-level "Accessories" — name uniqueness is scoped per parent,
        // matching manual creation's CategoryRequest rule exactly.
        $this->assertSame(2, $summary['created_count']);
        $this->assertSame(2, Category::where('name', 'Accessories')->count());
    }

    public function test_end_to_end_upload_preview_and_confirm_via_the_controller(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cat-import@example.com');

        $file = $this->csv("path,type\nElectronics,both\nElectronics > Computer,both\nElectronics > Computer > Laptop,product\n");

        $preview = $this->actingAs($admin)->post(route('admin.catalog.categories.import.preview'), ['file' => $file]);
        $preview->assertRedirect(route('admin.catalog.builder.categories'));
        $this->assertSame(0, Category::count(), 'Preview must not write anything yet.');

        $confirm = $this->actingAs($admin)->post(route('admin.catalog.categories.import.store'));
        $confirm->assertRedirect(route('admin.catalog.builder.categories'));

        $this->assertSame(3, Category::count());
        $this->assertDatabaseHas('categories', ['name' => 'Laptop', 'type' => 'product']);
    }

    public function test_confirm_without_a_prior_preview_is_rejected_gracefully(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cat-import-noprev@example.com');

        $response = $this->actingAs($admin)->post(route('admin.catalog.categories.import.store'));

        $response->assertRedirect(route('admin.catalog.builder.categories'));
        $this->assertSame(0, Category::count());
    }

    /**
     * The parent picker in _form.blade.php was rebuilt as a searchable
     * Alpine component instead of a flat <select>, but the actual submitted
     * field is still a single hidden `parent_id` input — the create/update
     * endpoint's contract is unchanged, so this must keep working exactly
     * as before.
     */
    public function test_manual_category_creation_still_works_with_the_new_parent_picker_markup(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cat-manual@example.com');

        $parent = Category::create(['name' => 'Electronics', 'slug' => 'electronics-manual', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.catalog.categories.store'), [
            'name' => 'Computer',
            'parent_id' => $parent->id,
            'type' => 'product',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Computer', 'parent_id' => $parent->id]);
        $this->assertSame(1, Category::where('name', 'Computer')->value('level'));
    }

    /**
     * level is set once at creation and would otherwise go stale the
     * moment a category is re-parented through the edit form's parent
     * picker — CategoryController::update() must recompute it for the
     * moved category *and* cascade the shift down through every descendant
     * still nested under it.
     */
    public function test_reparenting_a_category_through_edit_realigns_its_own_and_its_descendants_levels(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cat-reparent@example.com');

        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics-reparent', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 0]);
        $furniture = Category::create(['name' => 'Furniture', 'slug' => 'furniture-reparent', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 0]);
        $computer = Category::create(['name' => 'Computer', 'slug' => 'computer-reparent', 'parent_id' => $electronics->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 1]);
        $laptop = Category::create(['name' => 'Laptop', 'slug' => 'laptop-reparent', 'parent_id' => $computer->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 2]);
        $furnitureChild = Category::create(['name' => 'Storage', 'slug' => 'storage-reparent', 'parent_id' => $furniture->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 1]);

        // Move "Computer" (with "Laptop" nested under it) under
        // Furniture > Storage instead of the root "Electronics" — Computer
        // goes from level 1 to level 2, and Laptop must shift with it.
        $response = $this->actingAs($admin)->put(route('admin.catalog.categories.update', $computer), [
            'name' => 'Computer',
            'parent_id' => $furnitureChild->id,
            'type' => 'both',
            'is_active' => '1',
        ]);

        $response->assertRedirect();

        $this->assertSame(2, $computer->fresh()->level, 'Computer is now under Furniture > Storage (level 1), so it becomes level 2.');
        $this->assertSame(3, $laptop->fresh()->level, 'Laptop, still nested under Computer, must shift down with it.');
    }

    /**
     * Regression: the parent picker's Alpine `x-data="{...}"` attribute is
     * double-quoted HTML. A JS comment inside it once used a literal
     * double quote ("A > B > C" breadcrumb) which prematurely closed the
     * attribute in the browser, spilling the rest of the JS out as raw
     * visible page text right above the "Name" field.
     *
     * A naive substring check can't tell "present as intended, inside the
     * attribute" from "leaked as visible text" — both make the raw HTML
     * contain the comment. Instead: extract the attribute value up to its
     * *first* `"`, the same way a browser's HTML parser would. If the
     * attribute is well-formed, that first `"` is the real, intended
     * closing quote, and the captured value spans the whole JS object —
     * including `selectParent`, defined at the very end of it. If a stray
     * `"` broke the attribute early (the actual bug), the capture stops
     * there and `selectParent` is missing from it.
     */
    public function test_the_create_modal_parent_picker_attribute_is_not_corrupted(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cat-attr-corruption@example.com');
        Category::create(['name' => 'Electronics', 'slug' => 'electronics-corruption-check', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.categories'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/x-data="[^"]*selectParent[^"]*"/', $html,
            'The parent picker\'s x-data attribute closed early — a stray unescaped " inside it broke the HTML.');

        $this->assertStringContainsString('id="parent_id_search"', $html);
        $this->assertStringContainsString('Parent Category', $html);
    }

    /**
     * The tree list's search box used to filter allNodes by name alone, so
     * searching "Laptop" showed only bare "Laptop" rows with no indication
     * of which branch they belonged to or what was nested under them. The
     * fix walks parent_id client-side to pull in ancestors/descendants of a
     * match, which only works if parent_id is actually shipped to the
     * browser in the first place — this asserts that prerequisite holds.
     */
    public function test_the_tree_payload_sent_to_the_browser_includes_parent_id_for_search_context(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cat-search-context@example.com');

        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics-search-ctx', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 0]);
        $computer = Category::create(['name' => 'Computer', 'slug' => 'computer-search-ctx', 'parent_id' => $electronics->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 1]);
        $laptop = Category::create(['name' => 'Laptop', 'slug' => 'laptop-search-ctx', 'parent_id' => $computer->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 2]);
        Category::create(['name' => 'MiniLaptop', 'slug' => 'minilaptop-search-ctx', 'parent_id' => $laptop->id, 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true, 'level' => 3]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.categories'));
        $response->assertOk();
        $html = $response->getContent();

        // Js::from() emits `JSON.parse('...')` with quotes/HTML-sensitive
        // chars unicode-escaped so the payload survives sitting inside a
        // double-quoted HTML attribute — unescape those back to real JSON.
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
     * Regression: the tree list's `x-data="{...}"` attribute is
     * double-quoted HTML, same as the parent picker's. A JS comment
     * explaining the search-context fix once quoted an example category
     * name in double quotes (searching "Laptop" hid ...), which
     * prematurely closed the attribute and spilled the rest of the Alpine
     * component — including the filtered/pageItems/goToPage methods — out
     * as raw visible page text.
     *
     * Same detection approach as the parent-picker regression test: capture
     * the attribute value up to its first unescaped `"` the way a browser
     * would, and check it still reaches all the way to goToPage, defined
     * at the very end of the object.
     */
    public function test_the_tree_list_x_data_attribute_is_not_corrupted(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('cat-tree-attr-corruption@example.com');
        Category::create(['name' => 'Electronics', 'slug' => 'electronics-tree-corruption', 'type' => 'both', 'approval_status' => 'approved', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.categories'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/x-data="[^"]*goToPage\(p\)[^"]*"/', $html,
            'The tree list\'s x-data attribute closed early — a stray unescaped " inside it broke the HTML.');

        $this->assertStringContainsString('All Categories', $html);
        $this->assertStringContainsString('Search categories', $html);
    }
}
