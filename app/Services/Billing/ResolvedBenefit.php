<?php

namespace App\Services\Billing;

use App\Models\AccountBenefitOverride;
use App\Models\SubscriptionBenefit;

/**
 * One benefit's effective value for one account, plus where it came from —
 * what BenefitResolver returns. The plan/snapshot/default values are kept
 * alongside so the admin can see what an override is replacing.
 */
final class ResolvedBenefit
{
    public const SOURCE_OVERRIDE = 'override';
    public const SOURCE_SNAPSHOT = 'snapshot';
    public const SOURCE_PLAN = 'plan';
    public const SOURCE_DEFAULT = 'default';
    public const SOURCE_INELIGIBLE = 'ineligible';

    public function __construct(
        public readonly SubscriptionBenefit $benefit,
        public readonly bool|int|float|string|null $value,
        public readonly string $source,
        public readonly ?AccountBenefitOverride $override = null,
        public readonly bool $hasSnapshotValue = false,
        public readonly bool|int|float|string|null $snapshotValue = null,
        public readonly bool $hasPlanValue = false,
        public readonly bool|int|float|string|null $planValue = null,
    ) {}

    public function formatted(): string
    {
        return $this->benefit->formatValue($this->value);
    }

    /** The value the account would have without its override. */
    public function inheritedValue(): bool|int|float|string|null
    {
        return match (true) {
            $this->hasSnapshotValue => $this->snapshotValue,
            $this->hasPlanValue => $this->planValue,
            default => $this->benefit->fallbackValue(),
        };
    }

    public function sourceLabel(): string
    {
        return match ($this->source) {
            self::SOURCE_OVERRIDE => 'Admin override',
            self::SOURCE_SNAPSHOT => 'Subscription (snapshot)',
            self::SOURCE_PLAN => 'Plan',
            self::SOURCE_INELIGIBLE => 'Not eligible',
            default => 'Default',
        };
    }
}
