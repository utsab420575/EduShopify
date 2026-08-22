<?php

namespace App\Http\Controllers\Backend\Admin\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\System\CurrencyRequest;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.currencies.index', [
            'currencies' => Currency::orderBy('code')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.currencies.create', ['currency' => new Currency()]);
    }

    public function store(CurrencyRequest $request)
    {
        Currency::create($request->validated() + [
            'code' => strtoupper($request->string('code')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.system.currencies.index')->with('success', 'Currency created.');
    }

    public function edit(Currency $currency)
    {
        $this->authorize('platform.settings.manage');

        return view('backend.admin.system.currencies.edit', ['currency' => $currency]);
    }

    public function update(CurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->safe()->except('code') + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.system.currencies.index')->with('success', 'Currency updated.');
    }

    public function destroy(Currency $currency)
    {
        $this->authorize('platform.settings.manage');

        abort_if($currency->is_default, 422, 'Cannot delete the default currency.');
        abort_if($currency->subscriptionPlans()->exists(), 422, 'This currency is in use by subscription plans.');

        $currency->delete();

        return back()->with('success', 'Currency deleted.');
    }

    public function makeDefault(Currency $currency)
    {
        $this->authorize('platform.settings.manage');

        \Illuminate\Support\Facades\DB::transaction(function () use ($currency) {
            Currency::where('is_default', true)->update(['is_default' => false]);
            $currency->update(['is_default' => true, 'is_active' => true]);
        });

        return back()->with('success', 'Default currency updated.');
    }
}
