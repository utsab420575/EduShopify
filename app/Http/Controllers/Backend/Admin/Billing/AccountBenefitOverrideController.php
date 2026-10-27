<?php

namespace App\Http\Controllers\Backend\Admin\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Billing\AccountBenefitOverrideRequest;
use App\Models\Account;
use App\Models\AccountBenefitOverride;
use App\Models\SubscriptionBenefit;
use App\Services\Billing\BenefitOverrideService;
use App\Services\Billing\BenefitResolver;
use Illuminate\Http\Request;

/**
 * Subscription & Billing › Supplier Benefit Overrides — per-account benefit
 * values set by an admin, on top of (replacing) the supplier's subscription.
 * Accounts are addressed by accounts.id; only supplier accounts qualify.
 */
class AccountBenefitOverrideController extends Controller
{
    public function __construct(
        private readonly BenefitOverrideService $overrides,
        private readonly BenefitResolver $resolver,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('platform.subscriptions.manage');

        $search = trim($request->string('search')->toString());
        $filter = $request->string('filter')->toString();

        $suppliers = Account::query()
            ->whereHas('capabilities', fn ($q) => $q->whereHas('capabilityType', fn ($t) => $t->where('code', 'supplier')))
            ->with(['supplierProfile:id,account_id,display_name,logo,profile_photo', 'activeSubscription.plan:id,name'])
            ->withCount(['benefitOverrides as active_overrides_count' => fn ($q) => $q->effective()])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('display_name', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhereHas('supplierProfile', fn ($p) => $p->where('display_name', 'like', "%{$search}%"));
                    if (ctype_digit($search)) {
                        $w->orWhere('id', (int) $search);
                    }
                });
            })
            ->when($filter === 'with_overrides', fn ($q) => $q->whereHas('benefitOverrides', fn ($o) => $o->effective()))
            ->orderBy('display_name')
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.billing.benefit-overrides.index', compact('suppliers', 'search', 'filter'));
    }

    public function show(Account $account)
    {
        $this->authorize('platform.subscriptions.manage');
        $this->ensureSupplier($account);

        $account->load(['supplierProfile', 'capabilities.capabilityType']);

        $resolved = $this->resolver->forAccount($account);
        $history = $account->benefitOverrides()->with(['benefit', 'createdBy:id,name'])->latest('id')->get();
        $benefits = SubscriptionBenefit::active()->ordered()->get();

        // What each benefit is right now, for the override form ("Currently: …").
        $current = $benefits->mapWithKeys(function (SubscriptionBenefit $benefit) use ($resolved) {
            $item = $resolved->get($benefit->code);

            return [$benefit->id => [
                'type' => $benefit->value_type,
                'unit' => (string) $benefit->unit,
                'name' => $benefit->name,
                'label' => $item ? $item->formatted() : $benefit->formatValue($benefit->fallbackValue()),
                'source' => $item ? $item->sourceLabel() : 'Default',
                'value' => $this->formValue($item ? $item->value : $benefit->fallbackValue(), $benefit->value_type),
            ]];
        });

        return view('backend.admin.billing.benefit-overrides.show', [
            'account' => $account,
            'subscription' => $account->activeSubscription()->with('plan')->first(),
            'eligible' => $this->resolver->isEligible($account),
            'resolved' => $resolved,
            'history' => $history,
            'benefits' => $benefits,
            'current' => $current,
            'activeCount' => $history->filter(fn ($o) => $o->status() === 'active')->count(),
            'scheduledCount' => $history->filter(fn ($o) => $o->status() === 'scheduled')->count(),
        ]);
    }

    public function store(AccountBenefitOverrideRequest $request, Account $account)
    {
        $this->ensureSupplier($account);

        $this->overrides->create($account, $request->user(), $request->validated());

        return redirect()->route('admin.billing.benefit-overrides.show', $account)->with('success', 'Override saved.');
    }

    public function update(AccountBenefitOverrideRequest $request, Account $account, AccountBenefitOverride $override)
    {
        $this->ensureOwned($account, $override);

        $this->overrides->update($override, $request->user(), $request->validated());

        return redirect()->route('admin.billing.benefit-overrides.show', $account)->with('success', 'Override updated.');
    }

    public function deactivate(Request $request, Account $account, AccountBenefitOverride $override)
    {
        $this->authorize('platform.subscriptions.manage');
        $this->ensureOwned($account, $override);

        $this->overrides->deactivate($override, $request->user());

        return back()->with('success', 'Override deactivated — the subscription value applies again.');
    }

    public function reactivate(Request $request, Account $account, AccountBenefitOverride $override)
    {
        $this->authorize('platform.subscriptions.manage');
        $this->ensureOwned($account, $override);

        $this->overrides->reactivate($override, $request->user());

        return back()->with('success', 'Override reactivated.');
    }

    public function revoke(Request $request, Account $account, AccountBenefitOverride $override)
    {
        $this->authorize('platform.subscriptions.manage');
        $this->ensureOwned($account, $override);

        $this->overrides->revoke($override, $request->user());

        return back()->with('success', 'Override revoked — kept in history, the subscription value applies again.');
    }

    /** A typed value as a form string (booleans '1'/'0', '' for none). */
    private function formValue(mixed $value, string $type): string
    {
        return match (true) {
            $value === null => $type === 'boolean' ? '0' : '',
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    private function ensureSupplier(Account $account): void
    {
        $account->loadMissing('capabilities.capabilityType');
        abort_unless($account->hasCapability('supplier'), 404, 'This account is not a supplier.');
    }

    private function ensureOwned(Account $account, AccountBenefitOverride $override): void
    {
        abort_unless($override->account_id === $account->id, 404);
    }
}
