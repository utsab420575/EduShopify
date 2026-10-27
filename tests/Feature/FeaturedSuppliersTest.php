<?php

namespace Tests\Feature;

use App\Models\AccountBenefitOverride;
use App\Models\SubscriptionBenefit;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanBenefit;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\Billing\BenefitResolver;
use App\Services\Billing\FeaturedSupplierService;
use App\Services\SubscriptionSelectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Homepage "Featured Suppliers" and the directory's ★ Featured filter come
 * from the featured_supplier benefit (override → snapshot → plan → default).
 */
class FeaturedSuppliersTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionBenefit $featured;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->featured = SubscriptionBenefit::create(['code' => 'featured_supplier', 'name' => 'Featured Supplier', 'value_type' => 'boolean']);
    }

    /** An active, publicly listed supplier with the given rating. */
    private function supplier(string $name, float $rating): User
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual', 'capability' => 'supplier', 'name' => $name,
            'email' => Str::slug($name).'@example.com', 'phone' => '+1555'.random_int(1000000, 9999999), 'password' => 'Password123!',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);
        $user->account->supplierCapability()->update(['status' => 'active']);

        SupplierProfile::updateOrCreate(['account_id' => $user->account->id], [
            'display_name' => $name, 'slug' => Str::slug($name), 'profile_completed_at' => now(), 'rating' => $rating,
        ]);

        return $user->fresh();
    }

    private function featuredPlan(): SubscriptionPlan
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Featured Plan', 'slug' => 'featured-plan-'.uniqid(), 'billing_type' => 'free', 'price' => 0,
            'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
        ]);
        SubscriptionPlanBenefit::create(['subscription_plan_id' => $plan->id, 'subscription_benefit_id' => $this->featured->id, 'value' => true]);

        return $plan;
    }

    private function featuredIds(): array
    {
        FeaturedSupplierService::flush();
        app(BenefitResolver::class)->forget();

        return app(FeaturedSupplierService::class)->featuredAccountIds();
    }

    public function test_featured_comes_from_plan_snapshot_or_override_and_an_override_can_remove_it(): void
    {
        $viaPlan = $this->supplier('Plan Featured Co', 3.0);
        $viaOverride = $this->supplier('Override Featured Co', 4.0);
        $removed = $this->supplier('Removed Featured Co', 5.0);
        $plain = $this->supplier('Plain Co', 4.5);

        $plan = $this->featuredPlan();
        app(SubscriptionSelectionService::class)->activateFree($viaPlan->account, $plan, $viaPlan);
        app(SubscriptionSelectionService::class)->activateFree($removed->account, $plan, $removed);
        AccountBenefitOverride::create(['account_id' => $viaOverride->account->id, 'subscription_benefit_id' => $this->featured->id, 'value' => true]);
        AccountBenefitOverride::create(['account_id' => $removed->account->id, 'subscription_benefit_id' => $this->featured->id, 'value' => false]);

        $ids = $this->featuredIds();
        sort($ids);
        $expected = [$viaPlan->account->id, $viaOverride->account->id];
        sort($expected);

        $this->assertSame($expected, $ids);
        $this->assertNotContains($plain->account->id, $ids);
        $this->assertNotContains($removed->account->id, $ids);
    }

    public function test_homepage_shows_featured_by_rating_then_tops_up_to_four(): void
    {
        $low = $this->supplier('Low Featured', 2.0);
        $high = $this->supplier('High Featured', 4.0);
        foreach ([['Top Unfeatured', 5.0], ['Second Unfeatured', 4.8], ['Third Unfeatured', 4.6]] as [$name, $rating]) {
            $this->supplier($name, $rating);
        }
        foreach ([$low, $high] as $user) {
            AccountBenefitOverride::create(['account_id' => $user->account->id, 'subscription_benefit_id' => $this->featured->id, 'value' => true]);
        }
        $this->featuredIds();

        $list = app(FeaturedSupplierService::class)->forHomepage(max: 20, min: 4);

        // Featured first (highest rating first), then the best-rated others up to 4.
        $this->assertSame(['High Featured', 'Low Featured', 'Top Unfeatured', 'Second Unfeatured'], $list->pluck('display_name')->all());
        $this->assertSame([true, true, false, false], $list->pluck('is_featured_supplier')->all());

        $home = $this->get(route('v2.home'))->assertOk();
        $home->assertSee('High Featured')->assertSee('title="Featured supplier"', false);
        $home->assertSee(route('v2.suppliers.index', ['featured' => 1]), false);
    }

    public function test_directory_featured_filter(): void
    {
        $featured = $this->supplier('Directory Featured', 3.0);
        $this->supplier('Directory Plain', 5.0);
        AccountBenefitOverride::create(['account_id' => $featured->account->id, 'subscription_benefit_id' => $this->featured->id, 'value' => true]);
        $this->featuredIds();

        $this->get(route('v2.suppliers.index', ['featured' => 1]))
            ->assertOk()->assertSee('Directory Featured')->assertDontSee('Directory Plain');

        $this->get(route('v2.suppliers.index'))
            ->assertOk()->assertSee('Directory Featured')->assertSee('Directory Plain');
    }

    public function test_without_the_benefit_the_homepage_still_shows_four_top_rated(): void
    {
        $this->featured->update(['is_active' => false]);
        foreach (range(1, 5) as $i) {
            $this->supplier("Rated Supplier {$i}", $i);
        }

        $this->assertSame([], $this->featuredIds());
        $list = app(FeaturedSupplierService::class)->forHomepage(max: 20, min: 4);
        $this->assertCount(4, $list);
        $this->assertSame('Rated Supplier 5', $list->first()->display_name);
        $this->assertFalse((bool) $list->first()->is_featured_supplier);
    }
}
