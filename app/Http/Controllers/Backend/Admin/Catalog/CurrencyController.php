<?php

namespace App\Http\Controllers\Backend\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Admin\Catalog\CurrencyRequest;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    protected function authorizeAccess(): void
    {
        $u = auth()->user();
        abort_unless($u && $u->hasAnyPermission(['platform.attributes.manage', 'platform.settings.manage', 'platform.categories.manage']), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $currencies = Currency::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->string('search') . '%';
                $q->where('code', 'like', $s)
                  ->orWhere('name', 'like', $s)
                  ->orWhere('symbol', 'like', $s);
            })
            ->orderByDesc('is_default')
            ->orderBy('code')
            ->get();

        return view('backend.admin.catalog.currencies.index', [
            'currencies' => $currencies,
            'search'     => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        $this->authorizeAccess();

        return view('backend.admin.catalog.currencies.create', ['currency' => new Currency()]);
    }

    public function store(CurrencyRequest $request)
    {
        $this->authorizeAccess();

        Currency::create($request->validated() + [
            'code'      => strtoupper($request->string('code')),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.currencies.index')->with('success', 'Currency created.');
    }

    public function edit(Currency $currency)
    {
        $this->authorizeAccess();

        return view('backend.admin.catalog.currencies.edit', ['currency' => $currency]);
    }

    public function update(CurrencyRequest $request, Currency $currency)
    {
        $this->authorizeAccess();

        $currency->update($request->safe()->except('code') + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.currencies.index')->with('success', 'Currency updated.');
    }

    public function destroy(Currency $currency)
    {
        $this->authorizeAccess();

        abort_if($currency->is_default, 422, 'Cannot delete the default currency.');
        abort_if($currency->subscriptionPlans()->exists(), 422, 'This currency is in use by subscription plans.');

        $currency->delete();

        return back()->with('success', 'Currency deleted.');
    }

    public function makeDefault(Currency $currency)
    {
        $this->authorizeAccess();

        DB::transaction(function () use ($currency) {
            Currency::where('is_default', true)->update(['is_default' => false]);
            $currency->update(['is_default' => true, 'is_active' => true]);
        });

        return back()->with('success', 'Default currency updated.');
    }
}
