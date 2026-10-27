<?php

namespace App\Http\Controllers\Backend\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Billing\SubscriptionBenefitRequest;
use App\Models\AccountBenefitOverride;
use App\Models\SubscriptionBenefit;
use Illuminate\Http\Request;

/**
 * Subscription & Billing › Manage Benefits — the catalogue of benefits plans
 * and overrides can grant. A benefit in use can only be deactivated, never
 * deleted (the foreign keys restrict it as well).
 */
class SubscriptionBenefitController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('platform.subscriptions.manage');

        $search = trim($request->string('search')->toString());
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();

        $benefits = SubscriptionBenefit::query()
            ->withCount(['planBenefits', 'overrides'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when(array_key_exists($type, SubscriptionBenefit::VALUE_TYPES), fn ($q) => $q->where('value_type', $type))
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        // Row data for the list's edit modal.
        $editData = $benefits->getCollection()->mapWithKeys(fn (SubscriptionBenefit $b) => [$b->id => [
            'id' => $b->id,
            'name' => $b->name,
            'code' => $b->code,
            'description' => (string) $b->description,
            'value_type' => $b->value_type,
            'default_value' => $this->formValue($b->default_value),
            'unit' => (string) $b->unit,
            'sort_order' => $b->sort_order,
            'is_active' => $b->is_active,
            'locked' => $b->plan_benefits_count > 0 || $b->overrides_count > 0,
            'action' => route('admin.billing.benefits.update', $b),
        ]]);

        return view('backend.admin.billing.benefits.index', compact('benefits', 'search', 'type', 'status', 'editData'));
    }

    /** A stored value as a form string ('' = not set, booleans as '1'/'0'). */
    private function formValue(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    public function create()
    {
        $this->authorize('platform.subscriptions.manage');

        return view('backend.admin.billing.benefits.create', [
            'benefit' => new SubscriptionBenefit(['value_type' => 'boolean', 'is_active' => true]),
        ]);
    }

    public function store(SubscriptionBenefitRequest $request)
    {
        $benefit = SubscriptionBenefit::create($request->benefitData());

        return redirect()->route('admin.billing.benefits.show', $benefit)->with('success', 'Benefit created.');
    }

    public function show(SubscriptionBenefit $benefit)
    {
        $this->authorize('platform.subscriptions.manage');

        $benefit->load(['planBenefits.plan']);

        return view('backend.admin.billing.benefits.show', [
            'benefit' => $benefit,
            'overrideCount' => $benefit->overrides()->count(),
            'activeOverrideCount' => AccountBenefitOverride::where('subscription_benefit_id', $benefit->id)->effective()->count(),
        ]);
    }

    public function edit(SubscriptionBenefit $benefit)
    {
        $this->authorize('platform.subscriptions.manage');

        return view('backend.admin.billing.benefits.edit', [
            'benefit' => $benefit,
            'locked' => $benefit->isInUse(),
        ]);
    }

    public function update(SubscriptionBenefitRequest $request, SubscriptionBenefit $benefit)
    {
        $benefit->update($request->benefitData());

        // Edited from the list's modal → back to that list (search/filters/page kept).
        return redirect()->to($this->returnUrl($request, route('admin.billing.benefits.show', $benefit)))
            ->with('success', "Benefit \"{$benefit->name}\" updated.");
    }

    /** The submitted `_redirect`, only when it's this section's own list; otherwise $fallback. */
    private function returnUrl(Request $request, string $fallback): string
    {
        $url = (string) $request->input('_redirect', '');
        $list = route('admin.billing.benefits.index');

        return $url !== '' && ($url === $list || str_starts_with($url, $list.'?')) ? $url : $fallback;
    }

    public function destroy(SubscriptionBenefit $benefit)
    {
        $this->authorize('platform.subscriptions.manage');

        if ($benefit->isInUse()) {
            return back()->with('error', "\"{$benefit->name}\" is assigned to plans or supplier overrides and cannot be deleted — deactivate it instead.");
        }

        $benefit->delete();

        return redirect()->route('admin.billing.benefits.index')->with('success', 'Benefit deleted.');
    }

    public function activate(SubscriptionBenefit $benefit)
    {
        $this->authorize('platform.subscriptions.manage');

        $benefit->update(['is_active' => true]);

        return back()->with('success', 'Benefit activated.');
    }

    public function deactivate(SubscriptionBenefit $benefit)
    {
        $this->authorize('platform.subscriptions.manage');

        $benefit->update(['is_active' => false]);

        return back()->with('success', 'Benefit deactivated — it no longer applies to any plan or supplier.');
    }
}
