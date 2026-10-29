<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Product wizard step 1: "Summary Description of Product" (Summernote, no
 * images) is stored — sanitized — in listings.short_description; the old
 * long `description` is no longer collected (NULL for new products, kept
 * as-is on older ones).
 */
class ListingSummaryDescriptionTest extends TestCase
{
    use RefreshDatabase;

    private function makeSupplier(): User
    {
        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual', 'capability' => 'supplier', 'name' => 'Summary Supplier',
            'email' => 'summary@example.com', 'phone' => '+15550001111', 'password' => 'Password123!',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);
        $user->account->supplierCapability()->update(['status' => 'active']);
        $user->activateTeamContext();
        app(PermissionRegistrar::class)->setPermissionsTeamId($user->account->id);

        $plan = SubscriptionPlan::create([
            'name' => 'Free', 'slug' => 'free-'.uniqid(), 'billing_type' => 'free', 'price' => 0, 'currency_code' => 'USD',
            'is_free' => true, 'is_active' => true, 'max_active_listings' => 10, 'max_monthly_quotations' => 10, 'rfq_delay_minutes' => 0,
        ]);
        Subscription::create([
            'supplier_account_id' => $user->account->id, 'plan_id' => $plan->id,
            'selected_by_user_id' => $user->id, 'provider' => 'free', 'status' => 'pending',
        ])->activate();

        return $user->fresh();
    }

    public function test_sanitizer_keeps_formatting_and_removes_images_and_scripts(): void
    {
        $html = RichText::sanitize('<h3>Specs</h3><p onclick="x()">Fast <b>CPU</b><img src="x" onerror="alert(1)"></p>'
            .'<ul><li>8 GB</li></ul><script>alert(1)</script><a href="javascript:alert(1)">bad</a>');

        $this->assertSame('<h3>Specs</h3><p>Fast <b>CPU</b></p><ul><li>8 GB</li></ul><a>bad</a>', $html);
        $this->assertSame('', RichText::sanitize('<p><br></p>'));
        $this->assertSame('Specs Fast CPU 8 GB', RichText::toText('<h3>Specs</h3><p>Fast <b>CPU</b></p><ul><li>8 GB</li></ul><script>alert(1)</script>'));
    }

    public function test_wizard_step_1_stores_the_sanitized_summary_and_no_long_description(): void
    {
        $supplier = $this->makeSupplier();
        $type = ListingType::firstOrCreate(['code' => 'product'], ['name' => 'Product', 'is_active' => true]);

        $this->actingAs($supplier)->postJson(route('supplier.catalog.listings.wizard.step1'), [
            'listing_type_id' => $type->id,
            'name' => 'Lab Microscope',
            'short_description' => '<p>Optical <strong>1000x</strong></p><p><img src="data:image/png;base64,AAAA"></p><ul><li>LED</li></ul>',
            'description' => 'should be ignored',
        ])->assertOk();

        $listing = Listing::where('name', 'Lab Microscope')->firstOrFail();
        $this->assertSame('<p>Optical <strong>1000x</strong></p><p></p><ul><li>LED</li></ul>', $listing->short_description);
        $this->assertNull($listing->description);

        // Editing an older listing keeps its existing long description.
        $listing->update(['description' => 'Legacy long text']);
        $this->actingAs($supplier)->postJson(route('supplier.catalog.listings.wizard.step1'), [
            'listing_id' => $listing->id,
            'listing_type_id' => $type->id,
            'name' => 'Lab Microscope',
            'short_description' => '<p>Updated</p>',
        ])->assertOk();

        $listing->refresh();
        $this->assertSame('<p>Updated</p>', $listing->short_description);
        $this->assertSame('Legacy long text', $listing->description);
    }

    public function test_summary_helpers_and_rich_text_component(): void
    {
        $html = new Listing(['short_description' => '<p>Fast <b>CPU</b></p><ul><li>8 GB</li></ul>']);
        $legacy = new Listing(['short_description' => "Line one\nA & B < 5"]);

        $this->assertSame('<p>Fast <b>CPU</b></p><ul><li>8 GB</li></ul>', $html->summaryHtml());
        $this->assertSame('Fast CPU 8 GB', $html->summaryText());
        // Older plain-text summary: escaped, line breaks kept.
        $this->assertSame("Line one<br />\nA &amp; B &lt; 5", $legacy->summaryHtml());

        $rendered = Blade::render('<x-rich-text :html="$h" class="text-sm" />', ['h' => $html->summaryHtml()]);
        $this->assertStringContainsString('class="rich-text leading-relaxed break-words text-sm"', $rendered);
        $this->assertStringContainsString('<ul><li>8 GB</li></ul>', $rendered);
    }
}
