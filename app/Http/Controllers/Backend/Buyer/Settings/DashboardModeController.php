<?php

namespace App\Http\Controllers\Backend\Buyer\Settings;

use App\Http\Controllers\Backend\Buyer\Concerns\InteractsWithBuyerAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardModeController extends Controller
{
    use InteractsWithBuyerAccount;

    public function edit()
    {
        $account = $this->currentAccount();

        abort_unless($account->hasActiveCapability('buyer') && $account->hasActiveCapability('supplier'), 404);

        return view('backend.buyer.settings.dashboard-mode', [
            'current' => $account->dashboardPreference?->default_mode ?? 'buyer',
        ]);
    }

    public function update(Request $request)
    {
        $account = $this->currentAccount();

        abort_unless($account->hasActiveCapability('buyer') && $account->hasActiveCapability('supplier'), 404);

        $request->validate(['default_mode' => ['required', 'in:buyer,supplier']]);

        $account->dashboardPreference()->updateOrCreate([], ['default_mode' => $request->string('default_mode')]);

        return back()->with('success', 'Default dashboard updated.');
    }
}
