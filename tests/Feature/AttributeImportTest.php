<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\User;
use App\Services\AttributeImportService;
use App\Services\AttributeValueImportService;
use Database\Seeders\InputTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Two-file CSV bulk import for the Category Builder's Attributes tab: a
 * flat "attributes" CSV (one attribute per row) and a separate "attribute
 * values" CSV (one predefined option per row, referencing its attribute by
 * name) — run in that order, since values can't exist without their
 * attribute already being there. Same preview/confirm shape as
 * CategoryImportTest and AttributeGroupImportTest.
 */
class AttributeImportTest extends TestCase
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
            'name' => 'Attribute Import Admin', 'email' => $email, 'phone' => '+1000000' . random_int(1000, 9999),
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

    private function csv(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'attr_import_') . '.csv';
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, 'text/csv', null, true);
    }

    /* ── Attributes CSV ────────────────────────────────────────────────── */

    public function test_service_creates_attributes_with_auto_fields_and_resolved_group(): void
    {
        $this->seedBase();
        $group = AttributeGroup::create(['name' => 'Technical Specification', 'slug' => 'technical-specification-attr', 'is_active' => true]);
        $service = app(AttributeImportService::class);

        $rows = [
            ['line' => 2, 'name' => 'Voltage', 'input_type' => 'number', 'attribute_group' => 'Technical Specification', 'is_required' => false, 'is_filterable' => true, 'is_variant' => false],
            ['line' => 3, 'name' => 'Color', 'input_type' => 'select', 'attribute_group' => null, 'is_required' => false, 'is_filterable' => true, 'is_variant' => true],
        ];

        $summary = $service->import($rows);

        $this->assertSame(2, $summary['created_count']);

        $voltage = Attribute::where('name', 'Voltage')->firstOrFail();
        $color = Attribute::where('name', 'Color')->firstOrFail();

        $this->assertSame($group->id, $voltage->attribute_group_id);
        $this->assertNull($color->attribute_group_id);
        $this->assertTrue($voltage->is_active, 'is_active must default true regardless of the CSV.');
        $this->assertTrue($voltage->is_filterable);
        $this->assertFalse($voltage->is_variant);
        $this->assertNotNull($voltage->input_type_id, 'input_type_id must be resolved from the input_types lookup table.');
        $this->assertGreaterThan($voltage->sort_order, $color->sort_order, 'sort_order must be auto-generated, increasing per row.');
    }

    public function test_an_unknown_input_type_is_reported_as_a_row_error(): void
    {
        $this->seedBase();
        $service = app(AttributeImportService::class);

        $summary = $service->import([['line' => 2, 'name' => 'Weird', 'input_type' => 'freeform', 'attribute_group' => null, 'is_required' => false, 'is_filterable' => false, 'is_variant' => false]]);

        $this->assertSame(0, $summary['created_count']);
        $this->assertSame(1, $summary['error_count']);
        $this->assertStringContainsString('Unknown input_type', $summary['rows'][0]['error']);
        $this->assertSame(0, Attribute::count());
    }

    public function test_a_missing_attribute_group_is_reported_as_a_row_error_not_auto_created(): void
    {
        $this->seedBase();
        $service = app(AttributeImportService::class);

        $summary = $service->import([['line' => 2, 'name' => 'Voltage', 'input_type' => 'number', 'attribute_group' => 'Does Not Exist', 'is_required' => false, 'is_filterable' => false, 'is_variant' => false]]);

        $this->assertSame(0, $summary['created_count']);
        $this->assertSame(1, $summary['error_count']);
        $this->assertStringContainsString('not found', $summary['rows'][0]['error']);
        $this->assertSame(0, AttributeGroup::count(), 'Unlike categories, attribute groups must NOT be auto-created from a typo in the CSV.');
    }

    public function test_a_row_matching_an_existing_attribute_by_name_is_reused_not_duplicated(): void
    {
        $this->seedBase();
        Attribute::create(['name' => 'Voltage', 'slug' => 'voltage-reuse', 'input_type' => 'number', 'is_active' => true]);

        $service = app(AttributeImportService::class);
        $summary = $service->import([['line' => 2, 'name' => 'voltage', 'input_type' => 'number', 'attribute_group' => null, 'is_required' => false, 'is_filterable' => false, 'is_variant' => false]]);

        $this->assertSame(0, $summary['created_count']);
        $this->assertSame(1, $summary['existing_count'], 'Name matching must be case-insensitive.');
        $this->assertSame(1, Attribute::count());
    }

    public function test_preview_does_not_write_to_the_database(): void
    {
        $this->seedBase();
        $service = app(AttributeImportService::class);
        $summary = $service->preview([['line' => 2, 'name' => 'Voltage', 'input_type' => 'number', 'attribute_group' => null, 'is_required' => false, 'is_filterable' => false, 'is_variant' => false]]);

        $this->assertSame(0, Attribute::count());
        $this->assertSame('would_create', $summary['rows'][0]['status']);
    }

    public function test_end_to_end_upload_preview_and_confirm_via_the_controller(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-import@example.com');

        $file = $this->csv('attributes.csv', "name,input_type,attribute_group,is_required,is_filterable,is_variant\nVoltage,number,,0,1,0\nColor,select,,0,1,1\n");

        $preview = $this->actingAs($admin)->post(route('admin.catalog.attributes.import.preview'), ['file' => $file]);
        $preview->assertRedirect(route('admin.catalog.builder.attributes'));
        $this->assertSame(0, Attribute::count());

        $confirm = $this->actingAs($admin)->post(route('admin.catalog.attributes.import.store'));
        $confirm->assertRedirect(route('admin.catalog.builder.attributes'));

        $this->assertSame(2, Attribute::count());
        $this->assertDatabaseHas('attributes', ['name' => 'Color', 'input_type' => 'select']);
    }

    /* ── Attribute Values CSV ─────────────────────────────────────────── */

    public function test_values_service_creates_values_for_an_existing_select_attribute(): void
    {
        $this->seedBase();
        $attribute = Attribute::create(['name' => 'Color', 'slug' => 'color-values', 'input_type' => 'color', 'is_active' => true]);
        $service = app(AttributeValueImportService::class);

        $rows = [
            ['line' => 2, 'attribute_name' => 'Color', 'value' => 'Red'],
            ['line' => 3, 'attribute_name' => 'Color', 'value' => 'Black'],
        ];

        $summary = $service->import($rows);

        $this->assertSame(2, $summary['created_count']);
        $red = AttributeValue::where('attribute_id', $attribute->id)->where('value', 'Red')->firstOrFail();
        $black = AttributeValue::where('attribute_id', $attribute->id)->where('value', 'Black')->firstOrFail();
        $this->assertGreaterThan($red->sort_order, $black->sort_order, 'sort_order must be auto-generated per attribute, increasing per row.');
    }

    public function test_the_csv_never_sets_color_hex_even_for_a_color_attribute(): void
    {
        $this->seedBase();
        Attribute::create(['name' => 'Color', 'slug' => 'color-no-hex', 'input_type' => 'color', 'is_active' => true]);
        $service = app(AttributeValueImportService::class);

        $service->import([['line' => 2, 'attribute_name' => 'Color', 'value' => 'Red']]);

        $value = AttributeValue::where('value', 'Red')->firstOrFail();
        $this->assertNull($value->color_hex, 'color_hex is a supplier-side detail set elsewhere, never from the admin CSV.');
    }

    public function test_a_value_row_for_a_missing_attribute_is_reported_as_an_error(): void
    {
        $this->seedBase();
        $service = app(AttributeValueImportService::class);

        $summary = $service->import([['line' => 2, 'attribute_name' => 'Does Not Exist', 'value' => 'Red']]);

        $this->assertSame(0, $summary['created_count']);
        $this->assertSame(1, $summary['error_count']);
        $this->assertStringContainsString('not found', $summary['rows'][0]['error']);
    }

    public function test_a_value_row_for_a_non_option_attribute_is_reported_as_an_error(): void
    {
        $this->seedBase();
        Attribute::create(['name' => 'Voltage', 'slug' => 'voltage-non-option', 'input_type' => 'number', 'is_active' => true]);
        $service = app(AttributeValueImportService::class);

        $summary = $service->import([['line' => 2, 'attribute_name' => 'Voltage', 'value' => '220']]);

        $this->assertSame(0, $summary['created_count']);
        $this->assertSame(1, $summary['error_count']);
        $this->assertStringContainsString("aren't applicable", $summary['rows'][0]['error']);
    }

    public function test_reimporting_the_same_values_file_is_idempotent(): void
    {
        $this->seedBase();
        Attribute::create(['name' => 'Color', 'slug' => 'color-idempotent', 'input_type' => 'color', 'is_active' => true]);
        $service = app(AttributeValueImportService::class);
        $rows = [['line' => 2, 'attribute_name' => 'Color', 'value' => 'Red']];

        $service->import($rows);
        $secondRun = $service->import($rows);

        $this->assertSame(0, $secondRun['created_count']);
        $this->assertSame(1, $secondRun['existing_count']);
        $this->assertSame(1, AttributeValue::count());
    }

    public function test_values_end_to_end_upload_preview_and_confirm_via_the_controller(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-value-import@example.com');
        Attribute::create(['name' => 'Color', 'slug' => 'color-e2e', 'input_type' => 'color', 'is_active' => true]);

        $file = $this->csv('values.csv', "attribute_name,value\nColor,Red\nColor,Black\n");

        $preview = $this->actingAs($admin)->post(route('admin.catalog.attributes.values-import.preview'), ['file' => $file]);
        $preview->assertRedirect(route('admin.catalog.builder.attributes'));
        $this->assertSame(0, AttributeValue::count());

        $confirm = $this->actingAs($admin)->post(route('admin.catalog.attributes.values-import.store'));
        $confirm->assertRedirect(route('admin.catalog.builder.attributes'));

        $this->assertSame(2, AttributeValue::count());
    }

    public function test_confirm_without_a_prior_preview_is_rejected_gracefully_for_both_importers(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-import-noprev@example.com');

        $this->actingAs($admin)->post(route('admin.catalog.attributes.import.store'))
            ->assertRedirect(route('admin.catalog.builder.attributes'));
        $this->actingAs($admin)->post(route('admin.catalog.attributes.values-import.store'))
            ->assertRedirect(route('admin.catalog.builder.attributes'));

        $this->assertSame(0, Attribute::count());
        $this->assertSame(0, AttributeValue::count());
    }

    /**
     * Regression guard for the exact bug class that broke the category and
     * attribute-group builder pages: a JS comment inside the tree list's
     * double-quoted x-data="{...}" attribute once used a literal double
     * quote and prematurely closed the attribute, spilling the rest of the
     * Alpine component out as raw visible page text.
     */
    public function test_the_attribute_list_x_data_attribute_is_not_corrupted(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-attr-corruption@example.com');
        Attribute::create(['name' => 'Voltage', 'slug' => 'voltage-corruption', 'input_type' => 'number', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.attributes'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/x-data="[^"]*goToPage\(p\)[^"]*"/', $html,
            'The attribute list\'s x-data attribute closed early — a stray unescaped " inside it broke the HTML.');

        $this->assertStringContainsString('All Attributes', $html);
        $this->assertStringContainsString('Import Attributes', $html);
        $this->assertStringContainsString('Import Attribute Values', $html);
    }

    /**
     * The "Needs values" / "Has values" badges and their filter checkboxes
     * are computed client-side from has_options + values_count, so this
     * asserts the server actually ships both fields per attribute — without
     * them the filters would silently show nothing.
     */
    public function test_the_page_ships_has_options_and_values_count_for_the_value_status_filters(): void
    {
        $this->seedBase();
        $admin = $this->makeAdmin('attr-value-status-filters@example.com');
        $withoutValues = Attribute::create(['name' => 'Certifications', 'slug' => 'certifications-status', 'input_type' => 'multi_select', 'is_active' => true]);
        $withValues = Attribute::create(['name' => 'Color', 'slug' => 'color-status', 'input_type' => 'color', 'is_active' => true]);
        AttributeValue::create(['attribute_id' => $withValues->id, 'value' => 'Red', 'slug' => 'red-status', 'sort_order' => 0, 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.catalog.builder.attributes'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('Only show attributes that need predefined values', $html);
        $this->assertStringContainsString('Only show attributes that already have predefined values', $html);

        preg_match("/allNodes:\s*JSON\.parse\('(.*?)'\)/s", $html, $matches);
        $this->assertNotEmpty($matches);
        $jsonString = str_replace(["\\u0022", "\\u0027", "\\u0026", "\\u003C", "\\u003E"], ['"', "'", '&', '<', '>'], $matches[1]);
        $nodes = collect(json_decode($jsonString, true))->keyBy('name');

        $this->assertTrue($nodes['Certifications']['has_options']);
        $this->assertSame(0, $nodes['Certifications']['values_count']);
        $this->assertTrue($nodes['Color']['has_options']);
        $this->assertSame(1, $nodes['Color']['values_count']);
    }
}
