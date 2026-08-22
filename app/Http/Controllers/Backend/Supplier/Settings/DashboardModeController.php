<?php

namespace App\Http\Controllers\Backend\Supplier\Settings;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardModeController extends Controller
{
    use InteractsWithSupplierAccount;

    public function edit()
    {
        $account = $this->currentAccount();

        return view('backend.supplier.settings.dashboard-mode', [
            'account' => $account,
            'user' => $this->currentUser(),
            'current' => $account->dashboardPreference?->default_mode ?? 'supplier',
            'hasBuyer' => $account->hasActiveCapability('buyer'),
        ]);
    }

    public function update(Request $request)
    {
        $account = $this->currentAccount();

        $request->validate(['default_mode' => ['required', 'in:buyer,supplier']]);

        $account->dashboardPreference()->updateOrCreate([], ['default_mode' => $request->string('default_mode')]);

        return back()->with('success', 'Default dashboard mode preference saved.');
    }
}
