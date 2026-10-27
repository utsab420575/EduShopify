<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\AccountBenefitOverride;
use App\Models\SubscriptionBenefit;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin actions on account_benefit_overrides. History is never deleted:
 * "deactivate" switches an override off (it can be switched back on),
 * "revoke" ends it now (expires_at = now). Two switched-on overrides for the
 * same account + benefit may not overlap in time — enforced here, not by a
 * unique index, so expired/inactive rows stay as history.
 */
class BenefitOverrideService
{
    public function __construct(private readonly BenefitResolver $resolver) {}

    /** @param array{subscription_benefit_id:int, value:mixed, starts_at:?string, expires_at:?string, reason:?string} $data */
    public function create(Account $account, User $admin, array $data): AccountBenefitOverride
    {
        return DB::transaction(function () use ($account, $admin, $data) {
            $benefit = SubscriptionBenefit::findOrFail($data['subscription_benefit_id']);
            [$startsAt, $expiresAt] = $this->window($data);

            $this->assertNoOverlap($account, $benefit, $startsAt, $expiresAt);

            $override = AccountBenefitOverride::create([
                'account_id' => $account->id,
                'subscription_benefit_id' => $benefit->id,
                'value' => $benefit->normalize($data['value'] ?? null),
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'is_active' => true,
                'created_by_user_id' => $admin->id,
                'reason' => $data['reason'] ?? null,
            ]);

            $this->log($override, $admin, 'created', "Benefit override created: {$benefit->code}");

            return $override;
        });
    }

    /** @param array{value:mixed, starts_at:?string, expires_at:?string, reason:?string} $data */
    public function update(AccountBenefitOverride $override, User $admin, array $data): AccountBenefitOverride
    {
        return DB::transaction(function () use ($override, $admin, $data) {
            $benefit = $override->benefit;
            [$startsAt, $expiresAt] = $this->window($data);

            if ($override->is_active) {
                $this->assertNoOverlap($override->account, $benefit, $startsAt, $expiresAt, $override->id);
            }

            $override->update([
                'value' => $benefit->normalize($data['value'] ?? null),
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'reason' => $data['reason'] ?? $override->reason,
            ]);

            $this->log($override, $admin, 'updated', "Benefit override updated: {$benefit->code}");

            return $override;
        });
    }

    public function deactivate(AccountBenefitOverride $override, User $admin): void
    {
        $override->update(['is_active' => false]);
        $this->log($override, $admin, 'deactivated', "Benefit override deactivated: {$override->benefit->code}");
    }

    public function reactivate(AccountBenefitOverride $override, User $admin): void
    {
        $this->assertNoOverlap($override->account, $override->benefit, $override->starts_at, $override->expires_at, $override->id);

        $override->update(['is_active' => true]);
        $this->log($override, $admin, 'reactivated', "Benefit override reactivated: {$override->benefit->code}");
    }

    /** End it now — kept as history, the subscription value applies again. */
    public function revoke(AccountBenefitOverride $override, User $admin): void
    {
        $now = now();
        $override->update([
            'expires_at' => $now,
            // A not-yet-started override ends before it begins.
            'starts_at' => $override->starts_at && $override->starts_at->gt($now) ? $now : $override->starts_at,
        ]);
        $this->log($override, $admin, 'revoked', "Benefit override revoked: {$override->benefit->code}");
    }

    /** @return array{0: ?CarbonInterface, 1: ?CarbonInterface} */
    private function window(array $data): array
    {
        $startsAt = ! empty($data['starts_at']) ? Carbon::parse($data['starts_at']) : null;
        $expiresAt = ! empty($data['expires_at']) ? Carbon::parse($data['expires_at']) : null;

        return [$startsAt, $expiresAt];
    }

    /**
     * Another switched-on, not-yet-expired override for the same account and
     * benefit whose window intersects [startsAt, expiresAt) — null = open.
     */
    private function assertNoOverlap(Account $account, SubscriptionBenefit $benefit, ?CarbonInterface $startsAt, ?CarbonInterface $expiresAt, ?int $ignoreId = null): void
    {
        $conflict = AccountBenefitOverride::where('account_id', $account->id)
            ->where('subscription_benefit_id', $benefit->id)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->when($expiresAt, fn ($q) => $q->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<', $expiresAt)))
            ->when($startsAt, fn ($q) => $q->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>', $startsAt)))
            ->first();

        if ($conflict) {
            throw ValidationException::withMessages([
                'subscription_benefit_id' => "This supplier already has an active \"{$benefit->name}\" override for this period (#{$conflict->id}). Edit, deactivate or revoke it first.",
            ]);
        }
    }

    private function log(AccountBenefitOverride $override, User $admin, string $action, string $description): void
    {
        $this->resolver->forget($override->account);
        FeaturedSupplierService::flush();

        activity('billing')
            ->performedOn($override)
            ->causedBy($admin)
            ->withProperties([
                'action' => $action,
                'account_id' => $override->account_id,
                'benefit' => $override->benefit?->code,
                'value' => $override->value,
                'starts_at' => $override->starts_at?->toDateTimeString(),
                'expires_at' => $override->expires_at?->toDateTimeString(),
                'is_active' => $override->is_active,
            ])
            ->log($description);
    }
}
