<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\AttributeGroup;
use App\Models\User;
use App\Services\AttributeGroupImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Flat CSV import for attribute groups. The CSV only carries "name" and
 * "description" — both required per row. Everything else (is_active,
 * sort_order, created_by, timestamps) is set automatically, the same
 * preview/confirm shape as CategoryImportTest, minus any path/tree parsing
 * since attribute groups aren't hierarchical.
 */
class AttributeGroupImportTest extends TestCase
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
            'name' => 'Attribute Group Import Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
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
        $path = tempnam(sys_get_temp_dir(), 'attr_group_import_') . '.csv';
        file_put_contents($path, $content);

        return new UploadedFile($path, 'attribute-groups.csv', 'text/csv', null, true);
    }

    public function test_service_creates_groups_with_auto_fields_populated(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-auto-fields@example.com');
        $service = app(AttributeGroupImportService::class);

        $rows = [
            ['line' => 2, 'name' => 'Technical Specification', 'description' => 'Core specs.'],
            ['line' => 3, 'name' => 'Physical Dimensions', 'description' => 'Size and weight'],
        ];

        $summary = $service->import($rows, $admin->id);

        $this->assertSame(2, $summary['created_count']);
        $this->assertSame(0, $summary['existing_count']);

        $first = AttributeGroup::where('name', 'Technical Specification')->firstOrFail();
        $second = AttributeGroup::where('name', 'Physical Dimensions')->firstOrFail();

        $this->assertSame('Size and weight', $second->description);
        $this->assertTrue($first->is_active, 'is_active must default true regardless of the CSV.');
        $this->assertTrue($second->is_active);
        $this->assertSame($admin->id, $first->created_by_user_id);
        $this->assertSame($admin->id, $second->created_by_user_id);
        $this->assertNotNull($first->created_at);
        $this->assertGreaterThan($first->sort_order, $second->sort_order, 'sort_order must be auto-generated, increasing per row.');
    }

    public function test_generated_sort_order_continues_after_existing_groups(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-sort-continue@example.com');
        AttributeGroup::create(['name' => 'Existing Group', 'slug' => 'existing-group-sort', 'sort_order' => 100, 'is_active' => true]);

        $service = app(AttributeGroupImportService::class);
        $summary = $service->import([['line' => 2, 'name' => 'New Group', 'description' => 'A new one.']], $admin->id);

        $this->assertSame(1, $summary['created_count']);
        $created = AttributeGroup::where('name', 'New Group')->firstOrFail();
        $this->assertGreaterThan(100, $created->sort_order, 'New rows must be appended after the current max sort_order, not start back at 0.');
    }

    public function test_a_row_missing_description_is_reported_as_an_error_not_silently_dropped(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-missing-desc@example.com');
        $service = app(AttributeGroupImportService::class);

        $rows = [['line' => 2, 'name' => 'Technical Specification', 'description' => '']];
        $summary = $service->import($rows, $admin->id);

        $this->assertSame(0, $summary['created_count']);
        $this->assertSame(1, $summary['error_count']);
        $this->assertSame('Description is required.', $summary['rows'][0]['error']);
        $this->assertSame(0, AttributeGroup::count());
    }

    public function test_a_row_matching_an_existing_group_by_name_is_reused_not_duplicated(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-reuse@example.com');
        AttributeGroup::create(['name' => 'Technical Specification', 'slug' => 'technical-specification', 'is_active' => true]);

        $service = app(AttributeGroupImportService::class);
        $rows = [['line' => 2, 'name' => 'technical specification', 'description' => 'Core specs.']];

        $summary = $service->import($rows, $admin->id);

        $this->assertSame(0, $summary['created_count']);
        $this->assertSame(1, $summary['existing_count'], 'Name matching must be case-insensitive.');
        $this->assertSame(1, AttributeGroup::count());
    }

    public function test_duplicate_names_within_the_same_file_are_only_created_once(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-dupe-file@example.com');
        $service = app(AttributeGroupImportService::class);

        $rows = [
            ['line' => 2, 'name' => 'Warranty & Support', 'description' => 'Coverage details.'],
            ['line' => 3, 'name' => 'Warranty & Support', 'description' => 'Coverage details.'],
        ];

        $summary = $service->import($rows, $admin->id);

        $this->assertSame(1, $summary['created_count']);
        $this->assertSame(1, $summary['existing_count']);
        $this->assertSame(1, AttributeGroup::count());
    }

    public function test_reimporting_the_same_file_is_idempotent(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('svc-reimport@example.com');
        $service = app(AttributeGroupImportService::class);
        $rows = [['line' => 2, 'name' => 'Warranty & Support', 'description' => 'Coverage details.']];

        $service->import($rows, $admin->id);
        $secondRun = $service->import($rows, $admin->id);

        $this->assertSame(0, $secondRun['created_count']);
        $this->assertSame(1, $secondRun['existing_count']);
        $this->assertSame(1, AttributeGroup::count());
    }

    public function test_preview_does_not_write_to_the_database(): void
    {
        $this->seedBase();
        $service = app(AttributeGroupImportService::class);
        $rows = [['line' => 2, 'name' => 'Technical Specification', 'description' => 'Core specs.']];

        $summary = $service->preview($rows);

        $this->assertSame(0, AttributeGroup::count());
        $this->assertSame(1, $summary['created_count']);
        $this->assertSame('would_create', $summary['rows'][0]['status']);
    }

    public function test_end_to_end_upload_preview_and_confirm_via_the_controller(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-group-import@example.com');

        $file = $this->csv("name,description\nTechnical Specification,Core specs.\nPhysical Dimensions,Size and weight.\n");

        $preview = $this->actingAs($admin)->post(route('admin.catalog.attribute-groups.import.preview'), ['file' => $file]);
        $preview->assertRedirect(route('admin.catalog.builder.attribute-groups'));
        $this->assertSame(0, AttributeGroup::count(), 'Preview must not write anything yet.');

        $confirm = $this->actingAs($admin)->post(route('admin.catalog.attribute-groups.import.store'));
        $confirm->assertRedirect(route('admin.catalog.builder.attribute-groups'));

        $this->assertSame(2, AttributeGroup::count());
        $group = AttributeGroup::where('name', 'Physical Dimensions')->firstOrFail();
        $this->assertSame('Size and weight.', $group->description);
        $this->assertTrue($group->is_active);
        $this->assertSame($admin->id, $group->created_by_user_id, 'created_by must be whoever confirmed the import.');
    }

    public function test_a_csv_missing_the_description_column_entirely_is_rejected(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-group-no-desc-col@example.com');

        $file = $this->csv("name\nTechnical Specification\n");

        $response = $this->actingAs($admin)->post(route('admin.catalog.attribute-groups.import.preview'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, AttributeGroup::count());
    }

    public function test_confirm_without_a_prior_preview_is_rejected_gracefully(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-group-import-noprev@example.com');

        $response = $this->actingAs($admin)->post(route('admin.catalog.attribute-groups.import.store'));

        $response->assertRedirect(route('admin.catalog.builder.attribute-groups'));
        $this->assertSame(0, AttributeGroup::count());
    }

    public function test_template_download_returns_a_csv_with_only_the_required_columns(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-group-template@example.com');

        $response = $this->actingAs($admin)->get(route('admin.catalog.attribute-groups.import.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringStartsWith('name,description', $response->getContent());
    }

    /**
     * Regression guard for the exact bug class that broke the category
     * builder page: a JS comment inside the tree list's double-quoted
     * x-data="{...}" attribute once used a literal double quote and
     * prematurely closed the attribute, spilling the rest of the Alpine
     * component out as raw visible page text. The attribute-groups builder
     * page has the same shape (x-data with a filtered/pageItems/goToPage
     * getter chain) — assert it stays well-formed after adding the import UI.
     */
    public function test_the_attribute_group_list_x_data_attribute_is_not_corrupted(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-group-attr-corruption@example.com');
        AttributeGroup::create(['name' => 'Technical Specification', 'slug' => 'technical-specification-corruption', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.attribute-groups'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/x-data="[^"]*goToPage\(p\)[^"]*"/', $html,
            'The attribute group list\'s x-data attribute closed early — a stray unescaped " inside it broke the HTML.');

        $this->assertStringContainsString('All Attribute Groups', $html);
        $this->assertStringContainsString('Import Attribute Groups', $html);
    }
}
