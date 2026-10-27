<?php

namespace App\Http\Controllers\Backend\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Billing\PlanBenefitsRequest;
use App\Models\SubscriptionBenefit;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanBenefit;
use App\Services\Billing\FeaturedSupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Subscription & Billing › Plan Benefit Assignment — which benefits a plan
 * grants and with what value (subscription_plan_benefits, one row per plan +
 * benefit). New subscriptions snapshot these values; existing subscribers
 * keep theirs until renewal or plan change.
 */
class PlanBenefitController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.subscriptions.manage');

        $search = trim($request->string('search')->toString());

        $plans = SubscriptionPlan::query()
            ->withCount(['planBenefits', 'subscriptions'])
            ->with('planBenefits')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // For the "Manage" modals: active benefits, plus inactive ones a plan on this page still has.
        $assignedIds = $plans->getCollection()->flatMap->planBenefits->pluck('subscription_benefit_id')->unique();
        $benefits = SubscriptionBenefit::query()
            ->where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $assignedIds))
            ->ordered()
            ->get();

        return view('backend.admin.billing.plan-benefits.index', [
            'plans' => $plans,
            'benefits' => $benefits,
            'search' => $search,
            'benefitCount' => $benefits->where('is_active', true)->count(),
        ]);
    }

    public function edit(SubscriptionPlan $plan)
    {
        $this->authorize('platform.subscriptions.manage');

        $assigned = $plan->planBenefits()->get()->keyBy('subscription_benefit_id');

        // Every active benefit, plus inactive ones this plan still has (so they can be removed).
        $benefits = SubscriptionBenefit::query()
            ->where(fn ($q) => $q->where('is_active', true)->orWhereIn('id', $assigned->keys()))
            ->ordered()
            ->get();

        return view('backend.admin.billing.plan-benefits.edit', compact('plan', 'benefits', 'assigned'));
    }

    public function update(PlanBenefitsRequest $request, SubscriptionPlan $plan)
    {
        $rows = (array) $request->validated('benefits', []);
        $benefits = SubscriptionBenefit::whereIn('id', array_keys($rows))->get()->keyBy('id');

        $changes = DB::transaction(function () use ($plan, $rows, $benefits) {
            $changes = ['assigned' => [], 'removed' => []];

            foreach ($rows as $id => $row) {
                $benefit = $benefits->get((int) $id);
                if (! $benefit) {
                    continue;
                }

                if (filter_var($row['assigned'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    // Unique (plan, benefit) — updateOrCreate never duplicates.
                    SubscriptionPlanBenefit::updateOrCreate(
                        ['subscription_plan_id' => $plan->id, 'subscription_benefit_id' => $benefit->id],
                        ['value' => $benefit->normalize($row['value'] ?? null)]
                    );
                    $changes['assigned'][$benefit->code] = $benefit->normalize($row['value'] ?? null);
                } elseif ($plan->planBenefits()->where('subscription_benefit_id', $benefit->id)->delete()) {
                    $changes['removed'][] = $benefit->code;
                }
            }

            return $changes;
        });

        FeaturedSupplierService::flush();

        activity('billing')
            ->performedOn($plan)
            ->causedBy($request->user())
            ->withProperties($changes)
            ->log("Plan benefits updated: {$plan->name}");

        // Saved from the list's modal → back to that list page; otherwise the plan's own page.
        $list = route('admin.billing.plan-benefits.index');
        $back = (string) $request->input('_redirect', '');
        $target = $back !== '' && ($back === $list || str_starts_with($back, $list.'?'))
            ? $back
            : route('admin.billing.plan-benefits.edit', $plan);

        return redirect()->to($target)
            ->with('success', "\"{$plan->name}\" benefits saved. New subscriptions get these values; existing subscribers keep theirs until renewal or plan change.");
    }
}
