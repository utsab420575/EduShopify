<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: subscription_plan_benefits — the value of one benefit on one plan
 * (unique per plan + benefit). The single source of truth for plan benefit
 * configuration; existing subscribers keep the values snapshotted into
 * subscriptions.features_snapshot['benefits'] until renewal or plan change.
 */
class SubscriptionPlanBenefit extends Model
{
    protected $fillable = [
        'subscription_plan_id',
        'subscription_benefit_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(SubscriptionBenefit::class, 'subscription_benefit_id');
    }
}
