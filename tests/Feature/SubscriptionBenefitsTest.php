<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountBenefitOverride;
use App\Models\AccountMember;
use App\Models\Subscription;
use App\Models\SubscriptionBenefit;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanBenefit;
use App\Models\User;
use App\Services\AccountRegistrationService;
use App\Services\Billing\BenefitResolver;
use App\Services\Billing\ResolvedBenefit;
use App\Services\SubscriptionSelectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Dynamic Subscription Benefits: the resolver's priority
 * (override → snapshot → plan → default) and the three admin areas
 * (Manage Benefits, Plan Benefit Assignment, Supplier Benefit Overrides).
 */
class SubscriptionBenefitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CapabilityTypeSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SystemAccountSeeder::class);
    }

    private function makeStaff(string $email, string $role): User
    {
        $system = Account::where('account_number', 'SYSTEM')->firstOrFail();
        $user = User::create([
            'name' => 'Staff', 'email' => $email, 'phone' => '+1000000'.random_int(1000, 9999),
            'password' => bcrypt('Password123!'), 'email_verified_at' => now(), 'status' => 'active',
        ]);
        AccountMember::create([
            'account_id' => $system->id, 'user_id' => $user->id, 'member_type' => 'member',
            'is_primary_owner' => false, 'status' => 'active', 'joined_at' => now(),
        ]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($system->id);
        $user->assignRole($role);

        return $user->fresh();
    }

    private function makeSupplier(string $email): User
    {
        $user = app(AccountRegistrationService::class)->register([
            'account_type' => 'individual', 'capability' => 'supplier', 'name' => 'Benefit Supplier',
            'email' => $email, 'phone' => '+1555000'.random_int(1000, 9999), 'password' => 'Password123!',
        ]);
        $user->markEmailAsVerified();
        $user->update(['status' => 'active']);
        $user->account->update(['status' => 'active']);
        $user->account->supplierCapability()->update(['status' => 'active']);

        return $user->fresh();
    }

    private function plan(string $name = 'Pro'): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name).'-'.uniqid(), 'billing_type' => 'free', 'price' => 0,
            'currency_code' => 'USD', 'is_free' => true, 'is_active' => true,
        ]);
    }

    private function benefit(string $code, string $type, mixed $default = null): SubscriptionBenefit
    {
        return SubscriptionBenefit::create(['code' => $code, 'name' => ucwords(str_replace('_', ' ', $code)), 'value_type' => $type, 'default_value' => $default]);
    }

    private function resolve(Account $account, string $code): ResolvedBenefit
    {
        $resolver = app(BenefitResolver::class);
        $resolver->forget();

        return $resolver->forAccount($account->fresh())->get($code);
    }

    public function test_resolution_priority_override_then_snapshot_then_plan_then_default(): void
    {
        $supplier = $this->makeSupplier('resolve@example.com');
        $account = $supplier->account;
        $listings = $this->benefit('max_active_listings', 'integer', 5);
        $featured = $this->benefit('featured_supplier', 'boolean');

        // 4. Default — no subscription yet.
        $this->assertSame(5, $this->resolve($account, 'max_active_listings')->value);
        $this->assertSame(ResolvedBenefit::SOURCE_DEFAULT, $this->resolve($account, 'max_active_listings')->source);

        // 2. Subscribing snapshots the plan's benefits.
        $plan = $this->plan();
        SubscriptionPlanBenefit::create(['subscription_plan_id' => $plan->id, 'subscription_benefit_id' => $listings->id, 'value' => 25]);
        SubscriptionPlanBenefit::create(['subscription_plan_id' => $plan->id, 'subscription_benefit_id' => $featured->id, 'value' => true]);
        app(SubscriptionSelectionService::class)->activateFree($account, $plan, $supplier);

        $this->assertSame(25, $this->resolve($account, 'max_active_listings')->value);
        $this->assertSame(ResolvedBenefit::SOURCE_SNAPSHOT, $this->resolve($account, 'max_active_listings')->source);

        // Changing the plan later does not change an existing subscriber (snapshot wins)…
        SubscriptionPlanBenefit::where('subscription_benefit_id', $listings->id)->update(['value' => json_encode(50)]);
        $this->assertSame(25, $this->resolve($account, 'max_active_listings')->value);

        // 3. …but a subscription without a benefit snapshot uses the live plan value.
        $sub = Subscription::where('supplier_account_id', $account->id)->latest('id')->first();
        $sub->update(['features_snapshot' => array_diff_key($sub->features_snapshot, ['benefits' => true])]);
        $this->assertSame(50, $this->resolve($account, 'max_active_listings')->value);
        $this->assertSame(ResolvedBenefit::SOURCE_PLAN, $this->resolve($account, 'max_active_listings')->source);

        // 1. Override replaces it — false and 0 included.
        AccountBenefitOverride::create(['account_id' => $account->id, 'subscription_benefit_id' => $featured->id, 'value' => false]);
        AccountBenefitOverride::create(['account_id' => $account->id, 'subscription_benefit_id' => $listings->id, 'value' => 0]);
        $this->assertFalse($this->resolve($account, 'featured_supplier')->value);
        $this->assertSame(0, $this->resolve($account, 'max_active_listings')->value);
        $this->assertSame(ResolvedBenefit::SOURCE_OVERRIDE, $this->resolve($account, 'max_active_listings')->source);
    }

    public function test_expired_and_future_overrides_are_ignored_and_ineligible_accounts_get_defaults(): void
    {
        $supplier = $this->makeSupplier('expired@example.com');
        $account = $supplier->account;
        $featured = $this->benefit('featured_supplier', 'boolean');

        AccountBenefitOverride::create(['account_id' => $account->id, 'subscription_benefit_id' => $featured->id, 'value' => true, 'expires_at' => now()->subMinute()]);
        AccountBenefitOverride::create(['account_id' => $account->id, 'subscription_benefit_id' => $featured->id, 'value' => true, 'starts_at' => now()->addDay()]);
        $this->assertFalse($this->resolve($account, 'featured_supplier')->value);

        // Override grants it without any subscription…
        AccountBenefitOverride::create(['account_id' => $account->id, 'subscription_benefit_id' => $featured->id, 'value' => true]);
        $this->assertTrue($this->resolve($account, 'featured_supplier')->value);

        // …but never to an ineligible (suspended) account.
        $account->update(['status' => 'suspended']);
        $this->assertFalse($this->resolve($account, 'featured_supplier')->value);
        $this->assertSame(ResolvedBenefit::SOURCE_INELIGIBLE, $this->resolve($account, 'featured_supplier')->source);
    }

    public function test_admin_manages_benefits(): void
    {
        $admin = $this->makeStaff('benefit-admin@example.com', 'admin');

        $this->actingAs($admin)->get(route('admin.billing.benefits.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.billing.benefits.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.billing.benefits.store'), [
            'code' => 'Bad Code!', 'name' => 'X', 'value_type' => 'integer',
        ])->assertSessionHasErrors('code');

        $this->actingAs($admin)->post(route('admin.billing.benefits.store'), [
            'code' => 'rfq_delay_minutes', 'name' => 'RFQ Delay', 'value_type' => 'integer',
            'default_value' => '30', 'unit' => 'minutes', 'sort_order' => 3, 'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $benefit = SubscriptionBenefit::where('code', 'rfq_delay_minutes')->firstOrFail();
        $this->assertSame(30, $benefit->default_value);
        $this->assertSame('30 minutes', $benefit->formatValue($benefit->fallbackValue()));
        $this->actingAs($admin)->get(route('admin.billing.benefits.show', $benefit))->assertOk()->assertSee('RFQ Delay');
        $this->actingAs($admin)->get(route('admin.billing.benefits.index', ['search' => 'rfq', 'type' => 'integer']))->assertOk()->assertSee('rfq_delay_minutes');

        // Duplicate code rejected.
        $this->actingAs($admin)->post(route('admin.billing.benefits.store'), ['code' => 'rfq_delay_minutes', 'name' => 'Dup', 'value_type' => 'integer'])
            ->assertSessionHasErrors('code');

        // In use → can't delete, and code/type are locked.
        $plan = $this->plan();
        SubscriptionPlanBenefit::create(['subscription_plan_id' => $plan->id, 'subscription_benefit_id' => $benefit->id, 'value' => 10]);
        $this->actingAs($admin)->delete(route('admin.billing.benefits.destroy', $benefit))->assertSessionHas('error');
        $this->assertNotNull($benefit->fresh());
        $this->actingAs($admin)->put(route('admin.billing.benefits.update', $benefit), [
            'code' => 'renamed', 'name' => 'RFQ Delay', 'value_type' => 'integer',
        ])->assertSessionHasErrors('code');

        // Deactivate instead.
        $this->actingAs($admin)->post(route('admin.billing.benefits.deactivate', $benefit));
        $this->assertFalse($benefit->fresh()->is_active);

        // An unused benefit can be deleted.
        $unused = $this->benefit('priority_support', 'boolean');
        $this->actingAs($admin)->delete(route('admin.billing.benefits.destroy', $unused))->assertRedirect(route('admin.billing.benefits.index'));
        $this->assertNull($unused->fresh());
    }

    public function test_admin_assigns_updates_and_removes_plan_benefits(): void
    {
        $admin = $this->makeStaff('plan-admin@example.com', 'admin');
        $plan = $this->plan('Gold');
        $listings = $this->benefit('max_active_listings', 'integer');
        $featured = $this->benefit('featured_supplier', 'boolean');

        $this->actingAs($admin)->get(route('admin.billing.plan-benefits.index'))->assertOk()->assertSee('Gold');
        $this->actingAs($admin)->get(route('admin.billing.plan-benefits.edit', $plan))->assertOk()->assertSee('max_active_listings');

        // Type-checked: a word is not a whole number.
        $this->actingAs($admin)->put(route('admin.billing.plan-benefits.update', $plan), [
            'benefits' => [$listings->id => ['assigned' => '1', 'value' => 'lots'], $featured->id => ['assigned' => '0']],
        ])->assertSessionHasErrors("benefits.{$listings->id}.value");

        $this->actingAs($admin)->put(route('admin.billing.plan-benefits.update', $plan), [
            'benefits' => [$listings->id => ['assigned' => '1', 'value' => '40'], $featured->id => ['assigned' => '1', 'value' => '1']],
        ])->assertSessionDoesntHaveErrors();
        // Saving again updates in place — never a duplicate row.
        $this->actingAs($admin)->put(route('admin.billing.plan-benefits.update', $plan), [
            'benefits' => [$listings->id => ['assigned' => '1', 'value' => '60'], $featured->id => ['assigned' => '1', 'value' => '1']],
        ]);

        $this->assertSame(2, $plan->planBenefits()->count());
        $this->assertSame(['featured_supplier' => true, 'max_active_listings' => 60], collect(app(BenefitResolver::class)->planBenefitMap($plan))->sortKeys()->all());

        // Unticking removes it.
        $this->actingAs($admin)->put(route('admin.billing.plan-benefits.update', $plan), [
            'benefits' => [$listings->id => ['assigned' => '1', 'value' => '60'], $featured->id => ['assigned' => '0']],
        ]);
        $this->assertSame(['max_active_listings'], $plan->planBenefits()->with('benefit')->get()->pluck('benefit.code')->all());
    }

    public function test_admin_manages_supplier_overrides(): void
    {
        $admin = $this->makeStaff('override-admin@example.com', 'admin');
        $supplier = $this->makeSupplier('override-supplier@example.com');
        $account = $supplier->account;
        $featured = $this->benefit('featured_supplier', 'boolean');

        $this->actingAs($admin)->get(route('admin.billing.benefit-overrides.index', ['search' => (string) $account->id]))->assertOk();
        $this->actingAs($admin)->get(route('admin.billing.benefit-overrides.show', $account))->assertOk()->assertSee('Effective Benefits');

        $this->actingAs($admin)->post(route('admin.billing.benefit-overrides.store', $account), [
            '_form' => 'create', 'subscription_benefit_id' => $featured->id, 'value' => '1', 'reason' => 'Launch promotion',
        ])->assertSessionDoesntHaveErrors();
        $override = $account->benefitOverrides()->firstOrFail();
        $this->assertSame($admin->id, $override->created_by_user_id);
        $this->assertTrue($this->resolve($account, 'featured_supplier')->value);

        // A second overlapping active override for the same benefit is rejected.
        $this->actingAs($admin)->post(route('admin.billing.benefit-overrides.store', $account), [
            '_form' => 'create', 'subscription_benefit_id' => $featured->id, 'value' => '0',
        ])->assertSessionHasErrors('subscription_benefit_id');

        // Deactivate → back to inherited; reactivate → applies again.
        $this->actingAs($admin)->post(route('admin.billing.benefit-overrides.deactivate', [$account, $override]));
        $this->assertFalse($this->resolve($account, 'featured_supplier')->value);
        $this->actingAs($admin)->post(route('admin.billing.benefit-overrides.reactivate', [$account, $override]));
        $this->assertTrue($this->resolve($account, 'featured_supplier')->value);

        // Revoke ends it now but keeps the row as history.
        $this->actingAs($admin)->post(route('admin.billing.benefit-overrides.revoke', [$account, $override]));
        $this->assertSame('expired', $override->fresh()->status());
        $this->assertFalse($this->resolve($account, 'featured_supplier')->value);
        $this->assertDatabaseHas('activity_log', ['log_name' => 'billing', 'description' => 'Benefit override revoked: featured_supplier']);

        // An override of another account can't be reached through this account's URL.
        $other = $this->makeSupplier('override-other@example.com');
        $foreign = AccountBenefitOverride::create(['account_id' => $other->account->id, 'subscription_benefit_id' => $featured->id, 'value' => true]);
        $this->actingAs($admin)->post(route('admin.billing.benefit-overrides.revoke', [$account, $foreign]))->assertNotFound();
    }

    public function test_list_modals_save_and_return_to_the_same_list(): void
    {
        $admin = $this->makeStaff('modal-admin@example.com', 'admin');
        $benefit = $this->benefit('featured_supplier', 'boolean');
        $plan = $this->plan('Silver');

        // Benefits list has the edit modal; saving from it returns to the list (query kept).
        $listUrl = route('admin.billing.benefits.index', ['search' => 'featured', 'page' => 1]);
        $this->actingAs($admin)->get($listUrl)->assertOk()->assertSee('open-modal-benefit-edit', false);
        $this->actingAs($admin)->put(route('admin.billing.benefits.update', $benefit), [
            '_form' => 'benefit-edit', '_id' => $benefit->id, '_redirect' => $listUrl,
            'code' => 'featured_supplier', 'name' => 'Featured Supplier Badge', 'value_type' => 'boolean', 'is_active' => '1',
        ])->assertRedirect($listUrl);
        $this->assertSame('Featured Supplier Badge', $benefit->fresh()->name);

        // A foreign _redirect is ignored.
        $this->actingAs($admin)->put(route('admin.billing.benefits.update', $benefit), [
            '_redirect' => 'https://evil.example.com/', 'code' => 'featured_supplier', 'name' => 'Featured', 'value_type' => 'boolean',
        ])->assertRedirect(route('admin.billing.benefits.show', $benefit));

        // Plan list is paginated with a Manage modal per plan; saving returns to that page.
        $plansUrl = route('admin.billing.plan-benefits.index', ['page' => 1]);
        $this->actingAs($admin)->get($plansUrl)->assertOk()->assertSee("open-modal-plan-benefits-{$plan->id}", false);
        $this->actingAs($admin)->put(route('admin.billing.plan-benefits.update', $plan), [
            '_form' => "plan-{$plan->id}", '_redirect' => $plansUrl,
            'benefits' => [$benefit->id => ['assigned' => '1', 'value' => '1']],
        ])->assertRedirect($plansUrl);
        $this->assertSame(1, $plan->planBenefits()->count());
    }

    public function test_overrides_list_shows_serial_numbers_across_pages(): void
    {
        $admin = $this->makeStaff('sl-admin@example.com', 'admin');
        foreach (range(1, 16) as $i) {
            $this->makeSupplier("sl-supplier-{$i}@example.com");
        }

        $page2 = $this->actingAs($admin)->get(route('admin.billing.benefit-overrides.index', ['page' => 2]))->assertOk();
        $this->assertMatchesRegularExpression('/<td class="px-5 py-3\.5 text-sm text-gray-500">\s*16\s*<\/td>/', $page2->getContent());

        $supplier = \App\Models\Account::whereHas('capabilities')->where('account_number', '!=', 'SYSTEM')->first();
        $this->actingAs($admin)->get(route('admin.billing.benefit-overrides.show', $supplier))
            ->assertOk()->assertSee('Apply Override')->assertSee('overrideForm(', false);
    }

    public function test_billing_benefit_pages_require_the_subscriptions_permission(): void
    {
        $moderator = $this->makeStaff('benefit-mod@example.com', 'moderator');

        $this->actingAs($moderator)->get(route('admin.billing.benefits.index'))->assertForbidden();
        $this->actingAs($moderator)->get(route('admin.billing.plan-benefits.index'))->assertForbidden();
        $this->actingAs($moderator)->get(route('admin.billing.benefit-overrides.index'))->assertForbidden();
    }
}
